@extends('layouts.app')

@section('title', 'Send Free Mail - ' . $task->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-envelope-open-text text-primary"></i>
                        Send Free Mail
                        <span class="text-muted">- Task: {{ $task->title }}</span>
                    </h4>
                    <p class="text-muted mb-0">Send any email related to this task via Gmail</p>
                </div>

                <div class="card-body">
                    <!-- Progress Steps -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="progress-container">
                                <div class="progress-step active">
                                    <div class="progress-circle">1</div>
                                    <span>Fill Details</span>
                                </div>
                                <div class="progress-line"></div>
                                <div class="progress-step">
                                    <div class="progress-circle">2</div>
                                    <span>Send via Gmail</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Free Mail Form -->
                    <form id="freeMailForm" method="POST" action="{{ route('tasks.send-free-mail', $task) }}">
                        @csrf

                        <div class="row">
                            <!-- TO Recipients -->
                            <div class="col-md-6 mb-3">
                                <label for="to_emails" class="form-label">
                                    <i class="fas fa-user text-primary"></i>
                                    TO RECIPIENTS <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('to_emails') is-invalid @enderror"
                                       id="to_emails"
                                       name="to_emails"
                                       placeholder="recipient@example.com, another@example.com"
                                       value="{{ old('to_emails') }}"
                                       required>
                                @error('to_emails')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- CC Recipients -->
                            <div class="col-md-6 mb-3">
                                <label for="cc_emails" class="form-label">
                                    <i class="fas fa-copy text-info"></i>
                                    CC RECIPIENTS
                                </label>
                                <input type="text"
                                       class="form-control @error('cc_emails') is-invalid @enderror"
                                       id="cc_emails"
                                       name="cc_emails"
                                       placeholder="cc@example.com"
                                       value="{{ old('cc_emails') }}">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    engineering@orion-contracting.com will be automatically added to CC
                                </small>
                                @error('cc_emails')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- BCC Recipients -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bcc_emails" class="form-label">
                                    <i class="fas fa-eye-slash text-secondary"></i>
                                    BCC RECIPIENTS
                                </label>
                                <input type="text"
                                       class="form-control @error('bcc_emails') is-invalid @enderror"
                                       id="bcc_emails"
                                       name="bcc_emails"
                                       placeholder="bcc@example.com"
                                       value="{{ old('bcc_emails') }}">
                                @error('bcc_emails')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Subject -->
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-tag text-warning"></i>
                                    EMAIL SUBJECT <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('subject') is-invalid @enderror"
                                       id="subject"
                                       name="subject"
                                       placeholder="Enter email subject"
                                       value="{{ old('subject') }}"
                                       required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email Body -->
                        <div class="mb-3">
                            <label for="body" class="form-label">
                                <i class="fas fa-align-left text-success"></i>
                                EMAIL BODY <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('body') is-invalid @enderror"
                                      id="body"
                                      name="body"
                                      rows="10"
                                      placeholder="Enter your email message here..."
                                      required>{{ old('body') }}</textarea>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                HTML styling is supported! You can use basic HTML tags for formatting.
                            </small>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Back to Task
                            </a>

                            <div class="btn-group">
                                <button type="button" class="btn btn-warning" id="previewBtn" disabled>
                                    <i class="fas fa-eye"></i>
                                    Preview Email
                                </button>
                                <button type="submit" class="btn btn-primary" id="sendViaGmailBtn" disabled>
                                    <i class="fab fa-google"></i>
                                    Send via Gmail (Recommended)
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Email Preview Modal -->
            <div class="modal fade" id="emailPreviewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-eye text-primary"></i>
                                Email Preview
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="email-preview">
                                <div class="preview-header mb-3">
                                    <strong>To:</strong> <span id="previewTo"></span><br>
                                    <strong>CC:</strong> <span id="previewCc"></span><br>
                                    <strong>Subject:</strong> <span id="previewSubject"></span>
                                </div>
                                <div class="preview-body border p-3" id="previewBody"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 20px 0;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    color: #6c757d;
}

.progress-step.active {
    color: #007bff;
}

.progress-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
}

.progress-step.active .progress-circle {
    background: #007bff;
    color: white;
}

.progress-line {
    width: 100px;
    height: 2px;
    background: #e9ecef;
    margin: 0 20px;
}

.progress-line.active {
    background: #007bff;
}

.email-preview {
    font-family: Arial, sans-serif;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - initializing free mail functionality');

    const freeMailForm = document.getElementById('freeMailForm');
    const toEmails = document.getElementById('to_emails');
    const ccEmails = document.getElementById('cc_emails');
    const bccEmails = document.getElementById('bcc_emails');
    const subject = document.getElementById('subject');
    const body = document.getElementById('body');
    const previewBtn = document.getElementById('previewBtn');
    const sendViaGmailBtn = document.getElementById('sendViaGmailBtn');
    const emailPreviewModal = new bootstrap.Modal(document.getElementById('emailPreviewModal'));
    const previewTo = document.getElementById('previewTo');
    const previewCc = document.getElementById('previewCc');
    const previewSubject = document.getElementById('previewSubject');
    const previewBody = document.getElementById('previewBody');

    console.log('DOM Elements found:', {
        freeMailForm: !!freeMailForm,
        toEmails: !!toEmails,
        ccEmails: !!ccEmails,
        bccEmails: !!bccEmails,
        subject: !!subject,
        body: !!body,
        previewBtn: !!previewBtn,
        sendViaGmailBtn: !!sendViaGmailBtn
    });

    // Update progress function
    function updateProgress() {
        const hasRequiredData = toEmails && toEmails.value.trim() !== '' &&
                               subject && subject.value.trim() !== '' &&
                               body && body.value.trim() !== '';

        if (previewBtn) previewBtn.disabled = !hasRequiredData;
        if (sendViaGmailBtn) sendViaGmailBtn.disabled = !hasRequiredData;

        const progressSteps = document.querySelectorAll('.progress-step');
        if (hasRequiredData) {
            progressSteps[1].classList.add('active');
            document.querySelectorAll('.progress-line')[0].classList.add('active');
        } else {
            progressSteps[1].classList.remove('active');
            document.querySelectorAll('.progress-line')[0].classList.remove('active');
        }
    }

    // Form field event listeners
    [toEmails, ccEmails, bccEmails, subject, body].forEach(field => {
        if (field) {
            field.addEventListener('input', updateProgress);
        }
    });

    // Preview button functionality
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            const ccWithEngineering = ccEmails.value + (ccEmails.value ? ',' : '') + 'engineering@orion-contracting.com';

            previewTo.textContent = toEmails.value;
            previewCc.textContent = ccWithEngineering;
            previewSubject.textContent = subject.value;
            previewBody.innerHTML = body.value;

            emailPreviewModal.show();
        });
    }

    // Form submission for Gmail
    if (freeMailForm) {
        freeMailForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (confirm('This will open Gmail in a new tab with your email pre-filled.\n\n⚠️ Note: You will need to manually attach any required files.\n\nClick OK to continue.')) {
                sendViaGmailBtn.disabled = true;
                sendViaGmailBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening Gmail...';

                const formData = new FormData(freeMailForm);

                fetch('{{ route("tasks.send-free-mail", $task) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Free mail prepared successfully:', data);
                        const gmailWindow = window.open(data.gmail_url, '_blank');

                        if (gmailWindow) {
                            setTimeout(() => {
                                alert('✅ Gmail opened!\n\n📌 Next Steps:\n1. Attach any required files in Gmail\n2. Review and send the email\n3. The managers have been notified about this email');
                            }, 500);

                            // Redirect to task after a delay
                            setTimeout(() => {
                                window.location.href = '{{ route("tasks.show", $task) }}';
                            }, 3000);
                        } else {
                            alert('❌ Gmail window was blocked by popup blocker!\n\nPlease allow popups for this site and try again.');
                            sendViaGmailBtn.disabled = false;
                            sendViaGmailBtn.innerHTML = '<i class="fab fa-google"></i> Send via Gmail (Recommended)';
                        }
                    } else {
                        alert('❌ Error: ' + data.message);
                        sendViaGmailBtn.disabled = false;
                        sendViaGmailBtn.innerHTML = '<i class="fab fa-google"></i> Send via Gmail (Recommended)';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error preparing free mail. Please try again.');
                    sendViaGmailBtn.disabled = false;
                    sendViaGmailBtn.innerHTML = '<i class="fab fa-google"></i> Send via Gmail (Recommended)';
                });
            }
        });
    }

    // Initial progress update
    updateProgress();
});
</script>
@endsection
