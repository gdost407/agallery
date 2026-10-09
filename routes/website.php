<?php

use App\Http\Controllers\PwaController;
use App\Http\Controllers\website\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/{pwaAsset}', PwaController::class)
    ->where('pwaAsset', 'manifest\.webmanifest|service-worker\.js|pwa\.js|pwa\.css|offline\.html|pwa-icon-(?:180|192|512|maskable-512)\.png')
    ->name('pwa.asset');
