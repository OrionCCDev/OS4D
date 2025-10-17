@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">{{ __('Profile') }}</h4>
  </div>

  @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      @if (session('status') === 'profile-updated')
        {{ __('Profile information updated successfully.') }}
      @elseif (session('status') === 'profile-image-updated')
        {{ __('Profile image updated successfully.') }}
      @elseif (session('status') === 'profile-image-removed')
        {{ __('Profile image removed successfully.') }}
      @elseif (session('status') === 'notification-preferences-updated')
        {{ __('Notification preferences updated successfully.') }}
      @else
        {{ session('status') }}
      @endif
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Update Profile Information') }}</h5></div>
        <div class="card-body">
          @include('profile.partials.update-profile-information-form')
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Update Password') }}</h5></div>
        <div class="card-body">
          @include('profile.partials.update-password-form')
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Notification Preferences') }}</h5></div>
        <div class="card-body">
          @include('profile.partials.notification-preferences-form')
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Profile Image') }}</h5></div>
        <div class="card-body">
          @include('profile.partials.profile-image-form')
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Gmail Integration') }}</h5></div>
        <div class="card-body">
          @include('profile.partials.gmail-integration')
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Email Signature Preview') }}</h5>
          <small class="text-muted">{{ __('Preview how your email signature will appear in sent emails') }}</small>
        </div>
        <div class="card-body">
          @include('profile.partials.email-signature-preview')
        </div>
      </div>
    </div>
  </div>
</div>
<!-- / Content -->
@endsection
