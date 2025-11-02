@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bx bx-error-circle" style="font-size: 5rem; color: #dc3545;"></i>
                    </div>
                    <h2 class="card-title text-danger mb-3">Dashboard Error</h2>
                    <p class="card-text text-muted mb-4">
                        {{ $message ?? 'We encountered an error while loading the dashboard.' }}
                    </p>

                    @if(isset($error) && $error)
                    <div class="alert alert-warning text-start">
                        <h6 class="alert-heading">Error Details:</h6>
                        <p class="mb-0 small">{{ $error }}</p>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="bx bx-refresh me-2"></i>Try Again
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary ms-2">
                            <i class="bx bx-home me-2"></i>Go Home
                        </a>
                    </div>

                    <div class="mt-4 text-muted small">
                        <p>If this problem persists, please contact your system administrator.</p>
                        <p>Error logged at: {{ now()->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
