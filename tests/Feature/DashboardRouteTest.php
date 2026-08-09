<?php

it('resolves the app dashboard route name', function () {
    expect(route('app.dashboard', absolute: false))->toBe('/app/dashboard');
});
