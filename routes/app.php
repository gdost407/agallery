<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('app')->group(function () {
    Route::get('/dashboard', function () {
        return view('app/pages/home');
    })->name('app.dashboard');

    Route::get('/photos', function () {
        return view('app/pages/photos');
    })->name('app.photos');

    Route::get('/videos', function () {
        return view('app/pages/videos');
    })->name('app.videos');

    Route::get('/documents', function () {
        return view('app/pages/documents');
    })->name('app.documents');

    Route::get('/starred', function () {
        return view('app/pages/starred');
    })->name('app.starred');

    Route::get('/recent', function () {
        return view('app/pages/recent');
    })->name('app.recent');

    Route::get('/shared', function () {
        return view('app/pages/shared');
    })->name('app.shared');

    Route::get('/trash', function () {
        return view('app/pages/trash');
    })->name('app.trash');
});
