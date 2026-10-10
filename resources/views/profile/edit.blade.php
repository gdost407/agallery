@extends('app.layouts.layouts')

@section('title', 'Settings & profile')

@section('content')
    <div class="page-heading"><div><p class="eyebrow">YOUR ACCOUNT</p><h1>Settings &amp; profile</h1><p class="page-description">Keep your details up to date and your account secure.</p></div><a class="btn btn-light border" href="{{ route('app.dashboard') }}"><i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to library</a></div>
    <div class="profile-sections">
        <section class="settings-card" aria-labelledby="appearanceHeading">
            <h2 id="appearanceHeading">Appearance</h2>
            <p class="text-secondary">Choose your preferred look for AGallery on this device.</p>
            <label for="themePreference" class="form-label">Color mode</label>
            <select id="themePreference" class="form-select" data-theme-preference>
                <option value="system">Use device setting</option>
                <option value="light">Light</option>
                <option value="dark">Dark</option>
            </select>
        </section>
        @include('app.components.media-sync')
        <div class="settings-card">@include('profile.partials.update-profile-information-form')</div>
        <div class="settings-card">@include('profile.partials.update-password-form')</div>
        <div class="settings-card">@include('profile.partials.delete-user-form')</div>
    </div>
@endsection
