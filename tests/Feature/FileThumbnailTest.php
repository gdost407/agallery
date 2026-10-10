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

test('photo cards load kilobyte thumbnails and cached responses still require authorization', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('app.photos.store'), [
        'files' => [UploadedFile::fake()->image('large.jpg', 2400, 1600)],
    ])->assertSessionHasNoErrors();
    $file = $user->files()->sole();
    $this->get(route('app.photos'))->assertSee(route('app.files.thumbnail', $file->uuid), false)
        ->assertDontSee(route('app.files.content', $file->uuid), false);
    $response = $this->get(route('app.files.thumbnail', $file->uuid))
        ->assertSuccessful()->assertHeader('Content-Type', 'image/jpeg')
        ->assertHeader('Cache-Control', 'no-cache, private');
    $path = $response->baseResponse->getFile()->getPathname();
    $dimensions = getimagesize($path);
    expect($dimensions[0])->toBeLessThanOrEqual(160)->and($dimensions[1])->toBeLessThanOrEqual(160)
        ->and(filesize($path))->toBeLessThanOrEqual(1024)
        ->and(filesize($path))->toBeLessThan($file->size_bytes);
    $bytes = file_get_contents($path);
    $this->get(route('app.files.thumbnail', $file->uuid))->assertSuccessful();
    expect(file_get_contents($path))->toBe($bytes);
    Storage::disk('local')->assertCount('', 2, true);
    expect($user->fresh()->used_storage_bytes)->toBe($file->size_bytes);
    $this->withHeader('If-None-Match', $response->headers->get('ETag'))
        ->get(route('app.files.thumbnail', $file->uuid))->assertStatus(304);
    $this->actingAs(User::factory()->create())->get(route('app.files.thumbnail', $file->uuid))->assertNotFound();
});

test('locked ancestors block cached thumbnails and private contents stay hidden from normal galleries', function (): void {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $child = Folder::factory()->for($user)->for($folder, 'parent')->create();
    $this->actingAs($user)->post(route('app.photos.store'), [
        'folder_id' => $child->id, 'files' => [UploadedFile::fake()->image('secret.jpg')],
    ])->assertSessionHasNoErrors();
    $file = $user->files()->sole();
    $this->get(route('app.files.thumbnail', $file->uuid))->assertSuccessful();
    $folder->update(['password_hash' => 'folder-secret']);
    $this->get(route('app.private'))->assertSee('bi-lock-fill', false)->assertSee('bi-link-45deg', false);
    $this->get(route('app.photos'))->assertDontSee($file->original_name)
        ->assertDontSee(route('app.files.thumbnail', $file->uuid), false)
        ->assertDontSee(route('app.files.content', $file->uuid), false);
    $this->get(route('app.files.thumbnail', $file->uuid))->assertStatus(423);
    $this->post(route('app.files.unlock', $file->uuid), ['password' => 'wrong-password'])->assertSessionHasErrors('password');
    $this->get(route('app.files.thumbnail', $file->uuid))->assertStatus(423);
    $this->post(route('app.files.unlock', $file->uuid), ['password' => 'folder-secret'])->assertRedirect();
    $this->get(route('app.files.thumbnail', $file->uuid))->assertSuccessful();
    $this->get(route('app.folders.show', $child->uuid))->assertSee(route('app.files.thumbnail', $file->uuid), false)
        ->assertDontSee('locked-preview-placeholder', false);
    $this->get(route('app.photos'))->assertDontSee($file->original_name);
    $this->travel(31)->minutes();
    $this->get(route('app.files.thumbnail', $file->uuid))->assertStatus(423);
});

test('file passwords protect thumbnail responses independently of folder passwords', function (): void {
    $user = User::factory()->create();
    $file = File::factory()->for($user)->create(['password_hash' => 'file-secret']);
    Storage::disk('local')->put($file->storage_key, 'private');
    $this->actingAs($user)->get(route('app.files.thumbnail', $file->uuid))->assertStatus(423);
    $this->post(route('app.files.unlock', $file->uuid), ['password' => 'file-secret'])->assertRedirect();
    $this->get(route('app.files.thumbnail', $file->uuid))->assertSuccessful();
    $file->update(['password_hash' => 'changed-secret']);
    $this->get(route('app.files.thumbnail', $file->uuid))->assertStatus(423);
});

test('thumbnail endpoints deny guests other accounts missing files and trash', function (): void {
    $file = File::factory()->create();
    $this->get(route('app.files.thumbnail', $file->uuid))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get(route('app.files.thumbnail', $file->uuid))->assertNotFound();
    $this->actingAs($file->user)->get(route('app.files.thumbnail', $file->uuid))->assertNotFound();
    Storage::disk('local')->put($file->storage_key, 'private');
    $file->delete();
    $this->get(route('app.files.thumbnail', $file->uuid))->assertNotFound();
});

test('non photo previews use lightweight type images without embedding original content', function (string $category, string $extension): void {
    $file = File::factory()->create(['category' => $category, 'extension' => $extension]);
    Storage::disk('local')->put($file->storage_key, 'original-private-content');
    $this->actingAs($file->user)->get(route('app.files.thumbnail', $file->uuid))
        ->assertSuccessful()->assertHeader('Content-Type', 'image/svg+xml')
        ->assertHeader('Cache-Control', 'no-store, private')->assertSee(strtoupper($extension))
        ->assertDontSee('original-private-content');
    $this->get(route('app.dashboard'))->assertSee(route('app.files.thumbnail', $file->uuid), false)
        ->assertDontSee(route('app.files.content', $file->uuid), false);
})->with([['video', 'mp4'], ['document', 'pdf'], ['audio', 'mp3'], ['archive', 'zip']]);

test('received shares require passwords and stop serving thumbnails when revoked', function (): void {
    $recipient = User::factory()->create();
    $file = File::factory()->create(['password_hash' => 'share-secret']);
    $link = FileShareLink::factory()->for($file)->for($recipient, 'recipient')->create();
    Storage::disk('local')->put($file->storage_key, 'private');
    $this->actingAs($recipient)->get(route('app.files.thumbnail', $file->uuid))->assertStatus(423);
    $this->post(route('app.files.unlock', $file->uuid), ['password' => 'share-secret'])->assertRedirect();
    $this->get(route('app.files.thumbnail', $file->uuid))->assertSuccessful();
    $link->update(['revoked_at' => now()]);
    $this->get(route('app.files.thumbnail', $file->uuid))->assertNotFound();
});

test('invalid image files safely fall back to type thumbnails', function (): void {
    $file = File::factory()->create(['category' => 'image', 'extension' => 'jpg', 'mime_type' => 'image/jpeg']);
    Storage::disk('local')->put($file->storage_key, 'invalid-image-data');
    $this->actingAs($file->user)->get(route('app.files.thumbnail', $file->uuid))
        ->assertSuccessful()->assertHeader('Content-Type', 'image/svg+xml');
});

test('detailed photos are compressed to the thumbnail byte budget without changing the original', function (): void {
    $file = File::factory()->create(['category' => 'image', 'extension' => 'png', 'mime_type' => 'image/png']);
    $image = imagecreatetruecolor(640, 480);
    for ($y = 0; $y < 480; $y++) {
        for ($x = 0; $x < 640; $x++) {
            imagesetpixel($image, $x, $y, (($x * 73 + $y * 151) % 256) << 16 | (($x * 31 + $y * 97) % 256) << 8 | (($x * 157 + $y * 13) % 256));
        }
    }
    ob_start();
    imagepng($image);
    $original = ob_get_clean();
    imagedestroy($image);
    Storage::disk('local')->put($file->storage_key, $original);
    $response = $this->actingAs($file->user)->get(route('app.files.thumbnail', $file->uuid))
        ->assertSuccessful()->assertHeader('Content-Type', 'image/jpeg');
    expect(filesize($response->baseResponse->getFile()->getPathname()))->toBeLessThanOrEqual(1024)
        ->and(Storage::disk('local')->get($file->storage_key))->toBe($original);
});
