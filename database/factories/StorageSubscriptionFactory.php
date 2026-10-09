<?php

namespace Database\Factories;

use App\Models\StoragePlan;
use App\Models\StorageSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorageSubscription>
 */
class StorageSubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'storage_plan_id' => StoragePlan::factory(),
            'additional_storage_bytes' => fn (array $attributes): int => StoragePlan::findOrFail($attributes['storage_plan_id'])->additional_storage_bytes,
            'price_paise' => fn (array $attributes): int => StoragePlan::findOrFail($attributes['storage_plan_id'])->price_paise,
        ];
    }
}
