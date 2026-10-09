<?php

namespace Database\Seeders;

use App\Models\SubscriptionPayment;
use Illuminate\Database\Seeder;

class SubscriptionPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionPayment::factory()->count(3)->create();
    }
}
