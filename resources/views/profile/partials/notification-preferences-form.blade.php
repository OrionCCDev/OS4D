<section>
    <div class="mb-2">
        <h5 class="mb-1">{{ __('Notification Settings') }}</h5>
        <small class="text-muted">{{ __('Customize your notification preferences.') }}</small>
    </div>

    <form method="post" action="{{ route('profile.notification-preferences.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="notification_sound_enabled"
                       name="notification_sound_enabled" value="1"
                       {{ old('notification_sound_enabled', Auth::user()->notification_sound_enabled ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="notification_sound_enabled">
                    {{ __('Enable notification sounds') }}
                </label>
                <div class="form-text">{{ __('Play a sound when you receive new notifications.') }}</div>
            </div>
            @error('notification_sound_enabled')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ __('Save Preferences') }}</button>
            @if (session('status') === 'notification-preferences-updated')
                <span class="text-muted">{{ __('Preferences saved.') }}</span>
            @endif
        </div>
    </form>
</section>
