<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->orderBy('id')->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                DB::transaction(function () use ($user): void {
                    $folders = [];
                    foreach (['image' => 'Image', 'video' => 'Video', 'document' => 'Document', 'trash' => 'Trash'] as $key => $name) {
                        $existing = DB::table('folders')->where('user_id', $user->id)->where('system_key', $key)->first();
                        $folders[$key] = $existing?->id ?? DB::table('folders')->insertGetId([
                            'uuid' => (string) Str::uuid(), 'user_id' => $user->id, 'name' => $name, 'system_key' => $key,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                    DB::table('files')->where('user_id', $user->id)->orderBy('id')->chunkById(100, function ($files) use ($user, $folders): void {
                        foreach ($files as $file) {
                            $prefix = match ($file->category) {
                                'image' => 'IMG', 'video' => 'VID', 'document' => 'DOC', default => 'FILE'
                            };
                            $extension = preg_match('/^[a-z0-9]{1,32}$/i', $file->extension) ? '.'.strtolower($file->extension) : '';
                            DB::table('files')->where('id', $file->id)->update([
                                'folder_id' => $file->folder_id ?? $folders[in_array($file->category, ['image', 'video'], true) ? $file->category : 'document'],
                                'stored_name' => $file->stored_name ?? $prefix.$user->id.now()->format('YmdHisv').'_'.$file->id.$extension,
                            ]);
                        }
                    });
                });
            }
        });
    }

    public function down(): void
    {
        // Keep file associations and default folders to preserve users' organization on rollback.
    }
};
