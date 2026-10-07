<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('free_storage_bytes')->default(2000000000);
            $table->unsignedBigInteger('used_storage_bytes')->default(0);
            $table->unsignedBigInteger('reserved_storage_bytes')->default(0);
        });

        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('folders')->restrictOnDelete();
            $table->string('name');
            $table->string('password_hash')->nullable()->comment('Hash only. Application must enforce protection for all descendants.');
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'parent_id', 'deleted_at']);
        });

        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('folders')->restrictOnDelete();
            $table->string('original_name');
            $table->string('extension', 32)->default('');
            $table->string('mime_type', 191);
            $table->string('category', 32)->default('other');
            $table->string('disk', 32)->default('local')->comment('Private disk only, even for files accessible by share link.');
            $table->string('storage_key');
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum_sha256', 64)->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamp('starred_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['disk', 'storage_key']);
            $table->index(['user_id', 'folder_id', 'deleted_at']);
            $table->index(['user_id', 'extension', 'deleted_at']);
            $table->index(['user_id', 'category', 'deleted_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('storage_usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->string('operation', 32);
            $table->bigInteger('bytes_delta')->comment('Signed change in stored bytes. Trash counts until physical deletion.');
            $table->uuid('idempotency_key')->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('file_share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->string('password_hash')->nullable();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('allow_download')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->unsignedBigInteger('access_count')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'file_id']);
        });

        Schema::create('folder_share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('folder_id')->constrained('folders')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->string('password_hash')->nullable();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('allow_download')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->unsignedBigInteger('access_count')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'folder_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folder_share_links');
        Schema::dropIfExists('file_share_links');
        Schema::dropIfExists('storage_usage_events');
        Schema::dropIfExists('files');
        Schema::dropIfExists('folders');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['free_storage_bytes', 'used_storage_bytes', 'reserved_storage_bytes']);
        });
    }
};
