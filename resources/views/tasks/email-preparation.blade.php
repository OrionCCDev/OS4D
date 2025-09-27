@extends('layouts.app')

@section('title', 'Prepare Email - ' . $task->title)

@section('content')
<style>
    .email-prep-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .email-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
    }

    .email-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .email-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    .email-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .form-section {
        padding: 2rem;
        background: white;
    }

    .form-group-enhanced {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-label-enhanced {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-control-enhanced {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.875rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .form-control-enhanced:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
        transform: translateY(-2px);
    }

    .email-input-group {
        position: relative;
    }

    .email-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .email-suggestion {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s;
    }

    .email-suggestion:hover {
        background: #f8fafc;
    }

    .email-suggestion:last-child {
        border-bottom: none;
    }

    .attachment-area {
        border: 2px dashed #cbd5e0;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .attachment-area:hover {
        border-color: #667eea;
        background: #f0f4ff;
    }

    .attachment-area.dragover {
        border-color: #667eea;
        background: #e6f3ff;
        transform: scale(1.02);
    }

    .attachment-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .attachment-item {
        background: #667eea;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .attachment-remove {
        cursor: pointer;
        font-weight: bold;
        opacity: 0.8;
    }

    .attachment-remove:hover {
        opacity: 1;
    }

    .btn-enhanced {
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .btn-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-enhanced:hover::before {
        left: 100%;
    }

    .btn-primary-enhanced {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary-enhanced:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .btn-success-enhanced {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .btn-success-enhanced:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(72, 187, 120, 0.3);
    }

    .btn-secondary-enhanced {
        background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
        color: white;
    }

    .btn-secondary-enhanced:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(113, 128, 150, 0.3);
    }

    .task-info-card {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .task-info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .task-info-item:last-child {
        border-bottom: none;
    }

    .task-info-label {
        font-weight: 600;
        color: #4a5568;
    }

    .task-info-value {
        color: #2d3748;
        font-weight: 500;
    }

    .status-badge-enhanced {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .priority-badge-enhanced {
        padding: 0.375rem 0.875rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .progress-indicator {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin: 2rem 0;
    }

    .progress-step {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #718096;
        transition: all 0.3s ease;
    }

    .progress-step.active {
        background: #667eea;
        color: white;
        transform: scale(1.1);
    }

    .progress-step.completed {
        background: #48bb78;
        color: white;
    }

    .progress-line {
        width: 60px;
        height: 3px;
        background: #e2e8f0;
        border-radius: 2px;
    }

    .progress-line.active {
        background: #667eea;
    }

    .floating-action {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 1000;
    }

    .preview-modal {
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
    }

    .preview-content {
        background: white;
        border-radius: 20px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .email-preview {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }

    .preview-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        text-align: center;
    }

    .preview-body {
        padding: 1.5rem;
        background: #f8fafc;
    }

    .character-count {
        font-size: 0.8rem;
        color: #718096;
        text-align: right;
        margin-top: 0.5rem;
    }

    .character-count.warning {
        color: #f6ad55;
    }

    .character-count.danger {
        color: #f56565;
    }

    @media (max-width: 768px) {
        .email-prep-container {
            padding: 1rem 0;
        }

        .form-section {
            padding: 1rem;
        }

        .email-header {
            padding: 1.5rem;
        }

        .btn-enhanced {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>

<div class="email-prep-container">
    <div class="container-fluid">
        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-step completed">
                <i class="bx bx-check"></i>
            </div>
            <div class="progress-line active"></div>
            <div class="progress-step active">
                <i class="bx bx-edit"></i>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step">
                <i class="bx bx-send"></i>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="email-card">
                    <!-- Header -->
                    <div class="email-header">
                        <i class="bx bx-envelope email-icon"></i>
                        <h2 class="mb-2">Prepare Confirmation Email</h2>
                        <p class="mb-0 opacity-90">Send a professional confirmation email for task completion</p>
                    </div>

                    <!-- Form Section -->
                    <div class="form-section">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('tasks.store-email-preparation', $task) }}" method="POST" enctype="multipart/form-data" id="emailForm">
                            @csrf

                            <!-- Email Recipients -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-enhanced">
                                        <label for="to_emails" class="form-label-enhanced">
                                            <i class="bx bx-user"></i>To Recipients <span class="text-danger">*</span>
                                        </label>
                                        <div class="email-input-group">
                                            <input type="text" class="form-control form-control-enhanced @error('to_emails') is-invalid @enderror"
                                                   id="to_emails" name="to_emails"
                                                   value="{{ old('to_emails', $emailPreparation->to_emails ?? '') }}"
                                                   placeholder="client@company.com, manager@company.com"
                                                   autocomplete="off">
                                            <div class="email-suggestions" id="to_suggestions"></div>
                                        </div>
                                        <div class="form-text">Separate multiple emails with commas</div>
                                        @error('to_emails')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-enhanced">
                                        <label for="cc_emails" class="form-label-enhanced">
                                            <i class="bx bx-copy"></i>CC Recipients
                                        </label>
                                        <div class="email-input-group">
                                            <input type="text" class="form-control form-control-enhanced @error('cc_emails') is-invalid @enderror"
                                                   id="cc_emails" name="cc_emails"
                                                   value="{{ old('cc_emails', $emailPreparation->cc_emails ?? '') }}"
                                                   placeholder="supervisor@company.com"
                                                   autocomplete="off">
                                            <div class="email-suggestions" id="cc_suggestions"></div>
                                        </div>
                                        <div class="form-text">Carbon copy recipients</div>
                                        @error('cc_emails')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group-enhanced">
                                <label for="bcc_emails" class="form-label-enhanced">
                                    <i class="bx bx-hide"></i>BCC Recipients
                                </label>
                                <div class="email-input-group">
                                    <input type="text" class="form-control form-control-enhanced @error('bcc_emails') is-invalid @enderror"
                                           id="bcc_emails" name="bcc_emails"
                                           value="{{ old('bcc_emails', $emailPreparation->bcc_emails ?? '') }}"
                                           placeholder="archive@company.com"
                                           autocomplete="off">
                                    <div class="email-suggestions" id="bcc_suggestions"></div>
                                </div>
                                <div class="form-text">Blind carbon copy recipients (hidden from other recipients)</div>
                                @error('bcc_emails')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Subject -->
                            <div class="form-group-enhanced">
                                <label for="subject" class="form-label-enhanced">
                                    <i class="bx bx-message-square"></i>Email Subject <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-enhanced @error('subject') is-invalid @enderror"
                                       id="subject" name="subject"
                                       value="{{ old('subject', $emailPreparation->subject ?? 'Task Completion Confirmation - ' . $task->title) }}"
                                       placeholder="Enter a clear and professional subject line">
                                <div class="character-count" id="subject-count">0/100 characters</div>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email Body -->
                            <div class="form-group-enhanced">
                                <label for="body" class="form-label-enhanced">
                                    <i class="bx bx-edit"></i>Email Message <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control form-control-enhanced @error('body') is-invalid @enderror"
                                          id="body" name="body" rows="8"
                                          placeholder="Write a professional message about the task completion. This will be included in the email along with task details...">{{ old('body', $emailPreparation->body ?? '') }}</textarea>
                                <div class="character-count" id="body-count">0/2000 characters</div>
                                <div class="form-text">This message will be included in the email along with task details</div>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Attachments -->
                            <div class="form-group-enhanced">
                                <label class="form-label-enhanced">
                                    <i class="bx bx-paperclip"></i>Attachments
                                </label>
                                <div class="attachment-area" id="attachmentArea">
                                    <i class="bx bx-cloud-upload" style="font-size: 2rem; color: #667eea; margin-bottom: 1rem;"></i>
                                    <h5>Drop files here or click to browse</h5>
                                    <p class="text-muted mb-3">Support for PDF, DOC, XLS, PPT, images and more</p>
                                    <input type="file" class="d-none" id="attachments" name="attachments[]" multiple
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('attachments').click()">
                                        <i class="bx bx-plus me-1"></i>Choose Files
                                    </button>
                                </div>
                                <div class="attachment-preview" id="attachmentPreview"></div>
                                <div class="form-text">Maximum 10MB per file. You can select multiple files.</div>
                                @error('attachments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Gmail Integration Section -->
                            @if(auth()->user()->hasGmailConnected())
                                <div class="form-group-enhanced">
                                    <div class="alert alert-info d-flex align-items-center">
                                        <i class="bx bx-check-circle me-2"></i>
                                        <div>
                                            <strong>Gmail Connected!</strong><br>
                                            <small>You can send this email from your Gmail account ({{ auth()->user()->email }})</small>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="use_gmail" name="use_gmail" value="1" checked>
                                        <label class="form-check-label" for="use_gmail">
                                            <strong>Send via Gmail</strong> - Send this email from your Gmail account instead of the system SMTP
                                        </label>
                                    </div>
                                </div>
                            @else
                                <div class="form-group-enhanced">
                                    <div class="alert alert-warning d-flex align-items-center">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <div>
                                            <strong>Gmail Not Connected</strong><br>
                                            <small>Connect your Gmail account to send emails from your own Gmail address</small>
                                        </div>
                                    </div>
                                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bx bx-envelope me-1"></i>Connect Gmail Account
                                    </a>
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
                                <button type="submit" class="btn btn-enhanced btn-primary-enhanced">
                                    <i class="bx bx-save me-2"></i>Save Draft
                                </button>
                                <button type="button" class="btn btn-enhanced btn-success-enhanced" id="previewEmailBtn">
                                    <i class="bx bx-show me-2"></i>Preview Email
                                </button>
                                <button type="button" class="btn btn-enhanced btn-success-enhanced" id="sendEmailBtn"
                                        {{ !$emailPreparation ? 'disabled' : '' }}>
                                    <i class="bx bx-send me-2"></i>Send Email
                                </button>
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-enhanced btn-secondary-enhanced">
                                    <i class="bx bx-arrow-back me-2"></i>Back to Task
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Task Information Sidebar -->
            <div class="col-12 col-xl-4 mt-4 mt-xl-0">
                <div class="task-info-card">
                    <h5 class="mb-3">
                        <i class="bx bx-info-circle me-2"></i>Task Information
                    </h5>

                    <div class="task-info-item">
                        <span class="task-info-label">Title:</span>
                        <span class="task-info-value">{{ $task->title }}</span>
                    </div>

                    <div class="task-info-item">
                        <span class="task-info-label">Project:</span>
                        <span class="task-info-value">{{ $task->project->name ?? 'No project' }}</span>
                    </div>

                    <div class="task-info-item">
                        <span class="task-info-label">Assigned To:</span>
                        <span class="task-info-value">{{ $task->assignee->name ?? 'Unassigned' }}</span>
                    </div>

                    <div class="task-info-item">
                        <span class="task-info-label">Status:</span>
                        <span class="status-badge-enhanced bg-info">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                    </div>

                    <div class="task-info-item">
                        <span class="task-info-label">Priority:</span>
                        <span class="priority-badge-enhanced bg-{{ $task->priority_badge_class }}">{{ ucfirst($task->priority) }}</span>
                    </div>

                    @if($task->due_date)
                    <div class="task-info-item">
                        <span class="task-info-label">Due Date:</span>
                        <span class="task-info-value">{{ $task->due_date->format('M d, Y') }}</span>
                    </div>
                    @endif

                    @if($task->completion_notes)
                    <div class="task-info-item">
                        <span class="task-info-label">Completion Notes:</span>
                        <span class="task-info-value">{{ $task->completion_notes }}</span>
                    </div>
                    @endif
                </div>

                @if($emailPreparation && $emailPreparation->attachments)
                <div class="task-info-card mt-3">
                    <h5 class="mb-3">
                        <i class="bx bx-paperclip me-2"></i>Current Attachments
                    </h5>
                    @foreach($emailPreparation->attachments as $attachment)
                        <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                            <i class="bx bx-file me-2 text-primary"></i>
                            <span class="text-truncate">{{ basename($attachment) }}</span>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Email Preview Modal -->
<div class="modal fade preview-modal" id="emailPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content preview-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-show me-2"></i>Email Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="email-preview">
                    <div class="preview-header">
                        <h4>✅ Task Completed Successfully!</h4>
                        <p class="mb-0">Task Completion Confirmation</p>
                    </div>
                    <div class="preview-body">
                        <h5>Hello,</h5>
                        <p>I'm pleased to inform you that the task <strong>"{{ $task->title }}"</strong> has been completed successfully.</p>

                        <div id="previewBodyContent" class="bg-white p-3 rounded border-start border-primary border-4">
                            <h6>📝 Additional Information</h6>
                            <div id="previewBodyText">Your message will appear here...</div>
                        </div>

                        <div class="bg-success bg-opacity-10 p-3 rounded mt-3">
                            <h6>✅ Completion Details</h6>
                            <p class="mb-1"><strong>Completed by:</strong> {{ Auth::user()->name }}</p>
                            <p class="mb-1"><strong>Completed on:</strong> {{ now()->format('M d, Y \a\t g:i A') }}</p>
                            @if($task->completion_notes)
                                <p class="mb-0"><strong>Completion Notes:</strong> {{ $task->completion_notes }}</p>
                            @endif
                        </div>

                        <div class="mt-3">
                            <p class="mb-0">Thank you for your attention to this matter. If you have any questions or need further information, please don't hesitate to contact me.</p>
                            <p class="mb-0 mt-2">Best regards,<br><strong>{{ Auth::user()->name }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updatePreview()">Refresh Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sendEmailBtn = document.getElementById('sendEmailBtn');
    const previewEmailBtn = document.getElementById('previewEmailBtn');
    const emailForm = document.getElementById('emailForm');
    const attachmentArea = document.getElementById('attachmentArea');
    const attachmentInput = document.getElementById('attachments');
    const attachmentPreview = document.getElementById('attachmentPreview');

    // Email suggestions data (you can populate this from your database)
    const emailSuggestions = [
        'client@company.com',
        'manager@company.com',
        'supervisor@company.com',
        'admin@company.com',
        'support@company.com',
        'info@company.com',
        'contact@company.com',
        'team@company.com'
    ];

    // Character count functionality
    function updateCharacterCount(inputId, countId, maxLength) {
        const input = document.getElementById(inputId);
        const count = document.getElementById(countId);

        if (input && count) {
            const length = input.value.length;
            count.textContent = `${length}/${maxLength} characters`;

            count.className = 'character-count';
            if (length > maxLength * 0.8) {
                count.className += ' warning';
            }
            if (length > maxLength * 0.95) {
                count.className += ' danger';
            }
        }
    }

    // Email suggestions functionality
    function setupEmailSuggestions(inputId, suggestionsId) {
        const input = document.getElementById(inputId);
        const suggestions = document.getElementById(suggestionsId);

        if (!input || !suggestions) return;

        input.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            const filteredSuggestions = emailSuggestions.filter(email =>
                email.toLowerCase().includes(value) && value.length > 0
            );

            if (filteredSuggestions.length > 0 && value.length > 0) {
                suggestions.innerHTML = filteredSuggestions.map(email =>
                    `<div class="email-suggestion" onclick="selectEmail('${inputId}', '${email}')">${email}</div>`
                ).join('');
                suggestions.style.display = 'block';
            } else {
                suggestions.style.display = 'none';
            }
        });

        input.addEventListener('blur', function() {
            setTimeout(() => {
                suggestions.style.display = 'none';
            }, 200);
        });
    }

    // Select email from suggestions
    window.selectEmail = function(inputId, email) {
        const input = document.getElementById(inputId);
        const suggestions = document.getElementById(inputId.replace('_emails', '_suggestions'));

        if (input) {
            const currentValue = input.value.trim();
            if (currentValue) {
                input.value = currentValue + ', ' + email;
            } else {
                input.value = email;
            }
            suggestions.style.display = 'none';
            input.focus();
        }
    };

    // Attachment drag and drop functionality
    attachmentArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    attachmentArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    attachmentArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');

        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    attachmentArea.addEventListener('click', function() {
        attachmentInput.click();
    });

    attachmentInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.size > 10 * 1024 * 1024) { // 10MB limit
                alert(`File ${file.name} is too large. Maximum size is 10MB.`);
                return;
            }

            const attachmentItem = document.createElement('div');
            attachmentItem.className = 'attachment-item';
            attachmentItem.innerHTML = `
                <i class="bx bx-file"></i>
                <span>${file.name}</span>
                <span class="attachment-remove" onclick="removeAttachment(this)">×</span>
            `;
            attachmentPreview.appendChild(attachmentItem);
        });
    }

    window.removeAttachment = function(element) {
        element.parentElement.remove();
    };

    // Preview email functionality
    previewEmailBtn.addEventListener('click', function() {
        updatePreview();
        const modal = new bootstrap.Modal(document.getElementById('emailPreviewModal'));
        modal.show();
    });

    window.updatePreview = function() {
        const bodyText = document.getElementById('body').value;
        const previewBodyText = document.getElementById('previewBodyText');

        if (bodyText.trim()) {
            previewBodyText.innerHTML = bodyText.replace(/\n/g, '<br>');
        } else {
            previewBodyText.innerHTML = '<em>No additional message provided.</em>';
        }
    };

    // Send email functionality
    sendEmailBtn.addEventListener('click', function() {
        const useGmail = document.getElementById('use_gmail') && document.getElementById('use_gmail').checked;
        const confirmMessage = useGmail ?
            'Are you sure you want to send this email via Gmail? This action cannot be undone.' :
            'Are you sure you want to send this email? This action cannot be undone.';

        if (confirm(confirmMessage)) {
            // Create a form for sending the email
            const sendForm = document.createElement('form');
            sendForm.method = 'POST';
            sendForm.action = '{{ route("tasks.send-confirmation-email", $task) }}';

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            sendForm.appendChild(csrfToken);

            // Add Gmail option if checked
            if (useGmail) {
                const gmailInput = document.createElement('input');
                gmailInput.type = 'hidden';
                gmailInput.name = 'use_gmail';
                gmailInput.value = '1';
                sendForm.appendChild(gmailInput);
            }

            document.body.appendChild(sendForm);
            sendForm.submit();
        }
    });

    // Form validation and enable/disable send button
    const requiredFields = ['to_emails', 'subject', 'body'];
    const checkForm = () => {
        const hasRequiredData = requiredFields.every(field => {
            const input = document.getElementById(field);
            return input && input.value.trim() !== '';
        });
        sendEmailBtn.disabled = !hasRequiredData;

        // Update progress indicator
        const progressSteps = document.querySelectorAll('.progress-step');
        if (hasRequiredData) {
            progressSteps[2].classList.add('active');
            document.querySelectorAll('.progress-line')[1].classList.add('active');
        } else {
            progressSteps[2].classList.remove('active');
            document.querySelectorAll('.progress-line')[1].classList.remove('active');
        }
    };

    // Set up event listeners
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.addEventListener('input', checkForm);

            // Character count for subject and body
            if (field === 'subject') {
                input.addEventListener('input', () => updateCharacterCount('subject', 'subject-count', 100));
            } else if (field === 'body') {
                input.addEventListener('input', () => updateCharacterCount('body', 'body-count', 2000));
            }
        }
    });

    // Set up email suggestions
    setupEmailSuggestions('to_emails', 'to_suggestions');
    setupEmailSuggestions('cc_emails', 'cc_suggestions');
    setupEmailSuggestions('bcc_emails', 'bcc_suggestions');

    // Initial setup
    checkForm();
    updateCharacterCount('subject', 'subject-count', 100);
    updateCharacterCount('body', 'body-count', 2000);

    // Auto-save draft functionality (optional)
    let autoSaveTimeout;
    const autoSave = () => {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // You can implement auto-save functionality here
            console.log('Auto-saving draft...');
        }, 5000); // Auto-save after 5 seconds of inactivity
    };

    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.addEventListener('input', autoSave);
        }
    });

    // Form submission with loading state
    emailForm.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Saving...';
            submitBtn.disabled = true;
        }
    });
});
</script>
@endsection
