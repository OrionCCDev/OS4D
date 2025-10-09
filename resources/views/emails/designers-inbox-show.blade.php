@extends('layouts.app')

@section('title', 'Engineering Inbox Email')

@section('head')
<style>
/* Reset and base styles */
.email-details-page {
    width: 100%;
    overflow-x: hidden;
}

.email-details-page .card {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.email-details-page .card-body {
    padding: 20px;
}

/* Email content container - simplified */
.email-content-wrapper {
    width: 100%;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    overflow-x: auto;
}

.email-content-container {
    width: 100%;
    max-width: 100%;
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    line-height: 1.6;
    overflow-wrap: break-word;
    word-wrap: break-word;
}

/* Email styling */
.email-content-container img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
}

.email-content-container table {
    width: 100%;
    max-width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
    overflow-x: auto;
    display: block;
}

.email-content-container table td,
.email-content-container table th {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    text-align: left;
}

.email-content-container pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    max-width: 100%;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #e9ecef;
    overflow-x: auto;
}

/* Remove any fixed widths from email HTML */
.email-content-container * {
    max-width: 100%;
}

/* Avatar styles */
.avatar-sm {
    width: 40px;
    height: 40px;
    min-width: 40px;
    min-height: 40px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .email-content-wrapper {
        padding: 10px;
    }

    .email-content-container {
        padding: 15px;
    }

    .email-details-page .card-body {
        padding: 15px;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid email-details-page">
    <div class="row">
        <div class="col-12">
            <x-modern-breadcrumb
                title="Engineering Inbox Email"
                subtitle="View and manage email details from designers inbox"
                icon="bx-envelope-open"
                theme="emails"
                :breadcrumbs="[
                    ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx-home'],
                    ['title' => 'Inbox', 'url' => route('emails.all'), 'icon' => 'bx-inbox'],
                    ['title' => 'Details', 'url' => '#', 'icon' => 'bx-envelope']
                ]"
            />
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="bx bx-envelope-open me-2"></i>
                        Email Details
                        @if($email->status === 'read')
                            <span class="badge bg-success ms-2">Read</span>
                        @else
                            <span class="badge bg-warning ms-2">Unread</span>
                        @endif
                    </h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('emails.all') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i>Back to Inbox
                        </a>
                        @if($email->status === 'received')
                            <button class="btn btn-success btn-sm" onclick="markAsRead({{ $email->id }})">
                                <i class="bx bx-check me-1"></i>Mark as Read
                            </button>
                        @else
                            <button class="btn btn-warning btn-sm" onclick="markAsUnread({{ $email->id }})">
                                <i class="bx bx-envelope me-1"></i>Mark as Unread
                            </button>
                        @endif
                        <button class="btn btn-danger btn-sm" onclick="deleteEmail({{ $email->id }})">
                            <i class="bx bx-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Email Header -->
                    <div class="row mb-4">
                        <div class="col-lg-8 mb-3 mb-lg-0">
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <span class="text-white fw-bold fs-5">{{ substr($email->sender_name, 0, 1) }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="mb-2">{{ $email->subject }}</h4>
                                    <div class="text-muted small">
                                        <div class="mb-1">
                                            <strong>From:</strong> {{ $email->from_email }}
                                            <span class="badge bg-primary ms-2">Engineering Inbox</span>
                                        </div>
                                        <div class="mb-1">
                                            <strong>To:</strong> {{ $email->to_email }}
                                        </div>
                                        @if(!empty($email->cc_emails))
                                        <div class="mb-1">
                                            <strong>CC:</strong> {{ implode(', ', $email->cc_emails) }}
                                        </div>
                                        @endif
                                        <div class="mb-1">
                                            <strong>Received:</strong>
                                            @if($email->received_at)
                                                {{ $email->received_at->format('F d, Y \a\t H:i') }}
                                                <small class="text-muted ms-2">({{ $email->received_at->diffForHumans() }})</small>
                                            @else
                                                Unknown
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="text-lg-end">
                                @if($email->reply_to_email_id)
                                    <span class="badge bg-info fs-6 mb-2">
                                        <i class="bx bx-reply me-1"></i>Reply
                                    </span>
                                @else
                                    <span class="badge bg-primary fs-6 mb-2">
                                        <i class="bx bx-envelope me-1"></i>Received
                                    </span>
                                @endif

                                @if(!empty($email->attachments))
                                    <span class="badge bg-warning fs-6 mb-2">
                                        <i class="bx bx-paperclip me-1"></i>{{ count($email->attachments) }} Attachment(s)
                                    </span>
                                @endif

                                <div class="text-muted small mt-2">
                                    <div><strong>Source:</strong> engineering@orion-contracting.com</div>
                                    <div class="text-break"><strong>Message ID:</strong> {{ Str::limit($email->message_id, 30) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Body -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bx bx-file-text me-2"></i>Email Content
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            @php
                                // Get the email body
                                $emailBody = $email->body;

                                // First, try PHP's built-in quoted-printable decode
                                $decodedBody = quoted_printable_decode($emailBody);

                                // If that didn't work or we still have encoding artifacts, do manual decoding
                                if (strpos($decodedBody, '=3D') !== false || strpos($decodedBody, '=20') !== false || strpos($decodedBody, '=3C') !== false) {
                                    $replacements = [
                                        '=3D' => '=', '=20' => ' ', '=0A' => "\n", '=0D' => "\r",
                                        '=3C' => '<', '=3E' => '>', '=22' => '"', '=27' => "'",
                                        '=2C' => ',', '=2E' => '.', '=2F' => '/', '=3A' => ':',
                                        '=3B' => ';', '=40' => '@', '=5B' => '[', '=5D' => ']',
                                        '=5F' => '_', '=60' => '`', '=7B' => '{', '=7D' => '}',
                                        '=7E' => '~', '=09' => "\t", '=28' => '(', '=29' => ')',
                                        '=2B' => '+', '=2D' => '-', '=3F' => '?', '=21' => '!',
                                        '=23' => '#', '=24' => '$', '=25' => '%', '=26' => '&',
                                        '=2A' => '*',
                                    ];
                                    $decodedBody = str_replace(array_keys($replacements), array_values($replacements), $emailBody);
                                    $decodedBody = preg_replace('/=\r?\n/', '', $decodedBody);
                                    $decodedBody = preg_replace('/=\s*$/', '', $decodedBody);
                                }

                                // Use parsed body if available, otherwise use decoded body
                                $displayBody = ($parsedBody && strlen($parsedBody) > 10) ? $parsedBody : $decodedBody;

                                // Fix any character encoding issues
                                $displayBody = mb_convert_encoding($displayBody, 'UTF-8', 'UTF-8');
                            @endphp

                            @if($displayBody && strlen($displayBody) > 10)
                                <div class="email-content-wrapper">
                                    <div class="email-content-container">
                                        {!! $displayBody !!}
                                    </div>
                                </div>
                            @else
                                <div class="text-muted text-center py-5">
                                    <i class="bx bx-file-blank" style="font-size: 48px;"></i>
                                    <p class="mt-3">No content available</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Attachments Section -->
                    @if(!empty($email->attachments))
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bx bx-paperclip me-2"></i>
                                Attachments ({{ count($email->attachments) }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($email->attachments as $attachment)
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="bx bx-file text-muted fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1 text-break">
                                            <div class="fw-semibold">{{ $attachment['filename'] ?? 'Unknown File' }}</div>
                                            <small class="text-muted">
                                                {{ $attachment['mime_type'] ?? 'Unknown Type' }}
                                                @if(isset($attachment['size']))
                                                    • {{ number_format($attachment['size'] / 1024, 1) }} KB
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Replies Section -->
                    @if($email->replies && $email->replies->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bx bx-reply me-2"></i>
                                Replies ({{ $email->replies->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($email->replies as $reply)
                            <div class="reply-item border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-sm bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <span class="text-white fw-bold">{{ substr($reply->sender_name, 0, 1) }}</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                            <div>
                                                <strong>{{ $reply->from_email }}</strong>
                                                <small class="text-muted ms-2">
                                                    @if($reply->received_at)
                                                        {{ $reply->received_at->format('M d, Y H:i') }}
                                                    @else
                                                        Unknown date
                                                    @endif
                                                </small>
                                            </div>
                                            <span class="badge bg-info">Reply</span>
                                        </div>
                                        @php
                                            $replyContent = $reply->body;
                                            if (strpos($replyContent, '=3D') !== false || strpos($replyContent, '=20') !== false) {
                                                $replacements = [
                                                    '=3D' => '=', '=20' => ' ', '=0A' => "\n", '=0D' => "\r",
                                                    '=3C' => '<', '=3E' => '>', '=22' => '"', '=27' => "'",
                                                    '=2C' => ',', '=2E' => '.', '=2F' => '/', '=3A' => ':',
                                                    '=3B' => ';', '=40' => '@', '=5B' => '[', '=5D' => ']',
                                                    '=5F' => '_', '=60' => '`', '=7B' => '{', '=7D' => '}',
                                                    '=7E' => '~',
                                                ];
                                                $replyContent = str_replace(array_keys($replacements), array_values($replacements), $replyContent);
                                                $replyContent = preg_replace('/=\r?\n/', '', $replyContent);
                                            }
                                            $displayReplyContent = $parsedReplies[$reply->id] ?? $replyContent;
                                        @endphp
                                        <div class="email-content-wrapper">
                                            <div class="email-content-container">
                                                {!! $displayReplyContent !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function markAsRead(emailId) {
        fetch(`/emails/${emailId}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Email marked as read');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert('error', data.message || 'Error marking email as read');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred');
        });
    }

    function markAsUnread(emailId) {
        fetch(`/emails/${emailId}/mark-unread`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Email marked as unread');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert('error', data.message || 'Error marking email as unread');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred');
        });
    }

    function deleteEmail(emailId) {
        if (confirm('Are you sure you want to delete this email?')) {
            fetch(`/emails/${emailId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Email deleted successfully');
                    setTimeout(() => window.location.href = '{{ route("emails.all") }}', 1000);
                } else {
                    showAlert('error', data.message || 'Error deleting email');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred');
            });
        }
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bx bx-${type === 'success' ? 'check-circle' : 'error-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.querySelector('.card-body').insertAdjacentHTML('afterbegin', alertHtml);
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) alert.remove();
        }, 5000);
    }
</script>
@endsection
