<?php

namespace App\Http\Controllers;

use App\Http\LibraryListing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(Request $request, LibraryListing $listing): View
    {
        return $listing->render($request, 'home');
    }

    public function starred(Request $request, LibraryListing $listing): View
    {
        return $listing->render($request, 'starred');
    }

    public function recent(Request $request, LibraryListing $listing): View
    {
        return $listing->render($request, 'recent');
    }

    public function shared(Request $request, LibraryListing $listing): View
    {
        return $listing->render($request, 'shared');
    }

    public function trash(Request $request, LibraryListing $listing): View
    {
        return $listing->render($request, 'trash');
    }

    public function privateFolders(Request $request, LibraryListing $listing): View
    {
        return $listing->render($request, 'private');
    }
}
