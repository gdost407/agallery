<section aria-labelledby="profileDetailsTitle">
    <h2 id="profileDetailsTitle">Profile information</h2>
    <p class="settings-description">Your name and email address for your personal space.</p>
    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">@csrf</form>
    <form method="POST" action="{{ route('profile.update') }}" class="settings-form" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="d-flex align-items-center gap-3 mb-4">
            <x-user-avatar :user="$user" :large="true" />
            <div class="flex-grow-1">
                <label class="form-label" for="profilePhoto">Profile photo</label>
                <input type="file" id="profilePhoto" name="profile_photo" class="form-control {{ $errors->has('profile_photo') ? 'is-invalid' : '' }}" accept="image/jpeg,image/png,image/webp" aria-describedby="profilePhotoHelp">
                <p id="profilePhotoHelp" class="small text-secondary mt-2 mb-0">JPG, PNG or WebP, up to 2 MB.</p>
                @error('profile_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        @foreach (['name' => ['Name', 'text', 'name'], 'email' => ['Email address', 'email', 'username']] as $field => [$label, $type, $autocomplete])
            <div class="mb-3"><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-control {{ $errors->has($field) ? 'is-invalid' : '' }}" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field, $user->{$field}) }}" required autocomplete="{{ $autocomplete }}" @if($errors->has($field)) aria-invalid="true" aria-describedby="{{ $field }}Error" @endif>
                @error($field)<div class="invalid-feedback" id="{{ $field }}Error">{{ $message }}</div>@enderror
            </div>
        @endforeach
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="alert alert-warning small">Your email address is unverified. <button type="submit" form="send-verification" class="btn btn-link p-0 small">Resend verification email</button></div>
        @endif
        @if (session('status') === 'verification-link-sent')<p class="text-success small" role="status">A new verification link has been sent.</p>@endif
        <div class="d-flex align-items-center gap-3"><button type="submit" class="btn btn-primary">Save changes</button>@if(session('status') === 'profile-updated')<span class="text-success small" role="status"><i class="bi bi-check-circle me-1" aria-hidden="true"></i>Changes saved</span>@endif</div>
    </form>
</section>
