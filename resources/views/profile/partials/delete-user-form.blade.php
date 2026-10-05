<section aria-labelledby="deleteAccountTitle">
    <h2 id="deleteAccountTitle">Delete account</h2>
    <p class="settings-description">Deleting your account permanently removes its data. Download anything you want to keep first.</p>
    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">Delete account</button>
    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionTitle" aria-hidden="true" @if($errors->userDeletion->isNotEmpty()) data-open-on-load @endif>
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content preview-modal">
            <div class="modal-header"><h2 class="modal-title fs-5" id="confirmUserDeletionTitle">Delete your account?</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ route('profile.destroy') }}">@csrf @method('DELETE')
                <div class="modal-body"><p class="small text-secondary">This permanently deletes your account and its data. Enter your password to confirm.</p><label class="form-label" for="deletion_password">Password</label><input type="password" id="deletion_password" name="password" class="form-control {{ $errors->userDeletion->has('password') ? 'is-invalid' : '' }}" autocomplete="current-password" required @if($errors->userDeletion->has('password')) aria-invalid="true" aria-describedby="deletionPasswordError" @endif>
                    @foreach($errors->userDeletion->get('password') as $message)<div class="invalid-feedback" id="deletionPasswordError">{{ $message }}</div>@endforeach
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Delete account</button></div>
            </form>
        </div></div>
    </div>
</section>
