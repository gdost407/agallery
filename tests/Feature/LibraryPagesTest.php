<?php

use App\Models\User;

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
]);
