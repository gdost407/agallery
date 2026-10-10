@extends('app.layouts.layouts')

@section('title', 'Settings & profile')

@section('content')
    <div class="page-heading"><div><p class="eyebrow">YOUR ACCOUNT</p><h1>Settings &amp; profile</h1><p class="page-description">Keep your details up to date and your account secure.</p></div><a class="btn btn-light border" href="{{ route('app.dashboard') }}"><i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to library</a></div>
    <div class="profile-sections">
        @include('app.components.media-sync')
        <div class="settings-card">@include('profile.partials.update-profile-information-form')</div>
        <div class="settings-card">@include('profile.partials.update-password-form')</div>
        <div class="settings-card">@include('profile.partials.delete-user-form')</div>
    </div>
@endsection
