@extends('layouts.app')

@section('title', 'Send Email')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <x-modern-breadcrumb
                title="Send Email"
                subtitle="Send a professional email with company branding"
                icon="bx-envelope"
                theme="emails"
                :breadcrumbs="[
                    ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx-home'],
                    ['title' => 'Send Email', 'url' => '#', 'icon' => 'bx-envelope']
                ]"
            />
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-envelope me-2"></i>Compose Email
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bx bx-error-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('emails.send-general') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="to_emails" class="form-label">
                                <i class="bx bx-user me-1"></i>To (Recipients)
                            </label>
                            <input type="text"
                                   class="form-control @error('to_emails') is-invalid @enderror"
                                   id="to_emails"
                                   name="to_emails"
                                   value="{{ old('to_emails') }}"
                                   placeholder="Enter email addresses separated by commas (e.g., client@example.com, partner@company.com)"
                                   required>
                            @error('to_emails')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bx bx-info-circle me-1"></i>
                                Separate multiple email addresses with commas
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">
                                <i class="bx bx-edit me-1"></i>Subject
                            </label>
                            <input type="text"
                                   class="form-control @error('subject') is-invalid @enderror"
                                   id="subject"
                                   name="subject"
                                   value="{{ old('subject') }}"
                                   placeholder="Enter email subject"
                                   required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="body" class="form-label">
                                <i class="bx bx-message me-1"></i>Message
                            </label>
                            <textarea class="form-control @error('body') is-invalid @enderror"
                                      id="body"
                                      name="body"
                                      rows="10"
                                      placeholder="Enter your message here..."
                                      required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-send me-2"></i>Send Email
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">
                                <i class="bx bx-refresh me-2"></i>Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-info-circle me-2"></i>Email Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="bx bx-shield me-1"></i>Automatic CC</h6>
                        <p class="mb-0">All emails are automatically CC'd to <strong>engineering@orion-contracting.com</strong> for record keeping.</p>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="bx bx-palette me-1"></i>Professional Styling</h6>
                        <p class="mb-0">Your email will be sent with professional Orion Contracting branding and styling.</p>
                    </div>

                    <div class="alert alert-success">
                        <h6><i class="bx bx-user me-1"></i>Sender Information</h6>
                        <p class="mb-0">
                            <strong>From:</strong> {{ Auth::user()->name }}<br>
                            <strong>Email:</strong> {{ Auth::user()->email }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-help-circle me-2"></i>Quick Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bx bx-check text-success me-2"></i>
                            Use clear, professional language
                        </li>
                        <li class="mb-2">
                            <i class="bx bx-check text-success me-2"></i>
                            Include relevant project information
                        </li>
                        <li class="mb-2">
                            <i class="bx bx-check text-success me-2"></i>
                            Be specific in your subject line
                        </li>
                        <li class="mb-0">
                            <i class="bx bx-check text-success me-2"></i>
                            Proofread before sending
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearForm() {
    document.getElementById('to_emails').value = '';
    document.getElementById('subject').value = '';
    document.getElementById('body').value = '';
}
</script>
@endsection
