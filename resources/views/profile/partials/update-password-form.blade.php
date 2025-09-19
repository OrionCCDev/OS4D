<section>
    <div class="mb-2">
        <h5 class="mb-1">{{ __('Update Password') }}</h5>
        <small class="text-muted">{{ __('Ensure your account is using a long, random password to stay secure.') }}</small>
    </div>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif">
            @if($errors->updatePassword->has('current_password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif">
            @if($errors->updatePassword->has('password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif">
            @if($errors->updatePassword->has('password_confirmation'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            @if (session('status') === 'password-updated')
                <span class="text-muted">{{ __('Saved.') }}</span>
            @endif
        </div>
    </form>
</section>
