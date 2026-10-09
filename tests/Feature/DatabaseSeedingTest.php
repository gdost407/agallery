<?php

use App\Models\StoragePlan;
use Database\Seeders\DatabaseSeeder;

test('default seeding creates reference plans without factory generated users', function (): void {
    StoragePlan::query()->delete();
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('storage_plans', 3);
    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseHas('storage_plans', ['code' => 'extra-1gb-monthly', 'price_paise' => 9900]);
});

test('default seeding preserves existing plan changes', function (): void {
    $plan = StoragePlan::where('code', 'extra-1gb-monthly')->sole();
    $plan->update(['price_paise' => 10900, 'is_active' => false]);

    $this->seed(DatabaseSeeder::class);

    expect($plan->fresh()->price_paise)->toBe(10900)
        ->and($plan->fresh()->is_active)->toBeFalse();
});
