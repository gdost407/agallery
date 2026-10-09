<?php

namespace Database\Seeders;

use App\Models\StorageSubscription;
use Illuminate\Database\Seeder;

class StorageSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StorageSubscription::factory()->count(3)->create();
    }
}
