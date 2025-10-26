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

    .email-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }

    .progress-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin: 2rem auto;
        max-width: 600px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .progress-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin-bottom: 1rem;
    }

    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
        flex: 1;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .progress-step.active .step-circle {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .step-label {
        font-size: 0.875rem;
        color: #64748b;
        text-align: center;
        font-weight: 500;
    }

    .progress-step.active .step-label {
        color: #667eea;
        font-weight: 600;
    }

    .progress-line {
        position: absolute;
        top: 20px;
        left: 10%;
        right: 10%;
        height: 2px;
        background: #e2e8f0;
        z-index: 1;
    }

    .progress-line.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.875rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .btn-enhanced {
        border: none;
        border-radius: 12px;
        padding: 0.875rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        cursor: pointer;
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

    .btn-enhanced:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
        color: white;
    }

    .btn-info {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        color: white;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
        color: white;
    }

    .btn-outline-secondary {
        background: transparent;
        color: #718096;
        border: 2px solid #e2e8f0;
    }

    .btn-outline-secondary:hover {
        background: #f7fafc;
        border-color: #cbd5e0;
        color: #4a5568;
    }

    .alert {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert-success {
        background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
        color: #22543d;
        border-left: 4px solid #48bb78;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fffaf0 0%, #fed7aa 100%);
        color: #744210;
        border-left: 4px solid #ed8936;
    }

    .alert-info {
        background: linear-gradient(135deg, #f0f7ff 0%, #bee3f8 100%);
        color: #2a4365;
        border-left: 4px solid #4299e1;
    }

    .task-info-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
    }

    .task-info-header {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f7fafc;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 500;
        color: #4a5568;
    }

    .info-value {
        color: #2d3748;
        font-weight: 600;
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-ready {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .status-normal {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        color: white;
    }

    .status-high {
        background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
        color: white;
    }

    .status-urgent {
        background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
        color: white;
    }

    .suggestion-list {
        background: white;
        border: 2px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 12px 12px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        position: absolute;
        width: 100%;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .suggestion-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f7fafc;
        transition: background-color 0.2s ease;
    }

    .suggestion-item:hover {
        background-color: #f7fafc;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .email-preview {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin-top: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 2px solid #e2e8f0;
    }

    .preview-header {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .preview-content {
        background: #f7fafc;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .preview-subject {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }

    .preview-body {
        color: #4a5568;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .template-selector {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 2px solid #e2e8f0;
    }

    .template-header {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .template-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .template-option {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .template-option:hover {
        border-color: #667eea;
        background: #f7fafc;
    }

    .template-option.selected {
        border-color: #667eea;
        background: linear-gradient(135deg, #f0f7ff 0%, #e6f3ff 100%);
    }

    .template-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .template-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.25rem;
    }

    .template-description {
        font-size: 0.875rem;
        color: #718096;
    }

    @media (max-width: 768px) {
        .email-prep-container {
            padding: 1rem 0;
        }

        .progress-steps {
            flex-direction: column;
            gap: 1rem;
        }

        .progress-line {
            display: none;
        }

        .template-options {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="email-prep-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
        <!-- Progress Indicator -->
                <div class="progress-container">
                    <div class="progress-steps">
                        <div class="progress-step active">
                            <div class="step-circle">1</div>
                            <div class="step-label">Task Info</div>
            </div>
            <div class="progress-line active"></div>
            <div class="progress-step active">
                            <div class="step-circle">2</div>
                            <div class="step-label">Email Details</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step">
                            <div class="step-circle">3</div>
                            <div class="step-label">Send Email</div>
                        </div>
            </div>
        </div>

                <!-- Main Email Card -->
                <div class="email-card">
                    <div class="email-header">
                        <i class="bx bx-envelope-open email-icon"></i>
                        <h1 class="h2 mb-2">Prepare Confirmation Email</h1>
                        <p class="mb-0 opacity-90">Send a professional confirmation email for task completion.</p>
                    </div>

                    <div class="p-4">
                        <!-- Task Information -->
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle"></i>
                            <div>
                                <strong>Task:</strong> {{ $task->title }}<br>
                                <small>Project: {{ $task->project->name ?? 'N/A' }} | Due: {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}</small>
                            </div>
                            </div>

                        <!-- Email Template Selector -->
                        <div class="template-selector">
                            <div class="template-header">
                                <i class="bx bx-template"></i>
                                Select Email Template
                                        </div>
                            <div class="template-options">
                                <div class="template-option" data-template="none">
                                    <div class="template-icon">📄</div>
                                    <div class="template-title">None (Plain Text)</div>
                                    <div class="template-description">Start with a blank email template</div>
                                    </div>
                                <div class="template-option" data-template="project_completion">
                                    <div class="template-icon">✅</div>
                                    <div class="template-title">Project Completion</div>
                                    <div class="template-description">Notify client of successful project completion</div>
                                </div>
                                <div class="template-option" data-template="task_update">
                                    <div class="template-icon">📝</div>
                                    <div class="template-title">Task Update</div>
                                    <div class="template-description">Provide progress update to client</div>
                                        </div>
                                <div class="template-option" data-template="approval_request">
                                    <div class="template-icon">✋</div>
                                    <div class="template-title">Approval Request</div>
                                    <div class="template-description">Request client approval for completed work</div>
                                </div>
                                <div class="template-option" data-template="design_ready">
                                    <div class="template-icon">🎨</div>
                                    <div class="template-title">Design Ready</div>
                                    <div class="template-description">Notify client that design is ready for review</div>
                                    </div>
                                </div>
                            </div>

                        <!-- Email Form -->
                        <form id="emailForm" method="POST" action="{{ route('tasks.store-email-preparation', $task) }}">
                            @csrf

                            <!-- Recipients -->
                            <div class="form-group">
                                <label for="to_emails" class="form-label">
                                    <i class="bx bx-user"></i>
                                    To Recipients *
                                </label>
                                <input type="email"
                                       class="form-control"
                                       id="to_emails"
                                       name="to_emails"
                                       value="{{ old('to_emails', $emailPreparation->to_emails ?? '') }}"
                                       placeholder="Enter recipient email addresses (comma-separated)"
                                       required>
                                <div id="to_suggestions" class="suggestion-list" style="display: none;"></div>
                            </div>

                            <div class="form-group">
                                <label for="cc_emails" class="form-label">
                                    <i class="bx bx-user-plus"></i>
                                    CC Recipients
                                </label>
                                <input type="email"
                                       class="form-control"
                                       id="cc_emails"
                                       name="cc_emails"
                                       value="{{ old('cc_emails', $emailPreparation->cc_emails ?? '') }}"
                                       placeholder="Enter CC email addresses (comma-separated)">
                                <div id="cc_suggestions" class="suggestion-list" style="display: none;"></div>
                                <small class="text-muted">
                                    Project manager is automatically added to TO field. Engineering team, project owner, and all project contractors are automatically added to CC field.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="bcc_emails" class="form-label">
                                    <i class="bx bx-user-check"></i>
                                    BCC Recipients
                                </label>
                                <input type="email"
                                       class="form-control"
                                       id="bcc_emails"
                                       name="bcc_emails"
                                       value="{{ old('bcc_emails', $emailPreparation->bcc_emails ?? '') }}"
                                       placeholder="Enter BCC email addresses (comma-separated)">
                                <div id="bcc_suggestions" class="suggestion-list" style="display: none;"></div>
                            </div>

                            <!-- Email Subject -->
                            <div class="form-group">
                                <label for="subject" class="form-label">
                                    <i class="bx bx-message-square-detail"></i>
                                    Email Subject *
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="subject"
                                       name="subject"
                                       value="{{ old('subject', 'Task Completion Confirmation - ' . $task->title) }}"
                                       placeholder="Enter email subject"
                                       required>
                            </div>

                            <!-- Email Body -->
                            <div class="form-group">
                                <label for="body" class="form-label">
                                    <i class="bx bx-edit"></i>
                                    Email Body *
                                        </label>
                                <textarea class="form-control"
                                          id="body"
                                          name="body"
                                          rows="8"
                                          placeholder="Enter your email message here..."
                                          required>{{ old('body') }}</textarea>
                                <small class="text-muted">HTML styling is supported! The template includes your company logo and professional formatting.</small>
                                    </div>

                            <!-- Action Buttons -->
                            <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
                                {{--  <button type="button" class="btn btn-enhanced btn-secondary" id="previewBtn">
                                    <i class="bx bx-show me-2"></i>Preview Email
                                </button>

                                <button type="submit" class="btn btn-enhanced btn-primary" id="saveDraftBtn">
                                    <i class="bx bx-save me-2"></i>Save Draft
                                </button>  --}}

                                <button type="button" class="btn btn-enhanced btn-warning" id="sendViaGmailBtn">
                                    <i class="bx bxl-gmail me-2"></i>Send via Gmail (Recommended)
                                </button>

                                {{--  <button type="submit" class="btn btn-enhanced btn-success" id="sendViaServerBtn" name="send_email" value="1">
                                    <i class="bx bx-send me-2"></i>Send via Server
                                </button>  --}}

                                {{--  <button type="button" class="btn btn-enhanced btn-warning" id="directSendBtn">
                                    <i class="bx bx-send me-2"></i>Send & Continue (Direct)
                                </button>  --}}

                                <button type="button" class="btn btn-enhanced btn-info" id="markAsSentBtn">
                                    <i class="bx bx-check-double me-2"></i>Mark as Sent (After Gmail)
                                </button>

                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-enhanced btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-2"></i>Back to Task
                                </a>

                                {{--  <button type="button" class="btn btn-enhanced btn-success" id="continueToNextStepBtn" style="display: none;">
                                    <i class="bx bx-check-double me-2"></i>Continue to Next Step
                                </button>

                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-enhanced btn-warning" style="display: none;" id="directNavBtn">
                                    <i class="bx bx-skip-next me-2"></i>Go to Task (Direct)
                                </a>  --}}
                    </div>
                        </form>

                        <!-- Email Preview Modal -->
                        <div class="email-preview" id="emailPreview" style="display: none;">
                            <div class="preview-header">
                                <i class="bx bx-show"></i>
                                Email Preview
                        </div>
                            <div class="preview-content">
                                <div class="preview-subject" id="previewSubject"></div>
                                <div class="preview-body" id="previewBody"></div>
            </div>
        </div>
    </div>
</div>

                <!-- Task Information Card -->
                <div class="task-info-card">
                    <div class="task-info-header">
                        <i class="bx bx-info-circle me-2"></i>
                        Task Information
            </div>
                    <div class="info-row">
                        <span class="info-label">Title</span>
                        <span class="info-value">{{ $task->title }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Project</span>
                        <span class="info-value">{{ $task->project->name ?? 'N/A' }}</span>
                        </div>
                    <div class="info-row">
                        <span class="info-label">Assigned To</span>
                        <span class="info-value">{{ $task->assignee->name ?? 'Unassigned' }}</span>
                        </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-ready">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        </div>
                    <div class="info-row">
                        <span class="info-label">Priority</span>
                        <span class="status-badge status-{{ $task->priority === 'high' ? 'high' : ($task->priority === 'urgent' ? 'urgent' : 'normal') }}">
                            {{ strtoupper($task->priority) }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Due Date</span>
                        <span class="info-value">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}</span>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - initializing email preparation functionality');

    // Get form elements
    const emailForm = document.getElementById('emailForm');
    const toEmails = document.getElementById('to_emails');
    const ccEmails = document.getElementById('cc_emails');
    const bccEmails = document.getElementById('bcc_emails');
    const subject = document.getElementById('subject');
    const body = document.getElementById('body');
    const previewBtn = document.getElementById('previewBtn');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const sendViaGmailBtn = document.getElementById('sendViaGmailBtn');
    const sendViaServerBtn = document.getElementById('sendViaServerBtn');
    const markAsSentBtn = document.getElementById('markAsSentBtn');
    const continueToNextStepBtn = document.getElementById('continueToNextStepBtn');
    const directNavBtn = document.getElementById('directNavBtn');
    const directSendBtn = document.getElementById('directSendBtn');
    const emailPreview = document.getElementById('emailPreview');
    const previewSubject = document.getElementById('previewSubject');
    const previewBody = document.getElementById('previewBody');

    console.log('DOM Elements found:', {
        emailForm: !!emailForm,
        toEmails: !!toEmails,
        subject: !!subject,
        body: !!body,
        sendViaGmailBtn: !!sendViaGmailBtn,
        markAsSentBtn: !!markAsSentBtn
    });

    // Email templates
    const taskTitle = '{{ addslashes($task->title) }}';
    const taskId = '{{ $task->id }}';
    const companyName = 'Orion Contracting';
    const logoUrl = '{{ asset("uploads/logo-blue.webp") }}';

    // User data for dynamic signatures
    const assigneeData = {
        name: '{{ $task->assignee->name ?? "Task Manager" }}',
        position: '{{ $task->assignee->position ?? "Project Manager" }}',
        mobile: '{{ $task->assignee->mobile ?? "N/A" }}',
        email: '{{ $task->assignee->email ?? "engineering@orion-contracting.com" }}',
        image: '{{ $task->assignee->image ?? "default.png" }}',
        hasCustomImage: '{{ $task->assignee->image && strtolower($task->assignee->image) !== "default.png" ? "true" : "false" }}'
    };

    const emailTemplates = {
        none: {
            subject: '',
            plainTextBody: '',
            body: ''
        },
        project_completion: {
            subject: '✅ Project Completed: ' + taskTitle,
            plainTextBody: '✅ PROJECT COMPLETED SUCCESSFULLY!\n\n' +
                'Dear Valued Client,\n\n' +
                'We are pleased to inform you that your project "' + taskTitle + '" has been completed successfully!\n\n' +
                '📋 PROJECT DETAILS:\n' +
                '• Project Name: ' + taskTitle + '\n' +
                '• Task ID: #' + taskId + '\n' +
                '• Status: ✅ Completed\n' +
                '• Completion Date: ' + new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '\n\n' +
                'All deliverables have been prepared according to your specifications and are ready for your review.\n\n' +
                '🎯 NEXT STEPS:\n' +
                '• Review the completed work and deliverables\n' +
                '• Provide your feedback or approval\n' +
                '• Request any modifications if needed\n\n' +
                'Thank you for choosing ' + companyName + '. We look forward to your feedback!\n\n' +
                'Best regards,\n' +
                assigneeData.name + '\n' +
                assigneeData.position + '\n' +
                'Orion Contracting\n' +
                'Mobile: ' + assigneeData.mobile + '\n' +
                'Email: ' + assigneeData.email + '\n' +
                'Website: www.orioncc.com',
            body: '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f4f6f9; padding: 20px;">' +
                '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center; border-radius: 12px 12px 0 0;">' +
                    '<img src="' + logoUrl + '" alt="' + companyName + '" style="max-width: 200px; height: auto; margin-bottom: 20px;">' +
                    '<h1 style="color: white; margin: 0; font-size: 28px;">✅ Project Completed Successfully!</h1>' +
                    '<span style="background: #48bb78; color: white; padding: 8px 20px; border-radius: 20px; display: inline-block; font-weight: bold; margin-top: 10px;">COMPLETED</span>' +
                '</div>' +
                '<div style="background: white; padding: 40px 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">' +
                    '<h2 style="color: #2d3748; margin-top: 0;">Dear Valued Client,</h2>' +
                    '<p>We are pleased to inform you that your project <strong>"' + taskTitle + '"</strong> has been completed successfully!</p>' +
                    '<div style="background: #f0f7ff; border-left: 4px solid #4299e1; padding: 20px; margin: 20px 0; border-radius: 8px;">' +
                        '<h3 style="margin-top:0;">📋 Project Details:</h3>' +
                        '<p><strong>Project Name:</strong> ' + taskTitle + '</p>' +
                        '<p><strong>Task ID:</strong> #' + taskId + '</p>' +
                        '<p><strong>Status:</strong> <span style="color: #48bb78; font-weight: bold;">✅ Completed</span></p>' +
                        '<p><strong>Completion Date:</strong> ' + new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '</p>' +
                    '</div>' +
                    '<p>All deliverables have been prepared according to your specifications and are ready for your review.</p>' +
                    '<h3>🎯 Next Steps:</h3>' +
                    '<ul>' +
                        '<li>Review the completed work and deliverables</li>' +
                        '<li>Provide your feedback or approval</li>' +
                        '<li>Request any modifications if needed</li>' +
                    '</ul>' +
                    '<p>Thank you for choosing ' + companyName + '. We look forward to your feedback!</p>' +
                    '<div style="margin-top:30px;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%);border-radius:16px;padding:20px;border:2px solid #cbd5e0;box-shadow:0 4px 12px rgba(0,0,0,0.1);">' +
                      '<div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);height:4px;border-radius:2px;margin-bottom:16px;"></div>' +
                      '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">' +
                        '<tr>' +
                          '<td style="width:80px;vertical-align:top;padding-right:16px;">' +
                            (assigneeData.hasCustomImage === 'true' ?
                              '<img src="{{ asset("storage/") }}' + assigneeData.image + '" alt="' + assigneeData.name + '" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid #667eea;box-shadow:0 4px 8px rgba(102,126,234,0.3);">' :
                              '<div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:24px;border:3px solid #667eea;box-shadow:0 4px 8px rgba(102,126,234,0.3);">' + assigneeData.name.charAt(0).toUpperCase() + '</div>') +
                          '</td>' +
                          '<td style="vertical-align:top;font-family:Arial,sans-serif;">' +
                            '<div style="background:white;padding:16px;border-radius:12px;border-left:4px solid #667eea;box-shadow:0 2px 8px rgba(0,0,0,0.05);">' +
                              '<div style="font-weight:700;color:#2d3748;font-size:18px;line-height:1.2;margin-bottom:4px;">' + assigneeData.name + '</div>' +
                              '<div style="color:#667eea;font-weight:600;font-size:14px;margin-bottom:8px;background:linear-gradient(135deg,#f0f7ff,#e6f3ff);padding:4px 8px;border-radius:6px;display:inline-block;">' + assigneeData.position + '</div>' +
                              '<div style="color:#4a5568;font-size:14px;font-weight:600;margin-bottom:12px;">🏢 Orion Contracting</div>' +
                              '<div style="display:flex;flex-wrap:wrap;gap:12px;">' +
                                '<div style="background:linear-gradient(135deg,#48bb78,#38a169);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">📱 ' + assigneeData.mobile + '</div>' +
                                '<div style="background:linear-gradient(135deg,#4299e1,#3182ce);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">📧 <a href="mailto:' + assigneeData.email + '" style="color:white;text-decoration:none;">' + assigneeData.email + '</a></div>' +
                                '<div style="background:linear-gradient(135deg,#9f7aea,#805ad5);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">🌐 <a href="https://www.orioncc.com" style="color:white;text-decoration:none;">www.orioncc.com</a></div>' +
                              '</div>' +
                            '</div>' +
                          '</td>' +
                        '</tr>' +
                      '</table>' +
                    '</div>' +
                '</div>' +
            '</div>'
        },
        task_update: {
            subject: '📝 Task Update: ' + taskTitle,
            plainTextBody: '📝 TASK PROGRESS UPDATE\n\n' +
                'Dear Valued Client,\n\n' +
                'We would like to provide you with an update on your project "' + taskTitle + '".\n\n' +
                '📊 CURRENT STATUS:\n' +
                '• Project: ' + taskTitle + '\n' +
                '• Task ID: #' + taskId + '\n' +
                '• Update Date: ' + new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '\n\n' +
                '✅ PROGRESS UPDATE:\n' +
                '[Describe the current progress and any milestones achieved]\n\n' +
                '🔄 NEXT STEPS:\n' +
                '• [Next action item 1]\n' +
                '• [Next action item 2]\n' +
                '• [Expected completion timeline]\n\n' +
                'If you have any questions or concerns, please don\'t hesitate to reach out to us.\n\n' +
                'Best regards,\n' +
                assigneeData.name + '\n' +
                assigneeData.position + '\n' +
                'Orion Contracting\n' +
                'Mobile: ' + assigneeData.mobile + '\n' +
                'Email: ' + assigneeData.email + '\n' +
                'Website: www.orioncc.com',
            body: '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f4f6f9; padding: 20px;">' +
                '<div style="background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); padding: 40px 20px; text-align: center; border-radius: 12px 12px 0 0;">' +
                    '<img src="' + logoUrl + '" alt="' + companyName + '" style="max-width: 200px; height: auto; margin-bottom: 20px;">' +
                    '<h1 style="color: white; margin: 0; font-size: 28px;">📝 Task Progress Update</h1>' +
                    '<span style="background: #4299e1; color: white; padding: 8px 20px; border-radius: 20px; display: inline-block; font-weight: bold; margin-top: 10px;">IN PROGRESS</span>' +
                '</div>' +
                '<div style="background: white; padding: 40px 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">' +
                    '<h2 style="color: #2d3748; margin-top: 0;">Dear Valued Client,</h2>' +
                    '<p>We would like to provide you with an update on your project <strong>"' + taskTitle + '"</strong>.</p>' +
                    '<div style="background: #f0f7ff; border-left: 4px solid #4299e1; padding: 20px; margin: 20px 0; border-radius: 8px;">' +
                        '<h3 style="margin-top:0;">📊 Current Status:</h3>' +
                        '<p><strong>Project:</strong> ' + taskTitle + '</p>' +
                        '<p><strong>Task ID:</strong> #' + taskId + '</p>' +
                        '<p><strong>Update Date:</strong> ' + new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '</p>' +
                    '</div>' +
                    '<h3>✅ Progress Update:</h3>' +
                    '<p>[Describe the current progress and any milestones achieved]</p>' +
                    '<h3>🔄 Next Steps:</h3>' +
                    '<ul>' +
                        '<li>[Next action item 1]</li>' +
                        '<li>[Next action item 2]</li>' +
                        '<li>[Expected completion timeline]</li>' +
                    '</ul>' +
                    '<p>If you have any questions or concerns, please don\'t hesitate to reach out to us.</p>' +
                    '<div style="margin-top:30px;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%);border-radius:16px;padding:20px;border:2px solid #cbd5e0;box-shadow:0 4px 12px rgba(0,0,0,0.1);">' +
                      '<div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);height:4px;border-radius:2px;margin-bottom:16px;"></div>' +
                      '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">' +
                        '<tr>' +
                          '<td style="width:80px;vertical-align:top;padding-right:16px;">' +
                            (assigneeData.hasCustomImage === 'true' ?
                              '<img src="{{ asset("storage/") }}' + assigneeData.image + '" alt="' + assigneeData.name + '" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid #667eea;box-shadow:0 4px 8px rgba(102,126,234,0.3);">' :
                              '<div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:24px;border:3px solid #667eea;box-shadow:0 4px 8px rgba(102,126,234,0.3);">' + assigneeData.name.charAt(0).toUpperCase() + '</div>') +
                          '</td>' +
                          '<td style="vertical-align:top;font-family:Arial,sans-serif;">' +
                            '<div style="background:white;padding:16px;border-radius:12px;border-left:4px solid #667eea;box-shadow:0 2px 8px rgba(0,0,0,0.05);">' +
                              '<div style="font-weight:700;color:#2d3748;font-size:18px;line-height:1.2;margin-bottom:4px;">' + assigneeData.name + '</div>' +
                              '<div style="color:#667eea;font-weight:600;font-size:14px;margin-bottom:8px;background:linear-gradient(135deg,#f0f7ff,#e6f3ff);padding:4px 8px;border-radius:6px;display:inline-block;">' + assigneeData.position + '</div>' +
                              '<div style="color:#4a5568;font-size:14px;font-weight:600;margin-bottom:12px;">🏢 Orion Contracting</div>' +
                              '<div style="display:flex;flex-wrap:wrap;gap:12px;">' +
                                '<div style="background:linear-gradient(135deg,#48bb78,#38a169);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">📱 ' + assigneeData.mobile + '</div>' +
                                '<div style="background:linear-gradient(135deg,#4299e1,#3182ce);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">📧 <a href="mailto:' + assigneeData.email + '" style="color:white;text-decoration:none;">' + assigneeData.email + '</a></div>' +
                                '<div style="background:linear-gradient(135deg,#9f7aea,#805ad5);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">🌐 <a href="https://www.orioncc.com" style="color:white;text-decoration:none;">www.orioncc.com</a></div>' +
                              '</div>' +
                            '</div>' +
                          '</td>' +
                        '</tr>' +
                      '</table>' +
                    '</div>' +
                '</div>' +
            '</div>'
        },
        approval_request: {
            subject: '✋ Approval Required: ' + taskTitle,
            plainTextBody: '✋ YOUR APPROVAL IS NEEDED\n\n' +
                'Dear Valued Client,\n\n' +
                'Your approval is required for the project "' + taskTitle + '".\n\n' +
                '⚠️ ACTION REQUIRED:\n' +
                '• Project: ' + taskTitle + '\n' +
                '• Task ID: #' + taskId + '\n' +
                '• Request Date: ' + new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '\n\n' +
                '📋 WHAT WE NEED FROM YOU:\n' +
                '• Review the completed work\n' +
                '• Provide your approval or feedback\n' +
                '• Notify us of any required changes\n\n' +
                'Please review and respond at your earliest convenience.\n\n' +
                'Thank you,\n' +
                assigneeData.name + '\n' +
                assigneeData.position + '\n' +
                'Orion Contracting\n' +
                'Mobile: ' + assigneeData.mobile + '\n' +
                'Email: ' + assigneeData.email + '\n' +
                'Website: www.orioncc.com',
            body: '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f4f6f9; padding: 20px;">' +
                '<div style="background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); padding: 40px 20px; text-align: center; border-radius: 12px 12px 0 0;">' +
                    '<img src="' + logoUrl + '" alt="' + companyName + '" style="max-width: 200px; height: auto; margin-bottom: 20px;">' +
                    '<h1 style="color: white; margin: 0; font-size: 28px;">✋ Your Approval is Needed</h1>' +
                    '<span style="background: #ed8936; color: white; padding: 8px 20px; border-radius: 20px; display: inline-block; font-weight: bold; margin-top: 10px;">PENDING APPROVAL</span>' +
                '</div>' +
                '<div style="background: white; padding: 40px 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">' +
                    '<h2 style="color: #2d3748; margin-top: 0;">Dear Valued Client,</h2>' +
                    '<p>Your approval is required for the project <strong>"' + taskTitle + '"</strong>.</p>' +
                    '<div style="background: #fffaf0; border-left: 4px solid #ed8936; padding: 20px; margin: 20px 0; border-radius: 8px;">' +
                        '<h3 style="margin-top:0;">⚠️ Action Required:</h3>' +
                        '<p><strong>Project:</strong> ' + taskTitle + '</p>' +
                        '<p><strong>Task ID:</strong> #' + taskId + '</p>' +
                        '<p><strong>Request Date:</strong> ' + new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '</p>' +
                    '</div>' +
                    '<h3>📋 What We Need From You:</h3>' +
                    '<ul>' +
                        '<li>Review the completed work</li>' +
                        '<li>Provide your approval or feedback</li>' +
                        '<li>Notify us of any required changes</li>' +
                    '</ul>' +
                    '<p><strong>Please review and respond at your earliest convenience.</strong></p>' +
                    '<div style="margin-top:30px;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%);border-radius:16px;padding:20px;border:2px solid #cbd5e0;box-shadow:0 4px 12px rgba(0,0,0,0.1);">' +
                      '<div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);height:4px;border-radius:2px;margin-bottom:16px;"></div>' +
                      '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">' +
                        '<tr>' +
                          '<td style="width:80px;vertical-align:top;padding-right:16px;">' +
                            (assigneeData.hasCustomImage === 'true' ?
                              '<img src="{{ asset("storage/") }}' + assigneeData.image + '" alt="' + assigneeData.name + '" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid #667eea;box-shadow:0 4px 8px rgba(102,126,234,0.3);">' :
                              '<div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:24px;border:3px solid #667eea;box-shadow:0 4px 8px rgba(102,126,234,0.3);">' + assigneeData.name.charAt(0).toUpperCase() + '</div>') +
                          '</td>' +
                          '<td style="vertical-align:top;font-family:Arial,sans-serif;">' +
                            '<div style="background:white;padding:16px;border-radius:12px;border-left:4px solid #667eea;box-shadow:0 2px 8px rgba(0,0,0,0.05);">' +
                              '<div style="font-weight:700;color:#2d3748;font-size:18px;line-height:1.2;margin-bottom:4px;">' + assigneeData.name + '</div>' +
                              '<div style="color:#667eea;font-weight:600;font-size:14px;margin-bottom:8px;background:linear-gradient(135deg,#f0f7ff,#e6f3ff);padding:4px 8px;border-radius:6px;display:inline-block;">' + assigneeData.position + '</div>' +
                              '<div style="color:#4a5568;font-size:14px;font-weight:600;margin-bottom:12px;">🏢 Orion Contracting</div>' +
                              '<div style="display:flex;flex-wrap:wrap;gap:12px;">' +
                                '<div style="background:linear-gradient(135deg,#48bb78,#38a169);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">📱 ' + assigneeData.mobile + '</div>' +
                                '<div style="background:linear-gradient(135deg,#4299e1,#3182ce);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">📧 <a href="mailto:' + assigneeData.email + '" style="color:white;text-decoration:none;">' + assigneeData.email + '</a></div>' +
                                '<div style="background:linear-gradient(135deg,#9f7aea,#805ad5);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">🌐 <a href="https://www.orioncc.com" style="color:white;text-decoration:none;">www.orioncc.com</a></div>' +
                              '</div>' +
                            '</div>' +
                          '</td>' +
                        '</tr>' +
                      '</table>' +
                    '</div>' +
                '</div>' +
            '</div>'
        },
        design_ready: {
            subject: '🎨 Design Ready for Review: ' + taskTitle,
            plainTextBody: '🎨 DESIGN READY FOR YOUR REVIEW\n\n' +
                'Dear Valued Client,\n\n' +
                'Great news! The design for "' + taskTitle + '" is ready for your review!\n\n' +
                '🎯 DESIGN DETAILS:\n' +
                '• Project: ' + taskTitle + '\n' +
                '• Task ID: #' + taskId + '\n' +
                '• Completion Date: ' + new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '\n\n' +
                '✨ WHAT\'S INCLUDED:\n' +
                '• Final design files and assets\n' +
                '• All requested variations\n' +
                '• Ready for your feedback\n\n' +
                '📝 NEXT STEPS:\n' +
                'Please review the attached designs and let us know your thoughts. We\'re happy to make any adjustments you need!\n\n' +
                'Looking forward to your feedback,\n' +
                assigneeData.name + '\n' +
                assigneeData.position + '\n' +
                'Orion Contracting\n' +
                'Mobile: ' + assigneeData.mobile + '\n' +
                'Email: ' + assigneeData.email + '\n' +
                'Website: www.orioncc.com',
            body: '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f4f6f9; padding: 20px;">' +
                '<div style="background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%); padding: 40px 20px; text-align: center; border-radius: 12px 12px 0 0;">' +
                    '<img src="' + logoUrl + '" alt="' + companyName + '" style="max-width: 200px; height: auto; margin-bottom: 20px;">' +
                    '<h1 style="color: white; margin: 0; font-size: 28px;">🎨 Design Ready for Your Review</h1>' +
                    '<span style="background: #9f7aea; color: white; padding: 8px 20px; border-radius: 20px; display: inline-block; font-weight: bold; margin-top: 10px;">DESIGN COMPLETE</span>' +
                '</div>' +
                '<div style="background: white; padding: 40px 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">' +
                    '<h2 style="color: #2d3748; margin-top: 0;">Dear Valued Client,</h2>' +
                    '<p>Great news! The design for <strong>"' + taskTitle + '"</strong> is ready for your review!</p>' +
                    '<div style="background: #faf5ff; border-left: 4px solid #9f7aea; padding: 20px; margin: 20px 0; border-radius: 8px;">' +
                        '<h3 style="margin-top:0;">🎯 Design Details:</h3>' +
                        '<p><strong>Project:</strong> ' + taskTitle + '</p>' +
                        '<p><strong>Task ID:</strong> #' + taskId + '</p>' +
                        '<p><strong>Completion Date:</strong> ' + new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '</p>' +
                    '</div>' +
                    '<h3>✨ What\'s Included:</h3>' +
                    '<ul>' +
                        '<li>Final design files and assets</li>' +
                        '<li>All requested variations</li>' +
                        '<li>Ready for your feedback</li>' +
                    '</ul>' +
                    '<h3>📝 Next Steps:</h3>' +
                    '<p>Please review the attached designs and let us know your thoughts. We\'re happy to make any adjustments you need!</p>' +
                    '<div style="margin-top:30px;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%);border-radius:16px;padding:20px;border:2px solid #cbd5e0;box-shadow:0 4px 12px rgba(0,0,0,0.1);">' +
                      '<div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);height:4px;border-radius:2px;margin-bottom:16px;"></div>' +
                      '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">' +
                        '<tr>' +
                          '<td style="width:80px;vertical-align:top;padding-right:16px;">' +
                            (assigneeData.hasCustomImage === 'true' ?
                              '<img src="{{ asset("storage/") }}' + assigneeData.image + '" alt="' + assigneeData.name + '" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid #667eea;box-shadow:0 4px 8px rgba(102,126,234,0.3);">' :
                              '<div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:24px;border:3px solid #667eea;box-shadow:0 4px 8px rgba(102,126,234,0.3);">' + assigneeData.name.charAt(0).toUpperCase() + '</div>') +
                          '</td>' +
                          '<td style="vertical-align:top;font-family:Arial,sans-serif;">' +
                            '<div style="background:white;padding:16px;border-radius:12px;border-left:4px solid #667eea;box-shadow:0 2px 8px rgba(0,0,0,0.05);">' +
                              '<div style="font-weight:700;color:#2d3748;font-size:18px;line-height:1.2;margin-bottom:4px;">' + assigneeData.name + '</div>' +
                              '<div style="color:#667eea;font-weight:600;font-size:14px;margin-bottom:8px;background:linear-gradient(135deg,#f0f7ff,#e6f3ff);padding:4px 8px;border-radius:6px;display:inline-block;">' + assigneeData.position + '</div>' +
                              '<div style="color:#4a5568;font-size:14px;font-weight:600;margin-bottom:12px;">🏢 Orion Contracting</div>' +
                              '<div style="display:flex;flex-wrap:wrap;gap:12px;">' +
                                '<div style="background:linear-gradient(135deg,#48bb78,#38a169);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">📱 ' + assigneeData.mobile + '</div>' +
                                '<div style="background:linear-gradient(135deg,#4299e1,#3182ce);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">📧 <a href="mailto:' + assigneeData.email + '" style="color:white;text-decoration:none;">' + assigneeData.email + '</a></div>' +
                                '<div style="background:linear-gradient(135deg,#9f7aea,#805ad5);color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">🌐 <a href="https://www.orioncc.com" style="color:white;text-decoration:none;">www.orioncc.com</a></div>' +
                              '</div>' +
                            '</div>' +
                          '</td>' +
                        '</tr>' +
                      '</table>' +
                    '</div>' +
                '</div>' +
            '</div>'
        }
    };

    // Template selection
    document.querySelectorAll('.template-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.template-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            const templateType = this.dataset.template;
            if (emailTemplates[templateType]) {
                subject.value = emailTemplates[templateType].subject;
                body.value = emailTemplates[templateType].body;
                updateProgress();
            }
        });
    });

    // Form validation and progress update
    function updateProgress() {
        const hasRequiredData = toEmails && toEmails.value.trim() !== '' &&
                               subject && subject.value.trim() !== '' &&
                               body && body.value.trim() !== '';

        // Enable/disable buttons based on form data (with null checks)
        if (saveDraftBtn) saveDraftBtn.disabled = !hasRequiredData;
        if (sendViaGmailBtn) sendViaGmailBtn.disabled = !hasRequiredData;
        if (sendViaServerBtn) sendViaServerBtn.disabled = !hasRequiredData;
        if (markAsSentBtn) markAsSentBtn.disabled = !hasRequiredData;

        // Update progress indicator
        const progressSteps = document.querySelectorAll('.progress-step');
        if (hasRequiredData) {
            progressSteps[2].classList.add('active');
            document.querySelectorAll('.progress-line')[1].classList.add('active');
            } else {
            progressSteps[2].classList.remove('active');
            document.querySelectorAll('.progress-line')[1].classList.remove('active');
        }
    }

    // Add event listeners for form fields
    [toEmails, ccEmails, bccEmails, subject, body].forEach(field => {
        if (field) {
            field.addEventListener('input', updateProgress);
        }
    });

    // Preview functionality
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            if (previewSubject) previewSubject.textContent = subject ? subject.value : '';
            if (previewBody) previewBody.innerHTML = body ? body.value : '';
            if (emailPreview) emailPreview.style.display = emailPreview.style.display === 'none' ? 'block' : 'none';
        });
    }

    // Send via Gmail functionality
    if (sendViaGmailBtn) {
        sendViaGmailBtn.addEventListener('click', function() {
        console.log('Send via Gmail button clicked!');
        const toEmailsValue = toEmails.value || '';
        const ccEmailsValue = ccEmails.value || '';
        const bccEmailsValue = bccEmails.value || '';
        const subjectValue = subject.value || '';
        const bodyValue = body.value || '';

        console.log('Form values:', { toEmailsValue, subjectValue, bodyValue });

        const gmailUrl = new URL('https://mail.google.com/mail/');
        gmailUrl.searchParams.append('view', 'cm');
        gmailUrl.searchParams.append('fs', '1');
        gmailUrl.searchParams.append('to', toEmailsValue);

        if (ccEmailsValue) {
            gmailUrl.searchParams.append('cc', ccEmailsValue);
        }
        if (bccEmailsValue) {
            gmailUrl.searchParams.append('bcc', bccEmailsValue);
        }
        if (subjectValue) {
            gmailUrl.searchParams.append('su', subjectValue);
        }
        if (bodyValue) {
            let plainBody;

            // Check if we have a plain text version available
            const selectedTemplate = document.querySelector('.template-option.selected');
            if (selectedTemplate && emailTemplates[selectedTemplate.dataset.template] && emailTemplates[selectedTemplate.dataset.template].plainTextBody && selectedTemplate.dataset.template !== 'none') {
                plainBody = emailTemplates[selectedTemplate.dataset.template].plainTextBody;
            } else {
                // Fallback to converting HTML to plain text
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = bodyValue;
                plainBody = tempDiv.textContent || tempDiv.innerText || '';

                // Enhanced plain text formatting
                plainBody = plainBody
                    .replace(/\s+/g, ' ')  // Replace multiple spaces with single space
                    .replace(/ ([.!?])/g, '$1')  // Fix spacing before punctuation
                    .replace(/([.!?])([A-Z])/g, '$1\n\n$2')  // Add line breaks after sentences
                    .replace(/([a-z])([A-Z])/g, '$1 $2')  // Add space between camelCase
                    .replace(/([a-z])(\d)/g, '$1 $2')  // Add space between letters and numbers
                    .replace(/(\d)([A-Z])/g, '$1 $2')  // Add space between numbers and letters
                    .replace(/\n\s*\n/g, '\n\n')  // Clean up multiple line breaks
                    .trim();
            }

            gmailUrl.searchParams.append('body', plainBody);
        }

        console.log('Gmail URL constructed:', gmailUrl.toString());

        if (confirm('This will open Gmail in a new tab with your email pre-filled.\n\n⚠️ Note: You will need to manually attach any required files.\n\nClick OK to continue.')) {
            console.log('User confirmed - proceeding with Gmail workflow');
            // First save a draft automatically
            saveDraftForGmail().then(() => {
                console.log('Draft saved - opening Gmail now');
                // Then open Gmail
                const gmailWindow = window.open(gmailUrl.toString(), '_blank');
                console.log('Gmail window opened:', gmailWindow);

                if (gmailWindow) {
                    setTimeout(() => {
                        alert('✅ Gmail opened!\n\n📌 Next Steps:\n1. Attach any required files in Gmail\n2. Review and send the email\n3. Come back here and click "Mark as Sent" button');
                    }, 500);
                } else {
                    alert('❌ Gmail window was blocked by popup blocker!\n\nPlease allow popups for this site and try again.');
                }
            }).catch(error => {
                console.error('Failed to save draft:', error);
                alert('❌ Failed to save draft. Please try again.');
            });
        } else {
            console.log('User cancelled Gmail opening');
        }
        });
    }

    // Save draft for Gmail workflow
    function saveDraftForGmail() {
        return new Promise((resolve, reject) => {
            const formData = new FormData(emailForm);
            formData.append('save_draft', '1');

            fetch('{{ route("tasks.store-email-preparation", $task) }}', {
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
                    console.log('Draft saved successfully for Gmail workflow');
                    resolve(data);
        } else {
                    reject(new Error(data.message || 'Failed to save draft'));
                }
            })
            .catch(error => {
                console.error('Error saving draft:', error);
                reject(error);
            });
        });
    }

    // Save draft and redirect function
    function saveDraftAndRedirect() {
            const formData = new FormData(emailForm);
        formData.append('save_draft', '1');

        fetch('{{ route("tasks.store-email-preparation", $task) }}', {
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
                // Show success message and redirect
                showSuccessMessage('Draft saved successfully! Task status updated to "On Client/Consultant Review".');
                setTimeout(() => {
                        window.location.href = '{{ route("tasks.show", $task) }}';
                }, 2000);
                } else {
                alert('❌ Failed to save draft: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
            alert('❌ Error saving draft. Please try again.');
        });
    }

    // Mark as sent functionality
    if (markAsSentBtn) {
        markAsSentBtn.addEventListener('click', function() {
        console.log('Mark as Sent button clicked!');
        if (confirm('Have you successfully sent the email via Gmail?\n\nThis will mark the task as "On Client/Consultant Review".')) {
            console.log('User confirmed - proceeding with mark as sent');
            markAsSentBtn.disabled = true;
            markAsSentBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            // First ensure we have an email preparation
            const formData = new FormData(emailForm);
            formData.append('save_draft', '1');

            // Save draft first if needed
            console.log('Saving draft before marking as sent...');
            fetch('{{ route("tasks.store-email-preparation", $task) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                console.log('Draft save response status:', response.status);
                return response.json();
            })
            .then(draftData => {
                console.log('Draft save response data:', draftData);
                if (draftData.success) {
                    console.log('Draft ensured for mark as sent - now calling mark as sent API');
                    // Now mark as sent
                    console.log('Calling mark as sent API:', '{{ route("tasks.mark-email-sent", $task) }}');
                    return fetch('{{ route("tasks.mark-email-sent", $task) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            sent_via: 'gmail_manual'
                        })
                    });
                    } else {
                    throw new Error(draftData.message || 'Failed to save draft');
                }
            })
            .then(response => {
                console.log('Mark as sent response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Mark as sent response data:', data);
                if (data.success) {
                    showSuccessMessage(data.message || 'Email marked as sent successfully! Task status updated to "On Client/Consultant Review".');
                    // Show continue button as backup (with null checks)
                    if (continueToNextStepBtn) continueToNextStepBtn.style.display = 'inline-flex';
                    if (directNavBtn) directNavBtn.style.display = 'inline-flex';
                    // Immediate redirect without delay
                    setTimeout(() => {
                        window.location.href = data.redirect_url || '{{ route("tasks.show", $task) }}';
                    }, 1000);
                } else {
                    let errorMessage = data.message || 'Failed to mark email as sent';
                    if (data.debug) {
                        console.error('Debug info:', data.debug);
                        errorMessage += '\n\nDebug info: ' + JSON.stringify(data.debug, null, 2);
                    }
                    alert('❌ ' + errorMessage);
                    markAsSentBtn.disabled = false;
                    markAsSentBtn.innerHTML = '<i class="bx bx-check-double me-2"></i>Mark as Sent (After Gmail)';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error marking email as sent. Please try again.');
                markAsSentBtn.disabled = false;
                markAsSentBtn.innerHTML = '<i class="bx bx-check-double me-2"></i>Mark as Sent (After Gmail)';
            });
        }
    });
    }

    // Direct send functionality - bypasses AJAX completely
    if (directSendBtn) {
        directSendBtn.addEventListener('click', function() {
        if (confirm('Send this email and automatically continue to the next step?\n\nThis will send the email via server and update the task status.')) {
            // Show loading state
            directSendBtn.disabled = true;
            directSendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            // Create a form submission that will redirect after success
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("tasks.store-email-preparation", $task) }}';

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);

            // Add form data
            const formData = new FormData(emailForm);
            formData.append('send_email', '1');

            // Add all form fields
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            // Submit the form
            document.body.appendChild(form);
            form.submit();
        }
        });
    }

    // Continue to next step functionality
    if (continueToNextStepBtn) {
        continueToNextStepBtn.addEventListener('click', function() {
        if (confirm('Mark this email as sent and update task status to "On Client/Consultant Review"?\n\nThis will progress the task to the next workflow step.')) {
            continueToNextStepBtn.disabled = true;
            continueToNextStepBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            fetch('{{ route("tasks.mark-email-sent", $task) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    sent_via: 'manual_continue'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message || 'Task status updated successfully! Moving to "On Client/Consultant Review".');
                    setTimeout(() => {
                        window.location.href = data.redirect_url || '{{ route("tasks.show", $task) }}';
                    }, 1000);
        } else {
                    alert('❌ ' + (data.message || 'Failed to update task status'));
                    continueToNextStepBtn.disabled = false;
                    continueToNextStepBtn.innerHTML = '<i class="bx bx-check-double me-2"></i>Continue to Next Step';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error updating task status. Please try again.');
                continueToNextStepBtn.disabled = false;
                continueToNextStepBtn.innerHTML = '<i class="bx bx-check-double me-2"></i>Continue to Next Step';
            });
        }
        });
    }

    // Success message function
    function showSuccessMessage(message) {
        // Create a success notification
        const notification = document.createElement('div');
        notification.className = 'alert alert-success position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);';
        notification.innerHTML = `
                <div class="d-flex align-items-center">
                <i class="bx bx-check-circle me-2" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Success!</strong><br>
                    <small>${message}</small>
                </div>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    // Handle form submission for server sending
    if (emailForm) {
        emailForm.addEventListener('submit', function(e) {
        const sendEmailBtn = document.querySelector('button[name="send_email"][value="1"]');
        if (sendEmailBtn && e.submitter === sendEmailBtn) {
            e.preventDefault();

            if (confirm('Send this email via server?\n\nThis will automatically update the task status to "On Client/Consultant Review".')) {
                // Show loading state
                sendEmailBtn.disabled = true;
                sendEmailBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

                // Submit the form via fetch with proper headers
                const formData = new FormData(emailForm);
                formData.append('send_email', '1');

                fetch(emailForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);

                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        // If not JSON, it might be a redirect response
                        console.log('Non-JSON response received, likely a redirect');
                        showSuccessMessage('Email sent successfully! Redirecting to task view...');
                        setTimeout(() => {
                            window.location.href = '{{ route("tasks.show", $task) }}';
                        }, 1000);
                        return;
                    }
                })
                .then(data => {
                    if (data && data.success) {
                        console.log('Response data:', data);
                        showSuccessMessage(data.message || 'Email sent successfully! Task status updated to "On Client/Consultant Review".');
                        // Show continue button as backup (with null checks)
                        if (continueToNextStepBtn) continueToNextStepBtn.style.display = 'inline-flex';
                        if (directNavBtn) directNavBtn.style.display = 'inline-flex';
                        // Immediate redirect without delay
                        setTimeout(() => {
                            window.location.href = data.redirect_url || '{{ route("tasks.show", $task) }}';
                        }, 1000);
                    } else if (data && !data.success) {
                        alert('❌ ' + (data.message || 'Failed to send email'));
                        sendEmailBtn.disabled = false;
                        sendEmailBtn.innerHTML = '<i class="bx bx-send me-2"></i>Send via Server';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback: show success message and redirect anyway
                    showSuccessMessage('Email processing completed. Redirecting to task view...');
                    if (continueToNextStepBtn) continueToNextStepBtn.style.display = 'inline-flex';
                    if (directNavBtn) directNavBtn.style.display = 'inline-flex';
                    setTimeout(() => {
                        window.location.href = '{{ route("tasks.show", $task) }}';
                    }, 2000);
                });
            }
        }
        });
    }

    // Initialize form state
    updateProgress();
});
</script>
@endsection
