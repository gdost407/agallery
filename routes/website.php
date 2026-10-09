<?php

use App\Http\Controllers\PwaController;
use App\Http\Controllers\SharedFileController;
use App\Http\Controllers\website\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/s/{token}', [SharedFileController::class, 'show'])->where('token', '[A-Za-z0-9]{64}')->name('share.show');
Route::post('/s/{token}/unlock', [SharedFileController::class, 'unlock'])->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:10,1')->name('share.unlock');
Route::get('/s/{token}/download', [SharedFileController::class, 'download'])->where('token', '[A-Za-z0-9]{64}')->name('share.download');

Route::get('/{pwaAsset}', PwaController::class)
    ->where('pwaAsset', 'manifest\.webmanifest|service-worker\.js|pwa\.js|pwa\.css|offline\.html|pwa-icon-(?:180|192|512|maskable-512)\.png')
    ->name('pwa.asset');
