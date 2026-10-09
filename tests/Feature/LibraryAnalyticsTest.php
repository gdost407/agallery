<?php

use App\Models\File;
use App\Models\Folder;
use App\Models\User;

test('dashboard analytics reconcile type extension folder and available storage including private files and trash', function (): void {
    $user = User::factory()->create(['free_storage_bytes' => 1000, 'reserved_storage_bytes' => 10]);
    $folder = Folder::factory()->for($user)->create(['name' => 'Public folder']);
    $private = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret', 'name' => 'Hidden folder name']);
    File::factory()->for($user)->for($folder)->create(['category' => 'image', 'size_bytes' => 10, 'original_name' => 'public photo.jpg']);
    File::factory()->for($user)->for($private)->create(['category' => 'image', 'size_bytes' => 40, 'original_name' => 'hidden photo.jpg']);
    File::factory()->for($user)->for($folder)->create(['category' => 'video', 'size_bytes' => 20, 'deleted_at' => now()]);
    File::factory()->for($user)->for($folder)->create(['category' => 'document', 'extension' => 'PDF', 'size_bytes' => 30]);
    File::factory()->for($user)->create(['category' => 'document', 'extension' => 'docx', 'size_bytes' => 60]);
    File::factory()->for($user)->create(['category' => 'audio', 'size_bytes' => 5]);
    File::factory()->create(['size_bytes' => 999999]);
    $response = $this->actingAs($user)->get(route('app.dashboard'))->assertSuccessful()
        ->assertSee('Storage overview')->assertSee('Documents by type')->assertSee('Storage by folder')
        ->assertSee('Empty / available')->assertSee('Recent uploads')->assertDontSee($private->name)->assertDontSee('hidden photo.jpg');
    $data = $response->viewData('analytics');
    expect($data['summary']['used'])->toBe(165)->and($data['summary']['remaining'])->toBe(825)
        ->and($data['types']['image']['bytes'])->toBe(50)->and($data['types']['video']['bytes'])->toBe(20)
        ->and($data['types']['document']['bytes'])->toBe(90)->and($data['types']['other']['bytes'])->toBe(5)
        ->and($data['documents']->keyBy('extension')['PDF']['bytes'])->toBe(30)
        ->and($data['documents']->keyBy('extension')['DOCX']['bytes'])->toBe(60)
        ->and((int) $data['folders']->sum('bytes'))->toBe(165)
        ->and($data['folderUsage'][$folder->id]['bytes'])->toBe(60);
});

test('folder menu shows correct recursive sizes and file counts without leaking protected children', function (): void {
    $user = User::factory()->create();
    $root = Folder::factory()->for($user)->create(['name' => 'Root']);
    $child = Folder::factory()->for($user)->for($root, 'parent')->create(['name' => 'Child']);
    $grandchild = Folder::factory()->for($user)->for($child, 'parent')->create(['name' => 'Grandchild']);
    $private = Folder::factory()->for($user)->for($child, 'parent')->create(['name' => 'Secret child', 'password_hash' => 'folder-secret']);
    foreach ([[$root, 10], [$child, 20], [$grandchild, 30], [$private, 7]] as [$folder, $bytes]) {
        File::factory()->for($user)->for($folder)->create(['size_bytes' => $bytes]);
    }
    File::factory()->for($user)->for($grandchild)->create(['size_bytes' => 5, 'deleted_at' => now()]);
    $this->actingAs($user)->get(route('app.folders'))->assertSuccessful()->assertSee('Root')->assertSee('72 B')
        ->assertViewHas('folders', fn ($folders): bool => $folders->pluck('id')->all() === [$root->id]);
    $response = $this->get(route('app.folders.show', $child->uuid))->assertSuccessful()->assertSee('Grandchild')->assertSee('62 B')->assertSee('35 B')->assertDontSee('Secret child');
    expect($response->viewData('currentFolderUsage')['bytes'])->toBe(62)
        ->and($response->viewData('currentFolderUsage')['count'])->toBe(4)
        ->and($response->viewData('folderUsage')[$grandchild->id]['bytes'])->toBe(35);
});

test('dedicated media pages include their type from every normal folder and never other media types or filter chips', function (): void {
    $user = User::factory()->create();
    $root = Folder::factory()->for($user)->create();
    $nested = Folder::factory()->for($user)->for($root, 'parent')->create();
    $private = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    foreach (['image' => 'image.jpg', 'video' => 'video.mp4', 'document' => 'document.pdf'] as $category => $name) {
        File::factory()->for($user)->for($root)->create(['category' => $category, 'original_name' => 'root-'.$name]);
        File::factory()->for($user)->for($nested)->create(['category' => $category, 'original_name' => 'nested-'.$name]);
        File::factory()->for($user)->for($private)->create(['category' => $category, 'original_name' => 'private-'.$name]);
    }
    foreach (['app.photos' => 'image.jpg', 'app.videos' => 'video.mp4', 'app.documents' => 'document.pdf'] as $route => $name) {
        $response = $this->actingAs($user)->get(route($route))->assertSuccessful()->assertSee('root-'.$name)->assertSee('nested-'.$name)
            ->assertDontSee('private-'.$name)->assertDontSee('data-filter=', false)->assertDontSee('filter-chips', false)
            ->assertViewHas('files', fn ($files): bool => $files->total() === 2);
        foreach (array_diff(['image.jpg', 'video.mp4', 'document.pdf'], [$name]) as $other) {
            $response->assertDontSee('root-'.$other)->assertDontSee('nested-'.$other);
        }
    }
    foreach (['app.dashboard', 'app.folders', 'app.starred', 'app.recent', 'app.shared', 'app.trash', 'app.private'] as $route) {
        $this->get(route($route))->assertSuccessful()->assertDontSee('data-filter=', false)->assertDontSee('filter-chips', false);
    }
});

test('dashboard recent uploads include nested folders in newest order and omit private uploads', function (): void {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $private = Folder::factory()->for($user)->create(['password_hash' => 'folder-secret']);
    $old = File::factory()->for($user)->create(['created_at' => now()->subDay()]);
    $recent = File::factory()->for($user)->for($folder)->create();
    $hidden = File::factory()->for($user)->for($private)->create();
    $this->actingAs($user)->get(route('app.dashboard'))->assertSuccessful()->assertSee($recent->original_name)->assertDontSee($hidden->original_name)
        ->assertViewHas('files', fn ($files): bool => $files->pluck('id')->all() === [$recent->id, $old->id]);
});

test('empty dashboards and zero capacity render valid analytics without division errors', function (): void {
    $user = User::factory()->create(['free_storage_bytes' => 0]);
    $response = $this->actingAs($user)->get(route('app.dashboard'))->assertSuccessful()->assertSee('No documents uploaded yet.');
    $analytics = $response->viewData('analytics');
    expect($analytics['summary']['used'])->toBe(0)->and($analytics['summary']['remaining'])->toBe(0)
        ->and($analytics['chart'])->not->toContain('NAN')->not->toContain('INF');
});
