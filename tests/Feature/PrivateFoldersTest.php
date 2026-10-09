<?php

use App\Models\File;
use App\Models\FileShareLink;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

test('private folder trees and counts are excluded from every normal gallery even when unlocked', function (): void {
    $user = User::factory()->create();
    $normal = Folder::factory()->for($user)->create(['name' => 'Normal folder']);
    $private = Folder::factory()->for($user)->for($normal, 'parent')->create(['name' => 'Hidden folder', 'password_hash' => 'folder-secret']);
    $child = Folder::factory()->for($user)->for($private, 'parent')->create(['name' => 'Hidden child']);
    $visible = File::factory()->for($user)->create(['category' => 'image', 'original_name' => 'Visible photo.jpg', 'starred_at' => now()]);
    $hidden = File::factory()->for($user)->for($child)->create(['category' => 'image', 'original_name' => 'Hidden photo.jpg', 'starred_at' => now()]);
    File::factory()->for($user)->for($private)->create(['original_name' => 'Hidden document.pdf']);
    File::factory()->for($user)->for($child)->create(['category' => 'video', 'original_name' => 'Hidden video.mp4']);
    $trashed = File::factory()->for($user)->for($child)->create(['deleted_at' => now(), 'original_name' => 'Hidden trash.pdf']);
    foreach (['app.dashboard', 'app.photos', 'app.videos', 'app.documents', 'app.recent', 'app.starred', 'app.trash'] as $route) {
        $this->actingAs($user)->get(route($route))->assertSuccessful()->assertDontSee($private->name)
            ->assertDontSee($hidden->original_name)->assertDontSee('Hidden document.pdf')
            ->assertDontSee('Hidden video.mp4')->assertDontSee($trashed->original_name);
    }
    $this->get(route('app.photos'))->assertSee($visible->original_name)->assertViewHas('files', fn ($files): bool => $files->total() === 1);
    $this->get(route('app.folders.show', $normal->uuid))->assertDontSee($private->name);
    $this->get(route('app.private'))->assertSee($private->name)->assertDontSee($child->name)->assertDontSee($hidden->original_name);
    $this->get(route('app.folders.show', $private->uuid))->assertSee('Enter the password')->assertDontSee($child->name);
    $this->post(route('app.folders.unlock', $private->uuid), ['password' => 'folder-secret'])->assertRedirect();
    $this->get(route('app.folders.show', $private->uuid))->assertSee($child->name)->assertSee('Hidden document.pdf');
    $this->get(route('app.folders.show', $child->uuid))->assertSee($hidden->original_name);
    $this->get(route('app.photos'))->assertDontSee($hidden->original_name)->assertViewHas('files', fn ($files): bool => $files->total() === 1);
});

test('private folder list belongs to current user and normal visibility returns when protection is removed', function (): void {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    $foreign = Folder::factory()->create(['password_hash' => 'other-secret']);
    $file = File::factory()->for($user)->for($folder)->create(['category' => 'image']);
    $this->actingAs($user)->get(route('app.private'))->assertSee($folder->name)->assertDontSee($foreign->name);
    $this->get(route('app.folders.show', $foreign->uuid))->assertNotFound();
    $this->put(route('app.folders.password', $folder->uuid), ['current_password' => 'folder-secret', 'password' => null])->assertRedirect();
    $this->get(route('app.private'))->assertDontSee($folder->name);
    $this->get(route('app.dashboard'))->assertSee($folder->name);
    $this->get(route('app.photos'))->assertSee($file->original_name);
});

test('received private files stay hidden from shared list while direct links still require folder unlock', function (): void {
    $recipient = User::factory()->create();
    $owner = User::factory()->create();
    $folder = Folder::factory()->for($owner)->create(['password_hash' => 'folder-secret']);
    $file = File::factory()->for($owner)->for($folder)->create();
    FileShareLink::factory()->for($file)->for($recipient, 'recipient')->create();
    $this->actingAs($recipient)->get(route('app.shared'))->assertDontSee($file->original_name)->assertViewHas('files', fn ($files): bool => $files->total() === 0);
    $this->get(route('app.files.show', $file->uuid))->assertSee('Enter the password');
});

test('uploads inherit only folder protection and reject individual file passwords', function (): void {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    $this->actingAs($user)->post(route('app.folders.unlock', $folder->uuid), ['password' => 'folder-secret'])->assertRedirect();
    $this->postJson(route('app.files.store'), ['folder_id' => $folder->id, 'files' => [UploadedFile::fake()->image('private.jpg')]])
        ->assertSuccessful()->assertJsonPath('redirect', route('app.folders.show', $folder->uuid));
    $file = $user->files()->sole();
    expect($file->password_hash)->toBeNull();
    $this->get(route('app.folders.show', $folder->uuid))->assertSee($file->original_name)->assertDontSee('File password');
    $this->travel(31)->minutes();
    $this->get(route('app.files.content', $file->uuid))->assertStatus(423);
    $this->postJson(route('app.files.store'), ['files' => [UploadedFile::fake()->image('blocked.jpg')], 'password' => 'file-secret'])
        ->assertUnprocessable()->assertJsonValidationErrors('password');
    $this->putJson(route('app.files.password', $file->uuid), ['password' => 'file-secret'])->assertUnprocessable()->assertJsonValidationErrors('password');
    expect($user->files()->count())->toBe(1);
});

test('all section uploads return JSON destinations for progress uploads', function (string $route, string $destination, string $name, string $mime): void {
    $user = User::factory()->create();
    $upload = $mime === 'image/jpeg' ? UploadedFile::fake()->image($name) : UploadedFile::fake()->create($name, 1, $mime);
    $this->actingAs($user)->postJson(route($route), ['files' => [$upload]])
        ->assertSuccessful()->assertJsonPath('redirect', route($destination))->assertSessionHas('status', 'Files uploaded.');
})->with([
    ['app.photos.store', 'app.photos', 'photo.jpg', 'image/jpeg'],
    ['app.videos.store', 'app.videos', 'video.mp4', 'video/mp4'],
    ['app.documents.store', 'app.documents', 'document.pdf', 'application/pdf'],
]);

test('upload progress receives validation errors and protected folder failures as JSON', function (): void {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    $this->actingAs($user)->postJson(route('app.files.store'), [])->assertUnprocessable()->assertJsonValidationErrors('files');
    $this->postJson(route('app.files.store'), ['folder_id' => $folder->id, 'files' => [UploadedFile::fake()->image('photo.jpg')]])->assertStatus(423);
    $this->get(route('app.dashboard'))->assertSee('data-upload-progress', false)->assertSee('upload.js')->assertDontSee('uploadPassword', false);
    $this->assertDatabaseCount('files', 0);
});
