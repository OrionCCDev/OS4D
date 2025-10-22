@extends('layouts.app')

@section('title', 'Email Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <x-modern-breadcrumb
                title="Email Management"
                subtitle="Manage and track all email communications"
                icon="bx bx-envelope"
                theme="emails"
                :breadcrumbs="[
                    ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                    ['title' => 'Emails', 'url' => '#', 'icon' => 'bx bx-envelope']
                ]"
            />
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-envelope me-2"></i>Received Emails
                    </h5>
                    <button class="btn btn-primary btn-sm" onclick="checkNewEmails()">
                        <i class="bx bx-refresh me-1"></i>Check New Emails
                    </button>
                </div>
                <div class="card-body">
                    @if($emails->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>From</th>
                                        <th>Subject</th>
                                        <th>Task</th>
                                        <th>Received</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emails as $email)
                                        <tr class="{{ $email->status === 'received' ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <span class="text-white fw-bold">{{ substr($email->sender_name, 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $email->sender_name }}</div>
                                                        <small class="text-muted">{{ $email->from_email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $email->subject }}</div>
                                                <small class="text-muted">{{ $email->preview }}</small>
                                            </td>
                                            <td>
                                                @if($email->task)
                                                    <a href="{{ route('tasks.show', $email->task) }}" class="badge bg-info">
                                                        {{ $email->task->title }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">No task linked</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $email->formatted_received_date }}</div>
                                                <small class="text-muted">{{ $email->received_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @switch($email->status)
                                                    @case('received')
                                                        <span class="badge bg-warning">Unread</span>
                                                        @break
                                                    @case('read')
                                                        <span class="badge bg-success">Read</span>
                                                        @break
                                                    @case('replied')
                                                        <span class="badge bg-info">Replied</span>
                                                        @break
                                                    @case('archived')
                                                        <span class="badge bg-secondary">Archived</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('emails.show', $email) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                    @if($email->status === 'received')
                                                        <button class="btn btn-sm btn-outline-success" onclick="markAsRead({{ $email->id }})">
                                                            <i class="bx bx-check"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $emails->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-envelope-open display-1 text-muted"></i>
                            <h5 class="mt-3">No emails received yet</h5>
                            <p class="text-muted">Emails will appear here once they are received and processed.</p>
                            <button class="btn btn-primary" onclick="checkNewEmails()">
                                <i class="bx bx-refresh me-1"></i>Check for New Emails
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkNewEmails() {
    const btn = event.target;
    const originalText = btn.innerHTML;

    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Checking...';
    btn.disabled = true;

    fetch('{{ route("emails.check-new") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.count > 0) {
                // Show success message
                showAlert('success', `Found ${data.count} new email(s)!`);
                // Reload page to show new emails
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('info', 'No new emails found.');
            }
        } else {
            showAlert('error', data.message || 'Error checking for emails.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while checking for emails.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

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
        if (data.status === 'success') {
            showAlert('success', 'Email marked as read.');
            // Reload page to update status
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert('error', 'Error marking email as read.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred.');
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' :
                      type === 'error' ? 'alert-danger' : 'alert-info';

    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="bx bx-${type === 'success' ? 'check-circle' : type === 'error' ? 'error-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    // Insert alert at the top of the page
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection
