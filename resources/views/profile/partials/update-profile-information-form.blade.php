<section>
    <div class="mb-2">
        <h5 class="mb-1">{{ __('Profile Information') }}</h5>
        <small class="text-muted">{{ __("Update your account's profile information and email address.") }}</small>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning mt-3" role="alert">
                    <div class="d-flex flex-column gap-2">
                        <span>{{ __('Your email address is unverified.') }}</span>
                        <button form="send-verification" class="btn btn-sm btn-outline-primary align-self-start">{{ __('Click here to re-send the verification email.') }}</button>
                        @if (session('status') === 'verification-link-sent')
                            <span class="text-success">{{ __('A new verification link has been sent to your email address.') }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            @if (session('status') === 'profile-updated')
                <span class="text-muted">{{ __('Saved.') }}</span>
            @endif
        </div>
    </form>
</section>
