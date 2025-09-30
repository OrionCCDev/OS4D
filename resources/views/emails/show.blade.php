@extends('layouts.header')

@section('title', 'Email Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-envelope me-2"></i>
                        Email Details
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="window.history.back()">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </button>
                        <button class="btn btn-success btn-sm" onclick="markAsRead({{ $email->id }})">
                            <i class="bx bx-check me-1"></i>Mark as Read
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Email Header -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-lg me-3">
                                    <span class="avatar-initial rounded-circle bg-primary fs-4">
                                        {{ strtoupper(substr($email->from_email, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="mb-1">{{ $email->subject }}</h4>
                                    <div class="text-muted">
                                        <div class="mb-1">
                                            <strong>From:</strong> {{ $email->from_email }}
                                            @if(str_contains($email->from_email, 'designers@orion-contracting.com'))
                                                <span class="badge bg-success ms-2">Designers</span>
                                            @endif
                                        </div>
                                        <div class="mb-1">
                                            <strong>To:</strong> {{ $email->to_email }}
                                        </div>
                                        @if($email->cc)
                                        <div class="mb-1">
                                            <strong>CC:</strong> {{ $email->cc }}
                                        </div>
                                        @endif
                                        <div class="mb-1">
                                            <strong>Date:</strong> {{ $email->created_at->format('F d, Y \a\t H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                @if($email->reply_to_email_id)
                                    <span class="badge bg-success fs-6 mb-2">
                                        <i class="bx bx-reply me-1"></i>Reply
                                    </span>
                                @elseif($email->email_type === 'sent')
                                    <span class="badge bg-primary fs-6 mb-2">
                                        <i class="bx bx-send me-1"></i>Sent
                                    </span>
                                @else
                                    <span class="badge bg-info fs-6 mb-2">
                                        <i class="bx bx-envelope me-1"></i>Received
                                    </span>
                                @endif

                                <div class="text-muted small">
                                    @if($email->user)
                                        <div><strong>Sent by:</strong> {{ $email->user->name }}</div>
                                        <div>{{ $email->user->email }}</div>
                                    @endif

                                    @if($email->task)
                                        <div class="mt-2">
                                            <a href="/tasks/{{ $email->task->id }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-task me-1"></i>View Task
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Body -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Email Content</h6>
                        </div>
                        <div class="card-body">
                            @if($email->body)
                                <div class="email-content">
                                    {!! $email->body !!}
                                </div>
                            @else
                                <div class="text-muted text-center py-4">
                                    <i class="bx bx-file-blank fs-1 mb-3"></i>
                                    <p>No content available</p>
                                </div>
                            @endif
                        </div>
                    </div>

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
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded-circle bg-secondary">
                                            {{ strtoupper(substr($reply->from_email, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong>{{ $reply->from_email }}</strong>
                                                <small class="text-muted ms-2">{{ $reply->created_at->format('M d, Y H:i') }}</small>
                                            </div>
                                            <span class="badge bg-success">Reply</span>
                                        </div>
                                        <div class="reply-content">
                                            {!! $reply->body !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Notifications Section -->
                    @if($email->notifications && $email->notifications->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bx bx-bell me-2"></i>
                                Notifications ({{ $email->notifications->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($email->notifications as $notification)
                            <div class="notification-item d-flex align-items-start mb-3">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle {{ $notification->is_read ? 'bg-secondary' : 'bg-warning' }}">
                                        <i class="bx bx-bell"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <strong>{{ $notification->user->name ?? 'Unknown User' }}</strong>
                                            <small class="text-muted ms-2">{{ $notification->created_at->format('M d, Y H:i') }}</small>
                                        </div>
                                        @if(!$notification->is_read)
                                        <span class="badge bg-warning">Unread</span>
                                        @endif
                                    </div>
                                    <div class="notification-message">
                                        {{ $notification->message }}
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
    fetch(`{{ route("email-tracker.mark-read", ":id") }}`.replace(':id', emailId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Email marked as read');
            // Update UI to show as read
            location.reload();
        } else {
            showAlert('error', data.message || 'Error marking email as read');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error marking email as read');
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
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
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    background-color: #f8f9fa;
}

.reply-content {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 0.75rem;
    background-color: #f8f9fa;
}

.notification-item {
    border-left: 3px solid #e5e7eb;
    padding-left: 1rem;
}

.avatar {
    width: 40px;
    height: 40px;
}

.avatar-sm {
    width: 32px;
    height: 32px;
}

.avatar-lg {
    width: 48px;
    height: 48px;
}
</style>
@endsection
