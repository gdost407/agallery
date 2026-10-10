<?php

use App\Http\LibraryFolders;
use App\Http\StorageManager;
use App\Models\File;
use App\Models\FileShareLink;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Storage::fake('local');
});

test('permanent file deletion removes bytes thumbnails shares history and storage usage', function (bool $trashed): void {
    $user = User::factory()->create(['used_storage_bytes' => 120]);
    $file = File::factory()->for($user)->create(['size_bytes' => 100, 'deleted_at' => $trashed ? now() : null]);
    File::factory()->for($user)->create(['size_bytes' => 20]);
    $link = FileShareLink::factory()->for($file)->create(['user_id' => $user->id]);
    $event = $user->storageUsageEvents()->create(['file_id' => $file->id, 'operation' => 'upload', 'bytes_delta' => 100, 'idempotency_key' => (string) Str::uuid()]);
    Storage::disk('local')->put($file->storage_key, 'file bytes');
    Storage::disk('local')->put('thumbnails/'.$file->uuid.'/preview-v1.jpg', 'thumbnail');
    $this->actingAs($user)->delete(route('app.files.destroy', $file->uuid))->assertRedirect()->assertSessionHasNoErrors();
    $this->assertModelMissing($file);
    $this->assertModelMissing($link);
    $this->assertModelMissing($event);
    Storage::disk('local')->assertMissing($file->storage_key);
    Storage::disk('local')->assertDirectoryEmpty('thumbnails/'.$file->uuid);
    expect($user->fresh()->used_storage_bytes)->toBe(20)->and(app(StorageManager::class)->used($user))->toBe(20);
})->with([false, true]);

test('deleting a folder removes descendants and trashed files but preserves other files', function (): void {
    $user = User::factory()->create();
    $root = Folder::factory()->for($user)->create();
    $child = Folder::factory()->for($user)->create(['parent_id' => $root->id]);
    $grandchild = Folder::factory()->for($user)->create(['parent_id' => $child->id]);
    $files = collect([$root, $child, $grandchild])->map(fn (Folder $folder) => File::factory()->for($user)->for($folder)->create(['size_bytes' => 100, 'deleted_at' => $folder->is($child) ? now() : null]));
    $kept = File::factory()->for($user)->create(['size_bytes' => 50]);
    foreach ($files as $file) {
        Storage::disk('local')->put($file->storage_key, 'bytes');
    }
    $this->actingAs($user)->delete(route('app.folders.destroy', $root->uuid))->assertRedirect(route('app.folders'))->assertSessionHasNoErrors();
    foreach ([$root, $child, $grandchild, ...$files] as $item) {
        $this->assertModelMissing($item);
    }
    foreach ($files as $file) {
        Storage::disk('local')->assertMissing($file->storage_key);
    }
    expect($kept->fresh())->not->toBeNull()->and($user->fresh()->used_storage_bytes)->toBe(50);
});

test('folder deletion rejects another owner default folders and locked descendants before deleting bytes', function (): void {
    $user = User::factory()->create();
    app(LibraryFolders::class)->ensure($user);
    $root = Folder::factory()->for($user)->create();
    $child = Folder::factory()->for($user)->create(['parent_id' => $root->id, 'password_hash' => 'secret']);
    $file = File::factory()->for($user)->for($root)->create();
    Storage::disk('local')->put($file->storage_key, 'bytes');
    $this->actingAs(User::factory()->create())->deleteJson(route('app.folders.destroy', $root->uuid))->assertNotFound();
    $this->actingAs($user)->deleteJson(route('app.folders.destroy', $user->folders()->where('system_key', 'document')->sole()->uuid))->assertUnprocessable();
    $this->deleteJson(route('app.folders.destroy', $root->uuid))->assertStatus(423);
    Storage::disk('local')->assertExists($file->storage_key);
    expect($root->fresh())->not->toBeNull();
    $this->post(route('app.folders.unlock', $child->uuid), ['password' => 'secret'])->assertRedirect();
    $this->delete(route('app.folders.destroy', $root->uuid))->assertRedirect()->assertSessionHasNoErrors();
    $this->assertModelMissing($root);
});

test('failed physical deletion retains the database record and storage counter for retry', function (): void {
    $user = User::factory()->create(['used_storage_bytes' => 100]);
    $file = File::factory()->for($user)->create(['size_bytes' => 100]);
    $disk = Mockery::mock(Storage::disk('local'));
    $disk->shouldReceive('delete')->once()->with($file->storage_key)->andReturnFalse();
    Storage::set('local', $disk);
    $this->actingAs($user)->deleteJson(route('app.files.destroy', $file->uuid))->assertUnprocessable()->assertJsonValidationErrors('files');
    expect($file->fresh())->not->toBeNull()->and($user->fresh()->used_storage_bytes)->toBe(100);
});

test('folder explorer renders nested navigation and only the selected folders documents', function (): void {
    $user = User::factory()->create();
    $root = Folder::factory()->for($user)->create(['name' => 'Projects']);
    $child = Folder::factory()->for($user)->create(['parent_id' => $root->id, 'name' => 'Reports']);
    $inside = File::factory()->for($user)->for($child)->create(['original_name' => 'inside.pdf']);
    $outside = File::factory()->for($user)->for($root)->create(['original_name' => 'outside.pdf']);
    $this->actingAs($user)->get(route('app.folders.show', $child->uuid))->assertSuccessful()
        ->assertSee('Folder explorer')->assertSee('folder-tree-children', false)
        ->assertSee('Projects')->assertSee('Reports')->assertSee($inside->original_name)->assertDontSee($outside->original_name)
        ->assertSee('aria-current="page"', false)->assertSee('Delete folder permanently');
});
