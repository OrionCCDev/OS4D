@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">{{ __('Profile') }}</h4>
  </div>

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
        <div class="card-header"><h5 class="card-title mb-0">{{ __('Gmail Integration') }}</h5></div>
        <div class="card-body">
          @include('profile.partials.gmail-integration')
        </div>
      </div>
    </div>
  </div>
</div>
<!-- / Content -->
@endsection
