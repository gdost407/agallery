<?php

use App\Models\File;
use App\Models\Folder;
use App\Models\User;

test('homepage shows only the latest ten uploads while search and other pages show their full listings', function (): void {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $files = File::factory()->for($user)->for($folder)->count(15)->sequence(fn ($sequence) => ['original_name' => 'upload-'.$sequence->index.'.jpg', 'created_at' => now()->addSeconds($sequence->index)])->create();
    $this->actingAs($user)->get(route('app.dashboard', ['page' => 2]))->assertSuccessful()
        ->assertViewHas('files', fn ($listing): bool => $listing->count() === 10 && $listing->first()->is($files->last()) && $listing->last()->is($files[5]))
        ->assertSee('View all uploads')->assertDontSee('upload-0.jpg');
    $this->get(route('app.dashboard', ['q' => 'upload-']))->assertViewHas('files', fn ($listing): bool => $listing->total() === 15);
    $this->get(route('app.recent'))->assertViewHas('files', fn ($listing): bool => $listing->total() === 15);
    $this->get(route('app.folders.show', $folder->uuid))->assertViewHas('files', fn ($listing): bool => $listing->count() === 15);
});

test('video cards render a muted inline preview instead of a file icon', function (): void {
    $user = User::factory()->create();
    $file = File::factory()->for($user)->create(['category' => 'video', 'mime_type' => 'video/mp4']);
    $this->actingAs($user)->get(route('app.videos'))->assertSuccessful()
        ->assertSee('class="file-video-preview"', false)->assertSee(route('app.files.content', $file->uuid).'#t=0.1', false)
        ->assertSee('muted playsinline preload="metadata"', false);
});

test('signed in users can render each library page', function (string $routeName): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertSuccessful()
        ->assertSee($user->name)
        ->assertSee(route('logout'));
})->with([
    'dashboard',
    'app.dashboard',
    'app.photos',
    'app.videos',
    'app.documents',
    'app.starred',
    'app.recent',
    'app.shared',
    'app.trash',
    'app.private',
    'app.folders',
]);
