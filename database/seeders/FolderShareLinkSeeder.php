<?php

namespace Database\Seeders;

use App\Models\FolderShareLink;
use Illuminate\Database\Seeder;

class FolderShareLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FolderShareLink::factory()->count(3)->create();
    }
}
