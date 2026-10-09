<?php

namespace App\Http\Controllers;

use App\Http\LibraryListing;
use App\Http\Requests\UploadFilesRequest;
use App\Http\UploadFiles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request, LibraryListing $listing): View
    {
        return $listing->render($request, 'videos');
    }

    public function store(UploadFilesRequest $request, UploadFiles $uploads): RedirectResponse
    {
        return $uploads->handle($request);
    }
}
