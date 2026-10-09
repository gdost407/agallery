<?php

use App\Models\File;
use App\Models\FileShareLink;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

test('all selection actions reject foreign and locked files before changing anything', function (string $action): void {
    $user = User::factory()->create();
    $file = File::factory()->for($user)->create();
    $foreign = File::factory()->create();
    $folder = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    $locked = File::factory()->for($user)->for($folder)->create();
    $this->actingAs($user)->postJson(route('app.files.selected'), ['files' => [$file->uuid, $foreign->uuid], 'action' => $action, 'folder_id' => null])->assertUnprocessable();
    $this->postJson(route('app.files.selected'), ['files' => [$file->uuid, $locked->uuid], 'action' => $action, 'folder_id' => null])->assertStatus(423);
    expect($file->fresh()->trashed())->toBeFalse()->and($file->fresh()->folder_id)->toBeNull();
    $this->assertDatabaseCount('file_share_links', 0);
    $this->assertDatabaseCount('files', 3);
})->with(['delete', 'copy', 'move', 'share', 'download']);

test('multiple selected files copy or move into the chosen folder', function (string $action): void {
    $user = User::factory()->create(['used_storage_bytes' => 8]);
    $files = File::factory()->for($user)->count(2)->create(['size_bytes' => 4]);
    $folder = Folder::factory()->for($user)->create();
    foreach ($files as $file) {
        Storage::disk('local')->put($file->storage_key, 'data');
    }
    $this->actingAs($user)->from(route('app.dashboard'))->post(route('app.files.selected'), [
        'files' => $files->pluck('uuid')->all(), 'action' => $action, 'folder_id' => $folder->id,
    ])->assertRedirect(route('app.dashboard'))->assertSessionHasNoErrors();
    expect($folder->files()->count())->toBe(2)
        ->and($user->files()->count())->toBe($action === 'copy' ? 4 : 2)
        ->and($user->fresh()->used_storage_bytes)->toBe($action === 'copy' ? 16 : 8);
})->with(['copy', 'move']);

test('batch copy quota checks the whole selection and disk failures roll back earlier copies', function (): void {
    $user = User::factory()->create(['free_storage_bytes' => 12, 'used_storage_bytes' => 8]);
    $files = File::factory()->for($user)->count(2)->create(['size_bytes' => 4]);
    foreach ($files as $file) {
        Storage::disk('local')->put($file->storage_key, 'data');
    }
    $data = ['files' => $files->pluck('uuid')->all(), 'action' => 'copy', 'folder_id' => null];
    $this->actingAs($user)->postJson(route('app.files.selected'), $data)->assertUnprocessable();
    Storage::disk('local')->assertCount('', 2, true);
    $user->forceFill(['free_storage_bytes' => 100])->save();
    $disk = Storage::disk('local');
    $proxy = Mockery::mock($disk);
    $calls = 0;
    $proxy->shouldReceive('exists')->andReturnTrue();
    $proxy->shouldReceive('copy')->andReturnUsing(function (string $source, string $target) use ($disk, &$calls): bool {
        return ++$calls === 1 ? $disk->copy($source, $target) : false;
    });
    $proxy->shouldReceive('delete')->andReturnUsing(fn ($paths): bool => $disk->delete($paths));
    Storage::set('local', $proxy);
    $this->postJson(route('app.files.selected'), $data)->assertUnprocessable();
    $this->assertDatabaseCount('files', 2);
    $this->assertDatabaseCount('storage_usage_events', 0);
    $disk->assertCount('', 2, true);
    expect($user->fresh()->used_storage_bytes)->toBe(8);
});

test('download packages selected files with duplicate safe filenames and original contents', function (): void {
    $user = User::factory()->create();
    $files = File::factory()->for($user)->count(2)->create(['original_name' => 'photo.jpg']);
    foreach ($files as $index => $file) {
        Storage::disk('local')->put($file->storage_key, 'content-'.$index);
    }
    $response = $this->actingAs($user)->post(route('app.files.selected'), ['files' => $files->pluck('uuid')->all(), 'action' => 'download'])
        ->assertSuccessful()->assertDownload('AGallery-selected-files.zip')->assertHeader('Cache-Control', 'no-store, private');
    $path = $response->baseResponse->getFile()->getPathname();
    $zip = new ZipArchive;
    expect($zip->open($path))->toBeTrue()->and($zip->numFiles)->toBe(2)
        ->and($zip->getFromName('photo.jpg'))->toBe('content-0')->and($zip->getFromName('1-photo.jpg'))->toBe('content-1');
    $zip->close();
    unlink($path);
    $this->post(route('app.files.selected'), ['files' => [$files[0]->uuid], 'action' => 'download'])->assertDownload('photo.jpg');
});

test('batch share generates hashed secure links that work for guests and can be revoked', function (): void {
    $user = User::factory()->create();
    $files = File::factory()->for($user)->count(2)->create();
    foreach ($files as $file) {
        Storage::disk('local')->put($file->storage_key, 'shared data');
    }
    $response = $this->actingAs($user)->post(route('app.files.selected'), ['files' => $files->pluck('uuid')->all(), 'action' => 'share'])
        ->assertSuccessful()->assertSee('Copy links')->assertHeader('Cache-Control', 'no-store, private');
    $links = $response->viewData('links');
    expect($links)->toHaveCount(2);
    $token = basename($links[0]['url']);
    $record = FileShareLink::findOrFail($links[0]['id']);
    expect($record->token_hash)->toBe(hash('sha256', $token))->not->toBe($token);
    Auth::logout();
    $this->get($links[0]['url'])->assertSuccessful()->assertSee($files[0]->original_name);
    $this->get(route('share.download', $token))->assertSuccessful()->assertDownload($files[0]->original_name);
    $this->get('/s/'.str_repeat('x', 64))->assertNotFound();
    $this->actingAs($user)->delete(route('app.shares.revoke', $record->id))->assertRedirect();
    Auth::logout();
    $this->get($links[0]['url'])->assertNotFound();
});

test('public shares preserve folder passwords and changes invalidate guest unlocks', function (): void {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    $file = File::factory()->for($user)->for($folder)->create();
    Storage::disk('local')->put($file->storage_key, 'private shared file');
    $this->actingAs($user)->post(route('app.folders.unlock', $folder->uuid), ['password' => 'folder-secret'])->assertRedirect();
    $response = $this->post(route('app.files.selected'), ['files' => [$file->uuid], 'action' => 'share'])->assertSuccessful();
    $token = basename($response->viewData('links')[0]['url']);
    Auth::logout();
    $this->get(route('share.show', $token))->assertSuccessful()->assertSee('Enter the password')->assertDontSee($file->original_name);
    $this->get(route('share.download', $token))->assertStatus(423);
    $this->post(route('share.unlock', $token), ['password' => 'wrong'])->assertSessionHasErrors('password');
    $this->post(route('share.unlock', $token), ['password' => 'folder-secret'])->assertRedirect();
    $this->get(route('share.download', $token))->assertSuccessful();
    $folder->update(['password_hash' => 'new-secret']);
    $this->get(route('share.download', $token))->assertStatus(423);
});

test('selection toolbar offers all actions and only unlocked destination folders', function (): void {
    $user = User::factory()->create();
    File::factory()->for($user)->create();
    $folder = Folder::factory()->for($user)->create(['name' => 'Available destination']);
    $locked = Folder::factory()->for($user)->create(['name' => 'Secret destination', 'password_hash' => 'folder-secret']);
    $this->actingAs($user)->get(route('app.photos'))->assertSee('data-selection-transfer="copy"', false)->assertSee('data-selection-transfer="move"', false)
        ->assertSee('value="share"', false)->assertSee('value="download"', false)->assertSee($folder->name)->assertDontSee($locked->name);
});

test('public links enforce expiry download permission link passwords and deleted files', function (): void {
    $file = File::factory()->create();
    Storage::disk('local')->put($file->storage_key, 'shared');
    $token = str_repeat('a', 64);
    $link = FileShareLink::factory()->for($file)->create(['user_id' => $file->user_id,
        'recipient_user_id' => null, 'token_hash' => hash('sha256', $token), 'allow_download' => false]);
    $this->get(route('share.show', $token))->assertSuccessful();
    $this->get(route('share.download', $token))->assertNotFound();
    $link->update(['expires_at' => now()->subMinute()]);
    $this->get(route('share.show', $token))->assertNotFound();
    $link->update(['expires_at' => null, 'allow_download' => true, 'password_hash' => 'link-secret']);
    $this->get(route('share.download', $token))->assertStatus(423);
    $this->post(route('share.unlock', $token), ['password' => 'link-secret'])->assertRedirect();
    $this->get(route('share.download', $token))->assertSuccessful();
    $file->delete();
    $this->get(route('share.show', $token))->assertNotFound();
});

test('recipient limited links and revocation cannot be accessed by another account', function (): void {
    $file = File::factory()->create();
    $recipient = User::factory()->create();
    $token = str_repeat('b', 64);
    $link = FileShareLink::factory()->for($file)->create(['user_id' => $file->user_id,
        'recipient_user_id' => $recipient->id, 'token_hash' => hash('sha256', $token)]);
    $this->get(route('share.show', $token))->assertNotFound();
    $this->actingAs($recipient)->get(route('share.show', $token))->assertSuccessful();
    $this->delete(route('app.shares.revoke', $link->id))->assertNotFound();
    expect($link->fresh()->revoked_at)->toBeNull();
});
