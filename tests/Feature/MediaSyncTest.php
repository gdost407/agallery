<?php

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

test('sync status compares hashes only against the signed in accounts saved files', function (): void {
    $user = User::factory()->create();
    $saved = str_repeat('a', 64);
    $trashed = str_repeat('b', 64);
    $foreign = str_repeat('c', 64);
    $pending = str_repeat('d', 64);
    File::factory()->for($user)->create(['checksum_sha256' => $saved]);
    File::factory()->for($user)->create(['checksum_sha256' => $trashed, 'deleted_at' => now()]);
    File::factory()->create(['checksum_sha256' => $foreign]);
    File::factory()->for($user)->create(['checksum_sha256' => $pending, 'status' => 'pending']);
    $this->actingAs($user)->postJson(route('app.media-sync.status'), ['checksums' => [$saved, $trashed, $foreign, $pending]])
        ->assertSuccessful()->assertExactJson(['synced' => [$saved, $trashed]])->assertHeader('Cache-Control', 'no-store, private');
});

test('sync status requires authentication and validates bounded checksum batches', function (): void {
    $this->postJson(route('app.media-sync.status'), ['checksums' => [str_repeat('a', 64)]])->assertUnauthorized();
    $this->actingAs(User::factory()->create())->postJson(route('app.media-sync.status'), ['checksums' => ['bad']])->assertUnprocessable();
    $this->postJson(route('app.media-sync.status'), ['checksums' => array_fill(0, 101, str_repeat('a', 64))])->assertUnprocessable();
});

test('sync uploads use existing endpoints and deduplicate content across names and retries', function (): void {
    $user = User::factory()->create();
    $photo = UploadedFile::fake()->image('first.jpg');
    $bytes = file_get_contents($photo->getRealPath());
    $this->actingAs($user)->postJson(route('app.files.store'), ['sync' => '1', 'files' => [$photo]])->assertSuccessful()->assertJsonStructure(['storage' => ['used', 'remaining']]);
    $file = $user->files()->sole();
    expect($file->folder->system_key)->toBe('image')->and($file->checksum_sha256)->toBe(hash('sha256', $bytes));
    $user->forceFill(['free_storage_bytes' => $file->size_bytes])->save();
    $this->postJson(route('app.files.store'), ['sync' => '1', 'files' => [UploadedFile::fake()->createWithContent('renamed.jpg', $bytes)]])->assertSuccessful();
    $this->assertDatabaseCount('files', 1);
    $this->assertDatabaseCount('storage_usage_events', 1);
    Storage::disk('local')->assertCount('', 1, true);
    expect($user->fresh()->used_storage_bytes)->toBe($file->size_bytes);
    $this->delete(route('app.files.destroy', $file->uuid))->assertRedirect();
    $this->postJson(route('app.files.store'), ['sync' => '1', 'files' => [UploadedFile::fake()->createWithContent('renamed.jpg', $bytes)]])->assertSuccessful();
    expect($user->files()->sole()->uuid)->not->toBe($file->uuid);
});

test('sync accepts videos and rejects non media while respecting storage quota', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson(route('app.videos.store'), ['sync' => '1', 'files' => [UploadedFile::fake()->create('clip.mp4', 1, 'video/mp4')]])->assertSuccessful();
    expect($user->files()->sole()->folder->system_key)->toBe('video');
    $this->postJson(route('app.files.store'), ['sync' => '1', 'files' => [UploadedFile::fake()->create('note.txt', 1, 'text/plain')]])->assertUnprocessable();
    $user->forceFill(['free_storage_bytes' => 1024])->save();
    $this->postJson(route('app.files.store'), ['sync' => '1', 'files' => [UploadedFile::fake()->image('new.jpg')]])->assertUnprocessable()->assertJsonValidationErrors('files');
    $this->assertDatabaseCount('files', 1);
});

test('manual uploads still allow duplicate content and the app renders account scoped sync controls', function (): void {
    $user = User::factory()->create();
    $photo = UploadedFile::fake()->image('one.jpg');
    $bytes = file_get_contents($photo->getRealPath());
    $this->actingAs($user)->postJson(route('app.files.store'), ['files' => [$photo]])->assertSuccessful();
    $this->postJson(route('app.files.store'), ['files' => [UploadedFile::fake()->createWithContent('two.jpg', $bytes)]])->assertSuccessful();
    $this->assertDatabaseCount('files', 2);
    $this->get(route('app.dashboard'))->assertSuccessful()->assertDontSee('data-media-sync', false);
    $this->get(route('profile.edit'))->assertSuccessful()->assertSee('data-media-sync', false)
        ->assertSee('data-account="'.$user->id.'"', false)->assertSee('media-sync.js', false);
});
