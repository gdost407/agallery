@props(['user', 'large' => false])
<span {{ $attributes->class(['avatar', 'profile-avatar' => $large]) }}>
    @if($user->profile_photo_path)
        <img src="{{ route('profile.photo') }}" alt="{{ $user->name }}'s profile photo" width="{{ $large ? 80 : 39 }}" height="{{ $large ? 80 : 39 }}">
    @else
        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
    @endif
</span>
