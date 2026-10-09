<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FileActionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FilePasswordController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FolderPasswordController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('app')->name('app.')->group(function (): void {
    Route::get('/dashboard', [LibraryController::class, 'index'])->name('dashboard');
    Route::get('/photos', [PhotoController::class, 'index'])->name('photos');
    Route::post('/photos', [PhotoController::class, 'store'])->name('photos.store');
    Route::get('/videos', [VideoController::class, 'index'])->name('videos');
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/starred', [LibraryController::class, 'starred'])->name('starred');
    Route::get('/recent', [LibraryController::class, 'recent'])->name('recent');
    Route::get('/shared', [LibraryController::class, 'shared'])->name('shared');
    Route::get('/trash', [LibraryController::class, 'trash'])->name('trash');
    Route::get('/private-folders', [LibraryController::class, 'privateFolders'])->name('private');
    Route::get('/folders', [LibraryController::class, 'folders'])->name('folders');

    Route::post('/files', [FileController::class, 'store'])->name('files.store');
    Route::delete('/files', [FileActionController::class, 'destroySelected'])->name('files.bulk-destroy');
    Route::get('/files/{file:uuid}', [FileController::class, 'show'])->name('files.show');
    Route::get('/files/{file:uuid}/content', [FileController::class, 'content'])->name('files.content');
    Route::get('/files/{file:uuid}/thumbnail', [FileController::class, 'thumbnail'])->name('files.thumbnail');
    Route::get('/files/{file:uuid}/download', [FileController::class, 'download'])->name('files.download');
    Route::patch('/files/{file:uuid}/star', [FileController::class, 'star'])->name('files.star');
    Route::delete('/files/{file:uuid}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::post('/files/{file:uuid}/copy', [FileActionController::class, 'transfer'])->name('files.copy');
    Route::patch('/files/{file:uuid}/move', [FileActionController::class, 'transfer'])->name('files.move');
    Route::patch('/files/{file:uuid}/restore', [FileController::class, 'restore'])->withTrashed()->name('files.restore');
    Route::put('/files/{file:uuid}/password', [FilePasswordController::class, 'update'])->middleware('throttle:10,1')->name('files.password');
    Route::post('/files/{file:uuid}/unlock', [FilePasswordController::class, 'unlock'])->middleware('throttle:10,1')->name('files.unlock');

    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::get('/folders/{folder:uuid}', [FolderController::class, 'show'])->name('folders.show');
    Route::put('/folders/{folder:uuid}/password', [FolderPasswordController::class, 'update'])->middleware('throttle:10,1')->name('folders.password');
    Route::post('/folders/{folder:uuid}/unlock', [FolderPasswordController::class, 'unlock'])->middleware('throttle:10,1')->name('folders.unlock');
});
