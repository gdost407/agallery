<x-guest-layout>
    <div class="auth-heading"><span class="auth-eyebrow">AGALLERY</span><h1>Shared file</h1></div>
    @if ($locked)
        <p>Enter the password to access this protected file.</p>
        <form method="POST" action="{{ route('share.unlock', $token) }}">
            @csrf
            <x-input-label for="sharedPassword" value="Password" />
            <x-text-input id="sharedPassword" class="block mt-1 w-full" name="password" type="password" required maxlength="255" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <div class="auth-actions"><x-primary-button class="auth-submit">Unlock</x-primary-button></div>
        </form>
    @else
        <h2>{{ $link->file->original_name }}</h2>
        <p>{{ strtoupper($link->file->extension) }} · {{ \Illuminate\Support\Number::fileSize($link->file->size_bytes) }}</p>
        @if ($link->allow_download)<a href="{{ route('share.download', $token) }}" rel="noreferrer">Download file</a>@else<p>Downloads are disabled for this link.</p>@endif
    @endif
</x-guest-layout>
