<?php

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('profile photos are uploaded privately displayed in the header and replaced without retaining the old photo', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name, 'email' => $user->email, 'profile_photo' => UploadedFile::fake()->image('avatar.jpg'),
    ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();
    $first = $user->refresh()->profile_photo_path;
    Storage::disk('local')->assertExists($first);
    $this->get(route('profile.photo'))->assertSuccessful()->assertHeader('Cache-Control', 'no-store, private');
    $this->get(route('profile.edit'))->assertSee(route('profile.photo'), false)->assertSee('enctype="multipart/form-data"', false);
    $this->patch(route('profile.update'), [
        'name' => $user->name, 'email' => $user->email, 'profile_photo' => UploadedFile::fake()->image('new.png'),
    ])->assertSessionHasNoErrors();
    Storage::disk('local')->assertMissing($first);
    Storage::disk('local')->assertExists($user->fresh()->profile_photo_path);
    $this->patch(route('profile.update'), ['name' => 'Updated', 'email' => $user->email])->assertSessionHasNoErrors();
    expect($user->fresh()->profile_photo_path)->not->toBeNull();
});

test('invalid profile photos leave the existing account and stored photo unchanged', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->forceFill(['profile_photo_path' => 'profile-photos/existing.jpg'])->save();
    Storage::disk('local')->put($user->profile_photo_path, 'existing');
    foreach ([UploadedFile::fake()->create('note.txt', 1, 'text/plain'), UploadedFile::fake()->image('large.jpg')->size(2049)] as $photo) {
        $this->actingAs($user)->patch(route('profile.update'), ['name' => 'Changed', 'email' => $user->email, 'profile_photo' => $photo])->assertSessionHasErrors('profile_photo');
    }
    expect($user->fresh()->name)->toBe($user->name);
    Storage::disk('local')->assertExists($user->profile_photo_path);
});

test('profile photo access requires login and never serves another accounts photo', function (): void {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->forceFill(['profile_photo_path' => 'profile-photos/owner.jpg'])->save();
    Storage::disk('local')->put($user->profile_photo_path, 'private');
    $this->get(route('profile.photo'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get(route('profile.photo'))->assertNotFound();
});

test('mobile header includes the logo and enabled search works beyond the current gallery page', function (): void {
    $user = User::factory()->create();
    File::factory()->for($user)->create(['original_name' => 'annual-report.pdf']);
    File::factory()->for($user)->create(['original_name' => 'holiday.jpg']);
    File::factory()->create(['original_name' => 'foreign-report.pdf']);
    $this->actingAs($user)->get(route('profile.edit'))->assertSuccessful()
        ->assertSee('AGallery-Logo-Golden.png', false)->assertSee('role="search"', false)
        ->assertSee('action="'.route('app.dashboard').'"', false)->assertSee('aria-label="Search library"', false)
        ->assertDontSee('Search is available in your library');
    $this->get(route('app.dashboard', ['q' => 'report']))->assertSuccessful()->assertSee('annual-report.pdf')
        ->assertDontSee('holiday.jpg')->assertDontSee('foreign-report.pdf');
});

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
