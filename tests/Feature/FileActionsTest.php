<?php

use App\Http\StorageManager;
use App\Models\File;
use App\Models\FileShareLink;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

test('batch delete permanently removes selected files and releases storage', function (): void {
    $user = User::factory()->create();
    $files = File::factory()->for($user)->count(2)->create(['size_bytes' => 100]);
    $kept = File::factory()->for($user)->create(['size_bytes' => 100]);
    $this->actingAs($user)->get(route('app.dashboard'))->assertSee('data-file-select', false)->assertSee('Delete selected');
    $this->from(route('app.dashboard'))->delete(route('app.files.bulk-destroy'), ['files' => $files->pluck('uuid')->all()])
        ->assertRedirect(route('app.dashboard'))->assertSessionHasNoErrors();
    foreach ($files as $file) {
        $this->assertModelMissing($file);
    }
    expect($kept->fresh()->trashed())->toBeFalse();
    $this->get(route('app.trash'))->assertDontSee($files[0]->original_name)->assertDontSee($files[1]->original_name);
    expect(app(StorageManager::class)->used($user))->toBe(100);
});

test('batch deletion rejects foreign duplicate or locked files without deleting any selected files', function (): void {
    $user = User::factory()->create();
    $owned = File::factory()->for($user)->create();
    $foreign = File::factory()->create();
    $folder = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    $locked = File::factory()->for($user)->for($folder)->create();
    $this->actingAs($user)->deleteJson(route('app.files.bulk-destroy'), ['files' => [$owned->uuid, $foreign->uuid]])->assertUnprocessable();
    $this->deleteJson(route('app.files.bulk-destroy'), ['files' => [$owned->uuid, $owned->uuid]])->assertUnprocessable();
    $this->deleteJson(route('app.files.bulk-destroy'), ['files' => [$owned->uuid, $locked->uuid]])->assertStatus(423);
    expect($owned->fresh()->trashed())->toBeFalse()->and($locked->fresh()->trashed())->toBeFalse();
    $this->post(route('app.folders.unlock', $folder->uuid), ['password' => 'folder-secret'])->assertRedirect();
    $this->delete(route('app.files.bulk-destroy'), ['files' => [$owned->uuid, $locked->uuid]])->assertRedirect();
    $this->assertModelMissing($owned);
    $this->assertModelMissing($locked);
});

test('copy creates an independent private file and increases counters and history', function (): void {
    $user = User::factory()->create(['used_storage_bytes' => 12]);
    $file = File::factory()->for($user)->create(['size_bytes' => 12]);
    $folder = Folder::factory()->for($user)->create();
    Storage::disk('local')->put($file->storage_key, 'copy content');
    $this->actingAs($user)->post(route('app.files.copy', $file->uuid), ['folder_id' => $folder->id])->assertRedirect()->assertSessionHasNoErrors();
    $copy = $user->files()->where('id', '!=', $file->id)->sole();
    expect($copy->uuid)->not->toBe($file->uuid)->and($copy->storage_key)->not->toBe($file->storage_key)
        ->and($copy->folder_id)->toBe($folder->id)->and($file->fresh()->folder_id)->toBeNull()
        ->and($user->fresh()->used_storage_bytes)->toBe(24)
        ->and(Storage::disk('local')->get($copy->storage_key))->toBe('copy content');
    $this->assertDatabaseHas('storage_usage_events', ['file_id' => $copy->id, 'bytes_delta' => 12]);
});

test('copy enforces quota and failed disk copies leave no new records or objects', function (): void {
    $user = User::factory()->create(['free_storage_bytes' => 15, 'used_storage_bytes' => 12]);
    $file = File::factory()->for($user)->create(['size_bytes' => 12]);
    Storage::disk('local')->put($file->storage_key, 'copy content');
    $this->actingAs($user)->postJson(route('app.files.copy', $file->uuid), ['folder_id' => null])->assertUnprocessable()->assertJsonValidationErrors('folder_id');
    $this->assertDatabaseCount('files', 1);
    Storage::disk('local')->assertCount('', 1, true);
    $user->forceFill(['free_storage_bytes' => 100])->save();
    $disk = Storage::disk('local');
    $proxy = Mockery::mock($disk);
    $proxy->shouldReceive('exists')->andReturnTrue();
    $proxy->shouldReceive('copy')->once()->andReturnFalse();
    $proxy->shouldReceive('delete')->once()->andReturnTrue();
    Storage::set('local', $proxy);
    $this->postJson(route('app.files.copy', $file->uuid), ['folder_id' => null])->assertUnprocessable();
    $this->assertDatabaseCount('files', 1);
    expect($user->fresh()->used_storage_bytes)->toBe(12);
});

test('move changes only the folder and retains bytes storage key shares and counters', function (): void {
    $user = User::factory()->create(['used_storage_bytes' => 12]);
    $file = File::factory()->for($user)->create(['size_bytes' => 12]);
    $folder = Folder::factory()->for($user)->create();
    Storage::disk('local')->put($file->storage_key, 'move content');
    $link = FileShareLink::factory()->for($file)->create();
    $this->actingAs($user)->patch(route('app.files.move', $file->uuid), ['folder_id' => $folder->id])->assertRedirect(route('app.files.show', $file->uuid));
    expect($file->fresh()->folder_id)->toBe($folder->id)->and($file->fresh()->storage_key)->toBe($file->storage_key)
        ->and($user->fresh()->used_storage_bytes)->toBe(12)->and($link->fresh()->file_id)->toBe($file->id);
    $this->patch(route('app.files.move', $file->uuid), ['folder_id' => null])->assertRedirect();
    expect($file->fresh()->folder->system_key)->toBe('document');
    $this->assertDatabaseCount('files', 1);
    $this->assertDatabaseCount('storage_usage_events', 0);
});

test('transfer validates source ownership destination ownership and both folder passwords', function (): void {
    $user = User::factory()->create();
    $file = File::factory()->for($user)->create();
    $foreign = Folder::factory()->create();
    $protected = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    Storage::disk('local')->put($file->storage_key, 'source');
    foreach (['app.files.copy', 'app.files.move'] as $route) {
        $method = $route === 'app.files.copy' ? 'postJson' : 'patchJson';
        $this->actingAs($user)->{$method}(route($route, $file->uuid), ['folder_id' => $foreign->id])->assertUnprocessable();
        $this->{$method}(route($route, $file->uuid), ['folder_id' => $protected->id])->assertStatus(423);
        $this->{$method}(route($route, $file->uuid), [])->assertUnprocessable();
        $this->actingAs($foreign->user)->{$method}(route($route, $file->uuid), ['folder_id' => null])->assertNotFound();
    }
    $file->update(['folder_id' => $protected->id]);
    $this->actingAs($user)->patchJson(route('app.files.move', $file->uuid), ['folder_id' => null])->assertStatus(423);
    $this->post(route('app.folders.unlock', $protected->uuid), ['password' => 'folder-secret'])->assertRedirect();
    $this->patch(route('app.files.move', $file->uuid), ['folder_id' => null])->assertRedirect();
});

test('viewer navigates between photos and videos in the same folder and skips locked deleted and other files', function (): void {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $older = File::factory()->for($user)->for($folder)->create(['mime_type' => 'video/mp4', 'category' => 'video', 'created_at' => now()->subMinutes(2)]);
    $current = File::factory()->for($user)->for($folder)->create(['mime_type' => 'image/jpeg', 'category' => 'image', 'created_at' => now()->subMinute()]);
    $newer = File::factory()->for($user)->for($folder)->create(['mime_type' => 'image/png', 'category' => 'image']);
    File::factory()->for($user)->for($folder)->create(['mime_type' => 'image/jpeg', 'password_hash' => 'file-secret', 'created_at' => now()->subSeconds(30)]);
    File::factory()->for($user)->for($folder)->create(['mime_type' => 'image/jpeg', 'deleted_at' => now()]);
    File::factory()->for($user)->create(['mime_type' => 'image/jpeg']);
    $this->actingAs($user)->get(route('app.files.show', $current->uuid))->assertSuccessful()
        ->assertViewHas('previousFile', fn ($file): bool => $file->is($newer))
        ->assertViewHas('nextFile', fn ($file): bool => $file->is($older))
        ->assertSee('fileDetailsPanel', false)->assertSee($folder->name)->assertSee($current->mime_type)
        ->assertSee(route('app.files.copy', $current->uuid), false)->assertSee(route('app.files.move', $current->uuid), false);
    $this->get(route('app.files.show', $older->uuid))->assertViewHas('nextFile', null);
});

test('shared viewer navigation and actions are restricted to valid received shares', function (): void {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $current = File::factory()->for($owner)->create(['mime_type' => 'image/jpeg', 'created_at' => now()->subMinute()]);
    $shared = File::factory()->for($owner)->create(['mime_type' => 'video/mp4']);
    $private = File::factory()->for($owner)->create(['mime_type' => 'image/jpeg', 'created_at' => now()->subSeconds(30)]);
    foreach ([$current, $shared] as $file) {
        FileShareLink::factory()->for($file)->for($recipient, 'recipient')->create();
    }
    $this->actingAs($recipient)->get(route('app.files.show', $current->uuid))->assertSuccessful()
        ->assertViewHas('previousFile', fn ($file): bool => $file->is($shared))
        ->assertDontSee(route('app.files.show', $private->uuid), false)
        ->assertDontSee(route('app.files.copy', $current->uuid), false)->assertDontSee('method="POST" action="'.route('app.files.destroy', $current->uuid).'"', false);
    $this->postJson(route('app.files.copy', $current->uuid), ['folder_id' => null])->assertNotFound();
});
