<?php

namespace App\Models;

use Database\Factories\FileShareLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileShareLink extends Model
{
    /** @use HasFactory<FileShareLinkFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'file_id',
        'token_hash',
        'password_hash',
        'recipient_user_id',
        'allow_download',
        'expires_at',
        'revoked_at',
        'last_accessed_at',
        'access_count',
    ];

    /** @var list<string> */
    protected $hidden = [
        'token_hash',
        'password_hash',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'allow_download' => true,
        'access_count' => 0,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'allow_download' => 'boolean',
            'access_count' => 'integer',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'last_accessed_at' => 'datetime',
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

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
