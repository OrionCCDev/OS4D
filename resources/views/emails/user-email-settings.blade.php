@extends('layouts.app')

@section('title', 'Email Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <x-modern-breadcrumb
                title="Email Settings"
                subtitle="Configure your email account for sending emails"
                icon="bx-envelope"
                theme="emails"
                :breadcrumbs="[
                    ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx-home'],
                    ['title' => 'Email Settings', 'url' => '#', 'icon' => 'bx-envelope']
                ]"
            />
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <!-- Current Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Email Status</h5>
                </div>
                <div class="card-body">
                    @if($user->email_credentials_configured)
                        <div class="alert alert-success">
                            <i class="bx bx-check-circle me-2"></i>
                            <strong>Email configured!</strong> You can send emails from your account.
                            <br>
                            <small>Provider: {{ ucfirst($user->email_provider) }} | Last updated: {{ $user->email_credentials_updated_at->format('M d, Y H:i') }}</small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bx bx-exclamation-triangle me-2"></i>
                            <strong>Email not configured.</strong> Please set up your email credentials below to send emails from your account.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Email Provider Selection -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Choose Your Email Provider</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($providers as $key => $provider)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="bx bx-envelope fs-1 text-primary mb-3"></i>
                                    <h6 class="card-title">{{ $provider['name'] }}</h6>
                                    <p class="card-text small text-muted">{{ $provider['instructions'] }}</p>
                                    <button class="btn btn-outline-primary btn-sm" onclick="showProviderForm('{{ $key }}')">
                                        Configure {{ $provider['name'] }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Gmail Configuration -->
            <div class="card mb-4" id="gmail-form" style="display: none;">
                <div class="card-header">
                    <h5 class="card-title mb-0">Configure Gmail</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Gmail Setup Instructions:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Enable 2-Factor Authentication on your Gmail account</li>
                            <li>Go to Google Account Settings > Security > 2-Step Verification > App passwords</li>
                            <li>Generate an App Password for "Mail"</li>
                            <li>Enter your Gmail address and the App Password below</li>
                        </ol>
                    </div>

                    <form action="{{ route('user-email.gmail') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Gmail Address</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                            <div class="form-text">This will be used as the sender address</div>
                        </div>
                        <div class="mb-3">
                            <label for="app_password" class="form-label">App Password</label>
                            <input type="password" class="form-control" id="app_password" name="app_password" required>
                            <div class="form-text">16-character App Password from Google</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-2"></i>Save Gmail Credentials
                        </button>
                    </form>
                </div>
            </div>

            <!-- Outlook Configuration -->
            <div class="card mb-4" id="outlook-form" style="display: none;">
                <div class="card-header">
                    <h5 class="card-title mb-0">Configure Outlook</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user-email.outlook') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Outlook Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="outlook_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="outlook_password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-2"></i>Save Outlook Credentials
                        </button>
                    </form>
                </div>
            </div>

            <!-- Custom SMTP Configuration -->
            <div class="card mb-4" id="custom-form" style="display: none;">
                <div class="card-header">
                    <h5 class="card-title mb-0">Configure Custom SMTP</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user-email.custom') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="host" class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" id="host" name="host" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="port" class="form-label">Port</label>
                                    <input type="number" class="form-control" id="port" name="port" value="587" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ $user->email }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="encryption" class="form-label">Encryption</label>
                            <select class="form-select" id="encryption" name="encryption" required>
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-2"></i>Save Custom Credentials
                        </button>
                    </form>
                </div>
            </div>

            <!-- Test and Send Email -->
            @if($user->email_credentials_configured)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Test & Send Email</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Test Your Email</h6>
                            <p class="text-muted">Send a test email to yourself to verify your settings work.</p>
                            <form action="{{ route('user-email.test') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bx bx-send me-2"></i>Send Test Email
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h6>Send Email</h6>
                            <p class="text-muted">Send an email from your account to recipients.</p>
                            <a href="{{ route('emails.send-form') }}" class="btn btn-primary">
                                <i class="bx bx-envelope me-2"></i>Send Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function showProviderForm(provider) {
    // Hide all forms
    document.getElementById('gmail-form').style.display = 'none';
    document.getElementById('outlook-form').style.display = 'none';
    document.getElementById('custom-form').style.display = 'none';

    // Show selected form
    document.getElementById(provider + '-form').style.display = 'block';
}
</script>
@endsection
