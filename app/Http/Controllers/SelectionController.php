<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectedFilesRequest;
use App\Http\SelectedFiles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class SelectionController extends Controller
{
    public function __invoke(SelectedFilesRequest $request, SelectedFiles $selection, FileActionController $actions): RedirectResponse|Response|BinaryFileResponse
    {
        $action = $request->validated('action');
        if ($action === 'delete') {
            return $actions->destroySelected($request);
        }
        if ($action === 'download') {
            return $selection->download($request);
        }
        if ($action === 'share') {
            return response()->view('app.pages.share-links', ['links' => $selection->share($request), 'currentFolder' => null], 200,
                ['Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer']);
        }
        try {
            $selection->transfer($request);
        } catch (ValidationException|HttpException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['files' => 'The selected files could not be transferred. Please try again.']);
        }

        return back()->with('status', $action === 'copy' ? 'Selected files copied.' : 'Selected files moved.');
    }
}
