<?php

namespace Database\Seeders;

use App\Models\StorageUsageEvent;
use Illuminate\Database\Seeder;

class StorageUsageEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StorageUsageEvent::factory()->count(3)->create();
    }
}
