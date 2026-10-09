<?php

namespace Database\Factories;

use App\Models\Folder;
use App\Models\FolderShareLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FolderShareLink>
 */
class FolderShareLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'folder_id' => Folder::factory(),
            'user_id' => fn (array $attributes): int => Folder::findOrFail($attributes['folder_id'])->user_id,
            'token_hash' => hash('sha256', fake()->uuid()),
        ];
    }
}
