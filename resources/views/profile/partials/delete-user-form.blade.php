<section>
    <div class="mb-2">
        <h5 class="mb-1 text-danger">{{ __('Delete Account') }}</h5>
        <small class="text-muted">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</small>
    </div>

    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">
        {{ __('Delete Account') }}
    </button>

    <div class="modal fade" id="confirm-user-deletion" tabindex="-1" aria-labelledby="confirm-user-deletion-label" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirm-user-deletion-label">{{ __('Are you sure you want to delete your account?') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')
            <div class="modal-body">
              <p class="mb-3">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>
              <div class="mb-3">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif" placeholder="{{ __('Password') }}">
                @if($errors->userDeletion->has('password'))
                  <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                @endif
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
              <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</section>
