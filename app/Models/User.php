<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo_path',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'free_storage_bytes' => 2000000000,
        'used_storage_bytes' => 0,
        'reserved_storage_bytes' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'free_storage_bytes' => 'integer',
            'used_storage_bytes' => 'integer',
            'reserved_storage_bytes' => 'integer',
        ];
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function storageUsageEvents(): HasMany
    {
        return $this->hasMany(StorageUsageEvent::class);
    }

    public function fileShareLinks(): HasMany
    {
        return $this->hasMany(FileShareLink::class);
    }

    public function folderShareLinks(): HasMany
    {
        return $this->hasMany(FolderShareLink::class);
    }

    public function receivedFileShareLinks(): HasMany
    {
        return $this->hasMany(FileShareLink::class, 'recipient_user_id');
    }

    public function receivedFolderShareLinks(): HasMany
    {
        return $this->hasMany(FolderShareLink::class, 'recipient_user_id');
    }

    public function storageSubscriptions(): HasMany
    {
        return $this->hasMany(StorageSubscription::class);
    }

    public function subscriptionPayments(): HasManyThrough
    {
        return $this->hasManyThrough(SubscriptionPayment::class, StorageSubscription::class);
    }
}
