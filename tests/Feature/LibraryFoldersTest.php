<?php

use App\Http\LibraryFolders;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

test('default folders exist once and mixed uploads are filed using generated names', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('app.folders'))->assertSuccessful();
    $this->get(route('app.folders'))->assertSuccessful();
    expect($user->folders()->pluck('name', 'system_key')->all())->toEqual(LibraryFolders::DEFAULTS);
    $this->post(route('app.files.store'), ['files' => [
        UploadedFile::fake()->image('my photo.jpg'),
        UploadedFile::fake()->create('movie.mp4', 1, 'video/mp4'),
        UploadedFile::fake()->create('report.pdf', 1, 'application/pdf'),
        UploadedFile::fake()->create('archive.zip', 1, 'application/zip'),
    ]])->assertRedirect()->assertSessionHasNoErrors();
    foreach ($user->files()->get() as $file) {
        expect($file->folder->system_key)->toBe(in_array($file->category, ['image', 'video'], true) ? $file->category : 'document')
            ->and(basename($file->storage_key))->toBe($file->stored_name)
            ->and($file->stored_name)->toMatch('/^(IMG|VID|DOC|FILE)'.$user->id.'\d{17}_[a-zA-Z0-9]{12}\.[a-z0-9]+$/');
        Storage::disk('local')->assertExists($file->storage_key);
    }
    $this->get(route('app.photos'))->assertSee('my photo.jpg')->assertDontSee($user->files()->first()->stored_name);
});

test('custom folders and duplicate original names retain distinct backend names when copied', function (): void {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $this->actingAs($user)->post(route('app.files.store'), ['folder_id' => $folder->id, 'files' => [
        UploadedFile::fake()->image('same.jpg'), UploadedFile::fake()->image('same.jpg'),
    ]])->assertSessionHasNoErrors();
    $source = $user->files()->first();
    $this->post(route('app.files.copy', $source->uuid), ['folder_id' => null])->assertSessionHasNoErrors();
    expect($user->files()->pluck('stored_name')->unique())->toHaveCount(3)
        ->and($source->folder_id)->toBe($folder->id)
        ->and($user->files()->latest('id')->first()->folder->system_key)->toBe('image')
        ->and($user->files()->pluck('original_name')->unique()->all())->toBe(['same.jpg']);
});

test('trash folder opens deleted items and rejects uploads transfers children and passwords', function (): void {
    $user = User::factory()->create();
    app(LibraryFolders::class)->ensure($user);
    $trash = $user->folders()->where('system_key', 'trash')->sole();
    $file = File::factory()->for($user)->create();
    Storage::disk('local')->put($file->storage_key, 'data');
    $this->actingAs($user)->get(route('app.folders.show', $trash->uuid))->assertRedirect(route('app.trash'));
    $this->postJson(route('app.files.store'), ['folder_id' => $trash->id, 'files' => [UploadedFile::fake()->image('test.jpg')]])->assertUnprocessable();
    $this->patchJson(route('app.files.move', $file->uuid), ['folder_id' => $trash->id])->assertUnprocessable();
    $this->postJson(route('app.folders.store'), ['parent_id' => $trash->id, 'name' => 'Child'])->assertUnprocessable();
    $this->putJson(route('app.folders.password', $trash->uuid), ['password' => 'secret-password', 'password_confirmation' => 'secret-password'])->assertUnprocessable();
});

test('migration backfills existing unfiled items without changing originals or private storage paths', function (): void {
    $user = User::factory()->create();
    $file = File::factory()->for($user)->create(['category' => 'image', 'original_name' => 'original.jpg']);
    $key = $file->storage_key;
    $migration = require database_path('migrations/2026_10_09_232757_backfill_library_default_folders.php');
    $migration->up();
    $migration->up();
    expect($file->fresh()->folder->system_key)->toBe('image')
        ->and($file->fresh()->original_name)->toBe('original.jpg')
        ->and($file->fresh()->storage_key)->toBe($key)
        ->and($user->folders()->count())->toBe(4);
});
