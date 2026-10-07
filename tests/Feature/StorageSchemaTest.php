<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('users receive lifetime free storage without a subscription', function () {
    $user = User::factory()->create()->fresh();

    expect($user->free_storage_bytes)->toBe(2000000000)
        ->and($user->used_storage_bytes)->toBe(0)
        ->and($user->reserved_storage_bytes)->toBe(0);
    $this->assertDatabaseCount('storage_subscriptions', 0);
});

test('monthly storage plans have the requested INR prices and extra capacity', function (string $code, int $bytes, int $price) {
    $this->assertDatabaseHas('storage_plans', [
        'code' => $code,
        'additional_storage_bytes' => $bytes,
        'price_paise' => $price,
        'currency' => 'INR',
        'billing_interval' => 'month',
        'is_active' => true,
    ]);
})->with([
    ['extra-1gb-monthly', 1000000000, 9900],
    ['extra-2gb-monthly', 2000000000, 19900],
    ['extra-5gb-monthly', 5000000000, 39900],
]);

test('file sizes support extension totals and keep trashed bytes accounted for', function () {
    $user = User::factory()->create();
    foreach ([['jpg', 3000000000, null], ['jpg', 100, now()], ['pdf', 500, null]] as [$extension, $size, $deletedAt]) {
        DB::table('files')->insert([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'original_name' => 'example.'.$extension,
            'extension' => $extension,
            'mime_type' => $extension === 'jpg' ? 'image/jpeg' : 'application/pdf',
            'storage_key' => (string) Str::uuid(),
            'size_bytes' => $size,
            'deleted_at' => $deletedAt,
        ]);
    }

    $totals = DB::table('files')->where('user_id', $user->id)
        ->selectRaw('extension, SUM(size_bytes) AS total_bytes')
        ->groupBy('extension')->pluck('total_bytes', 'extension');

    expect((int) $totals['jpg'])->toBe(3000000100)
        ->and((int) $totals['pdf'])->toBe(500);
});

test('permanently deleting a populated folder cannot expose children at the root', function () {
    $user = User::factory()->create();
    $parent = DB::table('folders')->insertGetId([
        'uuid' => (string) Str::uuid(), 'user_id' => $user->id, 'name' => 'Protected',
    ]);
    DB::table('folders')->insert([
        'uuid' => (string) Str::uuid(), 'user_id' => $user->id, 'parent_id' => $parent, 'name' => 'Child',
    ]);

    expect(fn () => DB::table('folders')->where('id', $parent)->delete())->toThrow(QueryException::class);
});

test('storage migrations can be reversed and reapplied', function () {
    $paths = [
        database_path('migrations/2026_10_07_165215_create_library_storage_tables.php'),
        database_path('migrations/2026_10_07_165215_create_storage_subscription_tables.php'),
        database_path('migrations/2026_10_07_165232_insert_default_storage_plans.php'),
    ];
    foreach (array_reverse($paths) as $path) {
        (require $path)->down();
    }
    expect(Schema::hasTable('files'))->toBeFalse()
        ->and(Schema::hasTable('storage_plans'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'free_storage_bytes'))->toBeFalse();

    foreach ($paths as $path) {
        (require $path)->up();
    }
    $this->assertDatabaseCount('storage_plans', 3);
    expect(Schema::hasTable('folder_share_links'))->toBeTrue();
});
