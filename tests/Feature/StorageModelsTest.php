<?php

use App\Models\File;
use App\Models\FileShareLink;
use App\Models\Folder;
use App\Models\FolderShareLink;
use App\Models\StoragePlan;
use App\Models\StorageSubscription;
use App\Models\StorageUsageEvent;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Database\Seeders\StoragePlanSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

test('users folders and files are linked in both directions', function () {
    $user = User::factory()->create();
    $parent = Folder::factory()->for($user)->create();
    $child = Folder::factory()->for($user)->for($parent, 'parent')->create();
    $file = File::factory()->for($user)->for($child)->create();

    expect($user->folders->modelKeys())->toBe([$parent->id, $child->id])
        ->and($user->files->sole()->is($file))->toBeTrue()
        ->and($parent->children->sole()->is($child))->toBeTrue()
        ->and($child->parent->is($parent))->toBeTrue()
        ->and($child->files->sole()->is($file))->toBeTrue()
        ->and($file->folder->is($child))->toBeTrue()
        ->and($file->user->is($user))->toBeTrue()
        ->and($child->user->is($user))->toBeTrue();

    $created = $user->folders()->create(['name' => 'Created through relationship']);
    expect(Str::isUuid($created->uuid))->toBeTrue()
        ->and($created->getKeyType())->toBe('int')
        ->and($created->getIncrementing())->toBeTrue();
});

test('share links connect targets owners and recipients and hash passwords', function (string $targetClass, string $linkClass, string $targetRelation, string $sentRelation, string $receivedRelation) {
    $target = $targetClass::factory()->create();
    $recipient = User::factory()->create();
    $link = $linkClass::factory()->for($target, $targetRelation)
        ->for($recipient, 'recipient')->create(['password_hash' => 'link-password']);

    expect($link->{$targetRelation}->is($target))->toBeTrue()
        ->and($link->user->is($target->user))->toBeTrue()
        ->and($link->recipient->is($recipient))->toBeTrue()
        ->and($target->shareLinks->sole()->is($link))->toBeTrue()
        ->and($target->user->{$sentRelation}->sole()->is($link))->toBeTrue()
        ->and($recipient->{$receivedRelation}->sole()->is($link))->toBeTrue()
        ->and(Hash::check('link-password', $link->password_hash))->toBeTrue()
        ->and($link->toArray())->not->toHaveKeys(['token_hash', 'password_hash'])
        ->and($link->allow_download)->toBeTrue()
        ->and($link->access_count)->toBe(0);
})->with([
    [File::class, FileShareLink::class, 'file', 'fileShareLinks', 'receivedFileShareLinks'],
    [Folder::class, FolderShareLink::class, 'folder', 'folderShareLinks', 'receivedFolderShareLinks'],
]);

test('folder passwords and internal file paths stay out of serialization', function () {
    $folder = Folder::factory()->create(['password_hash' => 'folder-password', 'password_changed_at' => now()]);
    $file = File::factory()->create(['starred_at' => now()]);

    expect(Hash::check('folder-password', $folder->password_hash))->toBeTrue()
        ->and($folder->toArray())->not->toHaveKey('password_hash')
        ->and($file->toArray())->not->toHaveKeys(['storage_key', 'disk'])
        ->and($folder->password_changed_at)->toBeInstanceOf(Carbon::class)
        ->and($file->starred_at)->toBeInstanceOf(Carbon::class);

    $folder->delete();
    $file->delete();
    expect(Folder::find($folder->id))->toBeNull()
        ->and(File::find($file->id))->toBeNull()
        ->and(Folder::withTrashed()->find($folder->id)->trashed())->toBeTrue()
        ->and(File::withTrashed()->find($file->id)->trashed())->toBeTrue();
});

test('usage events save without updated at and link to files and users', function () {
    $event = StorageUsageEvent::factory()->create(['bytes_delta' => -5000000000]);

    expect($event->bytes_delta)->toBe(-5000000000)
        ->and($event->created_at)->toBeInstanceOf(Carbon::class)
        ->and($event->file->storageUsageEvents->sole()->is($event))->toBeTrue()
        ->and($event->user->storageUsageEvents->sole()->is($event))->toBeTrue()
        ->and($event->user->is($event->file->user))->toBeTrue();

    $event->update(['bytes_delta' => -10]);
    expect($event->fresh()->bytes_delta)->toBe(-10);
});

test('plans subscriptions and payments link to each other and the account', function () {
    $plan = StoragePlan::where('code', 'extra-5gb-monthly')->sole();
    $subscription = StorageSubscription::factory()->for($plan)->create([
        'current_period_starts_at' => now(),
        'current_period_ends_at' => now()->addMonth(),
    ]);
    $payment = SubscriptionPayment::factory()->for($subscription)->create();

    expect($subscription->storagePlan->is($plan))->toBeTrue()
        ->and($plan->subscriptions->sole()->is($subscription))->toBeTrue()
        ->and($subscription->user->storageSubscriptions->sole()->is($subscription))->toBeTrue()
        ->and($subscription->payments->sole()->is($payment))->toBeTrue()
        ->and($payment->storageSubscription->is($subscription))->toBeTrue()
        ->and($subscription->user->subscriptionPayments->sole()->is($payment))->toBeTrue()
        ->and($subscription->additional_storage_bytes)->toBe(5000000000)
        ->and($payment->amount_paise)->toBe(39900)
        ->and(Str::isUuid($payment->uuid))->toBeTrue()
        ->and($subscription->current_period_ends_at)->toBeInstanceOf(Carbon::class);
});

test('model defaults match the storage schema and account quotas are guarded', function () {
    expect((new User)->free_storage_bytes)->toBe(2000000000)
        ->and((new User)->isFillable('free_storage_bytes'))->toBeFalse()
        ->and((new Folder)->isFillable('user_id'))->toBeFalse()
        ->and((new File)->disk)->toBe('local')
        ->and((new File)->status)->toBe('pending')
        ->and((new StoragePlan)->is_active)->toBeTrue()
        ->and((new StorageSubscription)->cancel_at_period_end)->toBeFalse()
        ->and((new SubscriptionPayment)->refunded_amount_paise)->toBe(0);
});

test('storage plan seeding is idempotent', function () {
    $this->seed(StoragePlanSeeder::class);
    $this->seed(StoragePlanSeeder::class);

    $this->assertDatabaseCount('storage_plans', 3);
});
