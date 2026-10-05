<section aria-labelledby="passwordTitle">
    <h2 id="passwordTitle">Update password</h2>
    <p class="settings-description">Use a strong, unique password to protect your account.</p>
    <form method="POST" action="{{ route('password.update') }}" class="settings-form">
        @csrf
        @method('PUT')
        @foreach (['current_password' => ['Current password', 'current-password'], 'password' => ['New password', 'new-password'], 'password_confirmation' => ['Confirm new password', 'new-password']] as $field => [$label, $autocomplete])
            <div class="mb-3"><label class="form-label" for="update_password_{{ $field }}">{{ $label }}</label><input class="form-control {{ $errors->updatePassword->has($field) ? 'is-invalid' : '' }}" id="update_password_{{ $field }}" name="{{ $field }}" type="password" required autocomplete="{{ $autocomplete }}" @if($errors->updatePassword->has($field)) aria-invalid="true" aria-describedby="password_{{ $field }}Error" @endif>
                @foreach($errors->updatePassword->get($field) as $message)<div class="invalid-feedback" id="password_{{ $field }}Error">{{ $message }}</div>@endforeach
            </div>
        @endforeach
        <div class="d-flex align-items-center gap-3"><button type="submit" class="btn btn-primary">Update password</button>@if(session('status') === 'password-updated')<span class="text-success small" role="status">Password updated</span>@endif</div>
    </form>
</section>
