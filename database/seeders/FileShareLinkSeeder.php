<?php

namespace Database\Seeders;

use App\Models\FileShareLink;
use Illuminate\Database\Seeder;

class FileShareLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FileShareLink::factory()->count(3)->create();
    }
}
