<?php

namespace App\Models;

use Database\Factories\SubscriptionPaymentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    /** @use HasFactory<SubscriptionPaymentFactory> */
    use HasFactory, HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'storage_subscription_id',
        'payment_provider',
        'provider_order_id',
        'provider_payment_id',
        'idempotency_key',
        'amount_paise',
        'refunded_amount_paise',
        'currency',
        'status',
        'period_starts_at',
        'period_ends_at',
        'paid_at',
        'failed_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'refunded_amount_paise' => 0,
        'currency' => 'INR',
        'status' => 'pending',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount_paise' => 'integer',
            'refunded_amount_paise' => 'integer',
            'period_starts_at' => 'datetime',
            'period_ends_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function storageSubscription(): BelongsTo
    {
        return $this->belongsTo(StorageSubscription::class);
    }
}
