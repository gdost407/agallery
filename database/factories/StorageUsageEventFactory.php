<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\StorageUsageEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorageUsageEvent>
 */
class StorageUsageEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_id' => File::factory(),
            'user_id' => fn (array $attributes): int => File::findOrFail($attributes['file_id'])->user_id,
            'operation' => 'upload',
            'bytes_delta' => fn (array $attributes): int => File::findOrFail($attributes['file_id'])->size_bytes,
            'idempotency_key' => fake()->uuid(),
        ];
    }
}
