<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table): void {
            $table->string('system_key', 32)->nullable();
            $table->unique(['user_id', 'system_key']);
        });
        Schema::table('files', function (Blueprint $table): void {
            $table->string('stored_name')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table): void {
            $table->dropUnique(['stored_name']);
            $table->dropColumn('stored_name');
        });
        Schema::table('folders', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'system_key']);
            $table->dropColumn('system_key');
        });
    }
};
