<?php

namespace App\Models;

use Database\Factories\StoragePlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoragePlan extends Model
{
    /** @use HasFactory<StoragePlanFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'additional_storage_bytes',
        'price_paise',
        'currency',
        'billing_interval',
        'is_active',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'currency' => 'INR',
        'billing_interval' => 'month',
        'is_active' => true,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'additional_storage_bytes' => 'integer',
            'price_paise' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(StorageSubscription::class);
    }
}
