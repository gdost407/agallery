<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\FileShareLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FileShareLink>
 */
class FileShareLinkFactory extends Factory
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
            'token_hash' => hash('sha256', fake()->uuid()),
        ];
    }
}
