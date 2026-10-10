<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $newPhoto = null;
        $oldPhoto = null;
        try {
            DB::transaction(function () use ($request, &$newPhoto, &$oldPhoto): void {
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $user->fill($request->safe()->only(['name', 'email']));
                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }
                if ($request->hasFile('profile_photo')) {
                    $newPhoto = $request->file('profile_photo')->store('profile-photos/'.$user->id, 'local');
                    if (! $newPhoto) {
                        throw ValidationException::withMessages(['profile_photo' => 'Your photo could not be saved. Please try again.']);
                    }
                    $oldPhoto = $user->profile_photo_path;
                    $user->profile_photo_path = $newPhoto;
                }
                $user->save();
            });
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('local')->delete($newPhoto);
            }
            throw $exception;
        }
        if ($oldPhoto) {
            Storage::disk('local')->delete($oldPhoto);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function photo(Request $request): BinaryFileResponse
    {
        $path = $request->user()->profile_photo_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ])->setPrivate();
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();
        if ($user->profile_photo_path) {
            Storage::disk('local')->delete($user->profile_photo_path);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
