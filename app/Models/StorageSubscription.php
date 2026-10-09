<?php

namespace App\Models;

use Database\Factories\StorageSubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorageSubscription extends Model
{
    /** @use HasFactory<StorageSubscriptionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'storage_plan_id',
        'status',
        'additional_storage_bytes',
        'price_paise',
        'currency',
        'billing_interval',
        'payment_provider',
        'provider_subscription_id',
        'starts_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'cancel_at_period_end',
        'cancelled_at',
        'ended_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'pending',
        'currency' => 'INR',
        'billing_interval' => 'month',
        'cancel_at_period_end' => false,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'additional_storage_bytes' => 'integer',
            'price_paise' => 'integer',
            'cancel_at_period_end' => 'boolean',
            'starts_at' => 'datetime',
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function storagePlan(): BelongsTo
    {
        return $this->belongsTo(StoragePlan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
