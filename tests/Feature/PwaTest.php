<?php

use App\Models\User;

test('home redirects signed in users straight to their library', function (): void {
    $this->actingAs(User::factory()->create())->get(route('home'))->assertRedirect(route('app.dashboard'));
});

test('public home and login pages expose installation metadata', function (string $routeName): void {
    $this->get(route($routeName))->assertSuccessful()
        ->assertSee('rel="manifest"', false)
        ->assertSee(url('/manifest.webmanifest'))
        ->assertSee('apple-touch-icon')
        ->assertSee('id="pwaInstall"', false)
        ->assertSee(url('/service-worker.js'));
})->with(['home', 'login']);

test('signed in library pages expose installation metadata', function (): void {
    $this->actingAs(User::factory()->create())->get(route('app.dashboard'))
        ->assertSuccessful()->assertSee('rel="manifest"', false)->assertSee('id="pwaInstall"', false);
});

test('manifest icons have their declared dimensions and support app installation', function (): void {
    $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);
    expect($manifest['display'])->toBe('standalone')
        ->and($manifest['scope'])->toBe('./')
        ->and($manifest['start_url'])->toBe('./');
    foreach ($manifest['icons'] as $icon) {
        [$width, $height] = getimagesize(public_path($icon['src']));
        expect($width.'x'.$height)->toBe($icon['sizes']);
    }
    expect(array_column($manifest['icons'], 'purpose'))->toContain('maskable');
});

test('pwa assets are served at browser facing URLs without logging in', function (string $asset, string $contentType): void {
    $this->get(route('pwa.asset', ['pwaAsset' => $asset]))
        ->assertSuccessful()
        ->assertHeader('Content-Type', $contentType)
        ->assertHeader('X-Content-Type-Options', 'nosniff');
})->with([
    ['manifest.webmanifest', 'application/manifest+json'],
    ['service-worker.js', 'application/javascript'],
    ['pwa.js', 'application/javascript'],
    ['pwa.css', 'text/css; charset=utf-8'],
    ['offline.html', 'text/html; charset=utf-8'],
    ['pwa-icon-180.png', 'image/png'],
    ['pwa-icon-192.png', 'image/png'],
    ['pwa-icon-512.png', 'image/png'],
    ['pwa-icon-maskable-512.png', 'image/png'],
]);

test('pwa asset routes cannot expose other project files', function (): void {
    $this->get('/composer.json')->assertNotFound();
    $this->get('/.env')->assertNotFound();
    $this->get('/pwa-icon-999.png')->assertNotFound();
});
