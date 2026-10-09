@props(['action', 'protected' => false])
<details class="card border-0 p-3 mb-4">
    <summary class="fw-semibold">Password protection</summary>
    <form action="{{ $action }}" method="POST" class="mt-3">
        @csrf @method('PUT')
        @if ($protected)
            <label class="form-label" for="currentResourcePassword">Current password (if locked)</label>
            <input class="form-control mb-3" id="currentResourcePassword" type="password" name="current_password" autocomplete="current-password">
        @endif
        <label class="form-label" for="newResourcePassword">New password</label>
        <input class="form-control mb-3" id="newResourcePassword" type="password" name="password" minlength="8" maxlength="255" autocomplete="new-password">
        <label class="form-label" for="confirmResourcePassword">Confirm new password</label>
        <input class="form-control mb-3" id="confirmResourcePassword" type="password" name="password_confirmation" minlength="8" maxlength="255" autocomplete="new-password">
        <p class="text-secondary small">Use at least 8 characters. Leave both new password fields empty to remove protection.</p>
        <button type="submit" class="btn btn-primary">Save password</button>
    </form>
</details>
