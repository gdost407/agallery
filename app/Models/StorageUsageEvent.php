<?php

namespace App\Models;

use Database\Factories\StorageUsageEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageUsageEvent extends Model
{
    /** @use HasFactory<StorageUsageEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'file_id',
        'operation',
        'bytes_delta',
        'idempotency_key',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'bytes_delta' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
