<?php

namespace Database\Factories;

use App\Models\StoragePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoragePlan>
 */
class StoragePlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'test-'.fake()->unique()->uuid(),
            'name' => 'Extra 1 GB',
            'additional_storage_bytes' => 1000000000,
            'price_paise' => 9900,
        ];
    }
}
