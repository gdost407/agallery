<?php

use App\Http\StorageManager;
use App\Models\File;
use App\Models\FileShareLink;
use App\Models\Folder;
use App\Models\StorageSubscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

test('uploads save private files and update lists storage counters and history', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('app.files.store'), [
        'files' => [UploadedFile::fake()->image('holiday.jpg'), UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
    ])->assertRedirect(route('app.dashboard'))->assertSessionHasNoErrors();

    $files = $user->files()->get();
    expect($files)->toHaveCount(2)
        ->and($files->pluck('category')->all())->toBe(['image', 'document'])
        ->and($user->fresh()->used_storage_bytes)->toBe((int) $files->sum('size_bytes'));
    foreach ($files as $file) {
        Storage::disk('local')->assertExists($file->storage_key);
        Storage::disk('public')->assertMissing($file->storage_key);
        expect($file->checksum_sha256)->toHaveLength(64);
    }
    $this->assertDatabaseCount('storage_usage_events', 2);
    $this->get(route('app.dashboard'))->assertSuccessful()->assertSee('holiday.jpg')->assertSee('notes.pdf')
        ->assertDontSee('Sample files')->assertSee('Storage used');
    $this->get(route('app.photos'))->assertSee('holiday.jpg')->assertDontSee('notes.pdf');
    $this->get(route('app.documents'))->assertSee('notes.pdf')->assertDontSee('holiday.jpg');
    $this->get(route('app.files.content', $files->first()->uuid))->assertSuccessful()->assertHeader('Cache-Control', 'no-store, private');
});

test('section upload endpoints accept their file types', function (string $routeName, string $filename, string $mime, string $category): void {
    $user = User::factory()->create();
    $upload = $category === 'image' ? UploadedFile::fake()->image($filename) : UploadedFile::fake()->create($filename, 1, $mime);
    $this->actingAs($user)->post(route($routeName), ['files' => [$upload]])
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseHas('files', ['user_id' => $user->id, 'original_name' => $filename, 'category' => $category]);
})->with([
    ['app.photos.store', 'photo.png', 'image/png', 'image'],
    ['app.videos.store', 'movie.mp4', 'video/mp4', 'video'],
    ['app.documents.store', 'report.pdf', 'application/pdf', 'document'],
]);

test('photo uploads reject non images and incomplete requests', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('app.photos.store'), [
        'files' => [UploadedFile::fake()->create('report.pdf', 1, 'application/pdf')],
    ])->assertSessionHasErrors('files.0');
    $this->post(route('app.files.store'), [])->assertSessionHasErrors('files');
    $this->assertDatabaseCount('files', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('folders support nested uploads and foreign folder ids are rejected', function (): void {
    $user = User::factory()->create();
    $otherFolder = Folder::factory()->create();
    $this->actingAs($user)->post(route('app.folders.store'), ['name' => 'Work'])
        ->assertRedirect()->assertSessionHasNoErrors();
    $parent = $user->folders()->sole();
    $this->post(route('app.folders.store'), ['name' => 'Reports', 'parent_id' => $parent->id])
        ->assertRedirect()->assertSessionHasNoErrors();
    $child = $parent->children()->sole();
    $this->post(route('app.files.store'), [
        'folder_id' => $child->id, 'files' => [UploadedFile::fake()->create('inside.txt', 1, 'text/plain')],
    ])->assertRedirect(route('app.folders.show', $child->uuid))->assertSessionHasNoErrors();
    $this->get(route('app.folders.show', $child->uuid))->assertSee('inside.txt');
    $this->get(route('app.dashboard'))->assertSee('Work')->assertDontSee('inside.txt');
    $this->post(route('app.files.store'), [
        'folder_id' => $otherFolder->id, 'files' => [UploadedFile::fake()->image('blocked.jpg')],
    ])->assertSessionHasErrors('folder_id');
    $this->post(route('app.folders.store'), ['name' => 'Blocked', 'parent_id' => $otherFolder->id])
        ->assertSessionHasErrors('parent_id');
    $this->get(route('app.folders.show', $otherFolder->uuid))->assertNotFound();
});

test('folder protection blocks descendants direct downloads and uploads until unlocked', function (): void {
    $user = User::factory()->create();
    $parent = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    $child = Folder::factory()->for($user)->for($parent, 'parent')->create();
    $file = File::factory()->for($user)->for($child)->create();
    Storage::disk('local')->put($file->storage_key, 'private data');

    $this->actingAs($user)->get(route('app.folders.show', $child->uuid))->assertSee('Enter the password')->assertDontSee($file->original_name);
    $this->get(route('app.files.content', $file->uuid))->assertStatus(423);
    $this->get(route('app.files.download', $file->uuid))->assertStatus(423);
    $this->post(route('app.files.store'), ['folder_id' => $child->id, 'files' => [UploadedFile::fake()->image('blocked.png')]])->assertStatus(423);
    $this->post(route('app.folders.unlock', $parent->uuid), ['password' => 'incorrect'])->assertSessionHasErrors('password');
    $this->post(route('app.folders.unlock', $parent->uuid), ['password' => 'folder-secret'])->assertRedirect();
    $this->get(route('app.files.download', $file->uuid))->assertSuccessful();
    $this->get(route('app.folders.show', $child->uuid))->assertSee($file->original_name);

    $this->travel(31)->minutes();
    $this->get(route('app.files.download', $file->uuid))->assertStatus(423);
});

test('legacy file passwords can still be unlocked and removed but new file passwords are rejected', function (): void {
    $user = User::factory()->create();
    $file = File::factory()->for($user)->create(['password_hash' => 'first-password']);
    Storage::disk('local')->put($file->storage_key, 'private data');
    expect(Hash::check('first-password', $file->password_hash))->toBeTrue();
    $this->actingAs($user)->get(route('app.files.download', $file->uuid))->assertStatus(423);
    $this->put(route('app.files.password', $file->uuid), [
        'password' => 'new-password', 'password_confirmation' => 'new-password',
        'current_password' => 'incorrect',
    ])->assertSessionHasErrors('password');
    expect(Hash::check('first-password', $file->fresh()->password_hash))->toBeTrue();
    $this->post(route('app.files.unlock', $file->uuid), ['password' => 'first-password'])->assertRedirect();
    $this->get(route('app.files.download', $file->uuid))->assertSuccessful();
    $this->put(route('app.files.password', $file->uuid), [
        'password' => 'new-password', 'password_confirmation' => 'new-password',
    ])->assertSessionHasErrors('password');
    expect(Hash::check('first-password', $file->fresh()->password_hash))->toBeTrue();

    $file->update(['password_hash' => 'changed-elsewhere']);
    $this->get(route('app.files.download', $file->uuid))->assertStatus(423);
    $this->put(route('app.files.password', $file->uuid), [
        'current_password' => 'changed-elsewhere', 'password' => null, 'password_confirmation' => null,
    ])->assertRedirect()->assertSessionHasNoErrors();
    expect($file->fresh()->password_hash)->toBeNull();
    $this->get(route('app.files.download', $file->uuid))->assertSuccessful();
});

test('account storage limits reject a whole batch before writing any files', function (): void {
    $user = User::factory()->create();
    $user->forceFill(['free_storage_bytes' => 1500])->save();
    $this->actingAs($user)->post(route('app.files.store'), [
        'files' => [UploadedFile::fake()->create('first.txt', 1), UploadedFile::fake()->create('second.txt', 1)],
    ])->assertSessionHasErrors('files');
    $this->assertDatabaseCount('files', 0);
    $this->assertDatabaseCount('storage_usage_events', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('only current active subscriptions increase storage capacity', function (): void {
    $user = User::factory()->create();
    StorageSubscription::factory()->for($user)->create([
        'status' => 'active', 'additional_storage_bytes' => 1000000000,
        'current_period_starts_at' => now()->subDay(), 'current_period_ends_at' => now()->addMonth(),
    ]);
    StorageSubscription::factory()->for($user)->create([
        'status' => 'active', 'additional_storage_bytes' => 5000000000,
        'current_period_starts_at' => now()->subMonths(2), 'current_period_ends_at' => now()->subMonth(),
    ]);
    StorageSubscription::factory()->for($user)->create([
        'status' => 'pending', 'additional_storage_bytes' => 5000000000,
        'current_period_starts_at' => now()->subDay(), 'current_period_ends_at' => now()->addMonth(),
    ]);
    expect(app(StorageManager::class)->capacity($user))->toBe(3000000000);
    $this->actingAs($user)->get(route('app.dashboard'))->assertSee('of 3 GB used');
});

test('starred trash and restore pages reflect real file state while trash retains storage usage', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('app.files.store'), ['files' => [UploadedFile::fake()->create('my-note.txt', 1, 'text/plain')]]);
    $file = $user->files()->sole();
    $this->patch(route('app.files.star', $file->uuid))->assertRedirect();
    $this->get(route('app.starred'))->assertSee('my-note.txt');
    $this->delete(route('app.files.destroy', $file->uuid))->assertRedirect();
    $this->get(route('app.starred'))->assertDontSee('my-note.txt');
    $this->get(route('app.trash'))->assertSee('my-note.txt');
    $this->get(route('app.files.download', $file->uuid))->assertNotFound();
    expect(app(StorageManager::class)->used($user))->toBe($file->size_bytes);
    $this->patch(route('app.files.restore', $file->uuid))->assertRedirect();
    $this->get(route('app.dashboard'))->assertSee('my-note.txt');
});

test('another account cannot read edit or unlock private resources', function (): void {
    $file = File::factory()->create();
    $folder = Folder::factory()->create();
    $this->actingAs(User::factory()->create());
    foreach (['app.files.show', 'app.files.content', 'app.files.download'] as $routeName) {
        $this->get(route($routeName, $file->uuid))->assertNotFound();
    }
    $this->post(route('app.files.unlock', $file->uuid), ['password' => 'any-password'])->assertNotFound();
    $this->put(route('app.files.password', $file->uuid), ['password' => null])->assertNotFound();
    $this->put(route('app.folders.password', $folder->uuid), ['password' => null])->assertNotFound();
    $this->delete(route('app.files.destroy', $file->uuid))->assertNotFound();
    $this->get(route('app.dashboard'))->assertDontSee($file->original_name)->assertDontSee($folder->name);
});

test('sidebar usage comes from the current account including trashed files', function (): void {
    $user = User::factory()->create();
    File::factory()->for($user)->create(['size_bytes' => 500000000]);
    File::factory()->for($user)->create(['size_bytes' => 500000000, 'deleted_at' => now()]);
    File::factory()->create(['size_bytes' => 1000000000]);
    $this->actingAs($user)->get(route('app.dashboard'))
        ->assertSee('aria-valuenow="50"', false)->assertSee('of 2 GB used')->assertSee('1 GB');
});

test('shared list contains only valid received links and respects download permission', function (): void {
    $user = User::factory()->create();
    $link = FileShareLink::factory()->for($user, 'recipient')->create(['allow_download' => false]);
    $expired = FileShareLink::factory()->for($user, 'recipient')->create(['expires_at' => now()->subDay()]);
    Storage::disk('local')->put($link->file->storage_key, 'shared');
    $this->actingAs($user)->get(route('app.shared'))->assertSee($link->file->original_name)->assertDontSee($expired->file->original_name);
    $this->get(route('app.files.download', $link->file->uuid))->assertNotFound();
    $link->update(['allow_download' => true]);
    $this->get(route('app.files.download', $link->file->uuid))->assertSuccessful();
    $link->update(['revoked_at' => now()]);
    $this->get(route('app.files.download', $link->file->uuid))->assertNotFound();
});

test('uploads cannot be fetched by their physical project paths', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('app.files.store'), ['files' => [UploadedFile::fake()->create('private.txt', 1)]]);
    $file = $user->files()->sole();
    $this->get('/storage/'.$file->storage_key)->assertNotFound();
    $this->get('/storage/app/private/'.$file->storage_key)->assertNotFound();
    expect(config('filesystems.disks.local.serve'))->toBeFalse();
});

test('guests cannot upload or access private file content', function (): void {
    $file = File::factory()->create();
    $this->post(route('app.files.store'), ['files' => [UploadedFile::fake()->image('photo.jpg')]])->assertRedirect(route('login'));
    $this->get(route('app.files.download', $file->uuid))->assertRedirect(route('login'));
});

test('folder creation and password updates hash passwords and invalidate previous unlocks', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('app.folders.store'), [
        'name' => 'Protected folder', 'password' => 'first-password', 'password_confirmation' => 'first-password',
    ])->assertRedirect()->assertSessionHasNoErrors();
    $folder = $user->folders()->sole();
    expect(Hash::check('first-password', $folder->password_hash))->toBeTrue();
    $this->get(route('app.folders.show', $folder->uuid))->assertSee('Enter the password');
    $this->post(route('app.folders.unlock', $folder->uuid), ['password' => 'first-password'])->assertRedirect();
    $this->put(route('app.folders.password', $folder->uuid), [
        'password' => 'new-password', 'password_confirmation' => 'new-password',
    ])->assertRedirect()->assertSessionHasNoErrors();
    expect(Hash::check('new-password', $folder->fresh()->password_hash))->toBeTrue();
    $folder->update(['password_hash' => 'external-change']);
    $this->get(route('app.folders.show', $folder->uuid))->assertSee('Enter the password');
    $this->post(route('app.folders.store'), ['name' => 'Invalid/name'])->assertSessionHasErrors('name');
});

test('partial upload failures roll back rows counters and already written objects', function (): void {
    $user = User::factory()->create();
    $disk = Storage::disk('local');
    $proxy = Mockery::mock($disk);
    $calls = 0;
    $proxy->shouldReceive('put')->andReturnUsing(function (string $path, mixed $contents) use ($disk, &$calls): bool {
        $calls++;

        return $calls === 1 ? $disk->put($path, $contents) : false;
    });
    $proxy->shouldReceive('delete')->once()->andReturnUsing(fn (array $paths): bool => $disk->delete($paths));
    Storage::set('local', $proxy);

    $this->actingAs($user)->post(route('app.files.store'), [
        'files' => [UploadedFile::fake()->image('first.jpg'), UploadedFile::fake()->image('second.jpg')],
    ])->assertSessionHasErrors('files');

    $this->assertDatabaseCount('files', 0);
    $this->assertDatabaseCount('storage_usage_events', 0);
    expect($disk->allFiles())->toBe([])
        ->and($user->fresh()->used_storage_bytes)->toBe(0);
});

test('server search and pagination use real uploads from only the current user', function (): void {
    $user = User::factory()->create();
    File::factory()->for($user)->count(49)->create(['original_name' => 'Quarterly report.pdf']);
    File::factory()->create(['original_name' => 'Other account secret.pdf']);
    $this->actingAs($user)->get(route('app.dashboard', ['q' => 'Quarterly']))
        ->assertSuccessful()->assertSee('Quarterly report.pdf')->assertDontSee('Other account secret.pdf')
        ->assertViewHas('files', fn ($files): bool => $files->total() === 49 && $files->count() === 48);
    $this->get(route('app.dashboard', ['q' => 'Quarterly', 'page' => 2]))
        ->assertViewHas('files', fn ($files): bool => $files->count() === 1);
});

test('a received file share still requires ancestor and file passwords', function (): void {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $folder = Folder::factory()->for($owner)->create(['password_hash' => 'folder-password']);
    $file = File::factory()->for($owner)->for($folder)->create(['password_hash' => 'file-password']);
    FileShareLink::factory()->for($file)->for($recipient, 'recipient')->create();
    Storage::disk('local')->put($file->storage_key, 'private data');

    $this->actingAs($recipient)->get(route('app.files.show', $file->uuid))->assertSee($folder->name)->assertSee(route('app.files.unlock', $file->uuid));
    $this->get(route('app.files.download', $file->uuid))->assertStatus(423);
    $this->post(route('app.files.unlock', $file->uuid), ['password' => 'folder-password'])->assertRedirect(route('app.files.show', $file->uuid));
    $this->get(route('app.files.download', $file->uuid))->assertStatus(423);
    $this->post(route('app.files.unlock', $file->uuid), ['password' => 'file-password'])->assertRedirect();
    $this->get(route('app.files.download', $file->uuid))->assertSuccessful();
    $this->put(route('app.files.password', $file->uuid), ['password' => null])->assertNotFound();
});
