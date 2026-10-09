<?php

namespace App\Http\Controllers;

use App\Http\LibraryAccess;
use App\Models\FileShareLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SharedFileController extends Controller
{
    public function __construct(private LibraryAccess $access) {}

    private function link(Request $request, string $token): FileShareLink
    {
        $link = FileShareLink::with('file')->where('token_hash', hash('sha256', $token))->whereNull('revoked_at')
            ->where(fn ($expiry) => $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->whereHas('file', fn ($file) => $file->where('status', 'ready'))->firstOrFail();
        abort_unless($link->user_id === $link->file->user_id, 404);
        abort_if($link->recipient_user_id !== null && $link->recipient_user_id !== $request->user()?->id, 404);

        return $link;
    }

    private function linkLocked(FileShareLink $link, Request $request): bool
    {
        $grant = $request->session()->get('share_unlocks.'.$link->id);

        return $link->password_hash !== null && (! is_array($grant) || ($grant['expires'] ?? 0) <= now()->timestamp
            || ! hash_equals(hash('sha256', $link->password_hash), $grant['fingerprint'] ?? ''));
    }

    public function show(Request $request, string $token): Response
    {
        $link = $this->link($request, $token);
        $locked = $this->linkLocked($link, $request) || $this->access->firstLocked($link->file, $request) !== null;

        return response()->view('share-file', ['link' => $link, 'locked' => $locked, 'token' => $token], 200,
            ['Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow']);
    }

    public function unlock(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate(['password' => ['required', 'string', 'max:255']]);
        $link = $this->link($request, $token);
        if ($this->linkLocked($link, $request)) {
            if (! Hash::check($data['password'], $link->password_hash)) {
                throw ValidationException::withMessages(['password' => 'The password is incorrect.']);
            }
            $request->session()->put('share_unlocks.'.$link->id, ['fingerprint' => hash('sha256', $link->password_hash), 'expires' => now()->addMinutes(30)->timestamp]);
        } else {
            $locked = $this->access->firstLocked($link->file, $request);
            if ($locked !== null) {
                $this->access->unlock($locked, $request, $data['password']);
            }
        }

        return to_route('share.show', $token);
    }

    public function download(Request $request, string $token): BinaryFileResponse
    {
        $link = $this->link($request, $token);
        abort_unless($link->allow_download, 404);
        abort_if($this->linkLocked($link, $request), 423);
        $file = $link->file;
        $this->access->ensureUnlocked($file, $request);
        abort_unless($file->disk === 'local' && Storage::disk('local')->exists($file->storage_key), 404);
        $link->increment('access_count', 1, ['last_accessed_at' => now()]);

        return response()->download(Storage::disk('local')->path($file->storage_key), $file->original_name,
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff', 'Referrer-Policy' => 'no-referrer'])->setPrivate();
    }

    public function revoke(Request $request, FileShareLink $link): RedirectResponse
    {
        abort_unless($link->user_id === $request->user()->id, 404);
        $link->update(['revoked_at' => now()]);

        return to_route('app.dashboard')->with('status', 'Share link revoked.');
    }
}
