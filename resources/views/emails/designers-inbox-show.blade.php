@extends('layouts.app')

@section('title', 'Designers Inbox Email')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Designers Inbox Email</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('emails.all') }}">Designers Inbox</a></li>
                        <li class="breadcrumb-item active">Email Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-envelope-open me-2"></i>
                        Email Details
                        @if($email->status === 'read')
                            <span class="badge bg-success ms-2">Read</span>
                        @else
                            <span class="badge bg-warning ms-2">Unread</span>
                        @endif
                    </h5>
                    <div class="d-flex gap-2">
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
                        <div class="col-md-8">
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                    <span class="text-white fw-bold fs-5">{{ substr($email->sender_name, 0, 1) }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="mb-1">{{ $email->subject }}</h4>
                                    <div class="text-muted">
                                        <div class="mb-1">
                                            <strong>From:</strong> {{ $email->from_email }}
                                            <span class="badge bg-primary ms-2">Designers Inbox</span>
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
                                            <strong>Received:</strong> {{ $email->received_at->format('F d, Y \a\t H:i') }}
                                            <small class="text-muted ms-2">({{ $email->received_at->diffForHumans() }})</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
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
                                    <div><strong>Source:</strong> designers@orion-contracting.com</div>
                                    <div><strong>Message ID:</strong> {{ $email->message_id }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Body -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bx bx-file-text me-2"></i>Email Content
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($parsedBody)
                                <div class="email-content">
                                    {!! $parsedBody !!}
                                </div>
                            @else
                                <div class="text-muted text-center py-4">
                                    <i class="bx bx-file-blank fs-1 mb-3"></i>
                                    <p>No content available</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Attachments Section -->
                    @if(!empty($email->attachments))
                    <div class="card mb-4">
                        <div class="card-header">
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
                                            <i class="bx bx-file text-muted"></i>
                                        </div>
                                        <div class="flex-grow-1">
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
                        <div class="card-header">
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
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong>{{ $reply->from_email }}</strong>
                                                <small class="text-muted ms-2">{{ $reply->received_at->format('M d, Y H:i') }}</small>
                                            </div>
                                            <span class="badge bg-info">Reply</span>
                                        </div>
                                        <div class="reply-content">
                                            {!! parseEmailBody($reply->body) !!}
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
            setTimeout(() => {
                window.location.reload();
            }, 1000);
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
            setTimeout(() => {
                window.location.reload();
            }, 1000);
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
                setTimeout(() => {
                    window.location.href = '{{ route("emails.all") }}';
                }, 1000);
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

    // Insert at top of card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertAdjacentHTML('afterbegin', alertHtml);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = cardBody.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>

<style>
.email-content {
    max-height: 600px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    background-color: #f8f9fa;
    font-family: Arial, sans-serif;
    line-height: 1.6;
}

.email-content img {
    max-width: 100%;
    height: auto;
}

.email-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.email-content table th,
.email-content table td {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    text-align: left;
}

.email-content table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.reply-content {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 1rem;
    background-color: #f8f9fa;
    font-family: Arial, sans-serif;
    line-height: 1.5;
}

.avatar-sm {
    width: 32px;
    height: 32px;
}

/* Clean up email body formatting */
.email-content * {
    max-width: 100% !important;
}

.email-content pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
@endsection
