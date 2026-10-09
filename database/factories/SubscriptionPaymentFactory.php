<?php

namespace Database\Factories;

use App\Models\StorageSubscription;
use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPayment>
 */
class SubscriptionPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'storage_subscription_id' => StorageSubscription::factory(),
            'payment_provider' => 'test',
            'idempotency_key' => fake()->uuid(),
            'amount_paise' => fn (array $attributes): int => StorageSubscription::findOrFail($attributes['storage_subscription_id'])->price_paise,
        ];
    }
}
