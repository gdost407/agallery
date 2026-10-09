<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<File>
 */
class FileFactory extends Factory
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
            'original_name' => fake()->word().'.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'category' => 'document',
            'storage_key' => 'files/'.fake()->uuid().'.pdf',
            'size_bytes' => fake()->numberBetween(100, 10000000),
            'status' => 'ready',
        ];
    }
}
