<?php

namespace App\Models;

use App\Http\LibraryFolders;
use Database\Factories\FileFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    /** @use HasFactory<FileFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'folder_id',
        'original_name',
        'stored_name',
        'extension',
        'mime_type',
        'category',
        'disk',
        'storage_key',
        'size_bytes',
        'checksum_sha256',
        'status',
        'starred_at',
        'password_hash',
        'password_changed_at',
    ];

    /** @var list<string> */
    protected $hidden = [
        'disk',
        'storage_key',
        'password_hash',
        'stored_name',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'extension' => '',
        'category' => 'other',
        'disk' => 'local',
        'status' => 'pending',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'starred_at' => 'datetime',
            'password_hash' => 'hashed',
            'password_changed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (File $file): void {
            $file->stored_name ??= LibraryFolders::storedName($file->user_id, $file->category, $file->extension);
        });
    }

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function shareLinks(): HasMany
    {
        return $this->hasMany(FileShareLink::class);
    }

    public function storageUsageEvents(): HasMany
    {
        return $this->hasMany(StorageUsageEvent::class);
    }
}
