@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Email Notifications</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" onclick="markAllAsRead()">
                            <i class="fas fa-check"></i> Mark All as Read
                        </button>
                        <button type="button" class="btn btn-sm btn-info" onclick="refreshNotifications()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Message</th>
                                        <th>Email Subject</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                        <tr class="{{ $notification->is_read ? '' : 'table-warning' }}">
                                            <td>
                                                <span class="badge badge-{{ $notification->notification_type === 'reply_received' ? 'success' : ($notification->notification_type === 'email_opened' ? 'info' : 'primary') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $notification->notification_type)) }}
                                                </span>
                                            </td>
                                            <td>{{ $notification->message }}</td>
                                            <td>
                                                @if($notification->email)
                                                    <a href="{{ route('emails.show', $notification->email->id) }}" class="text-decoration-none">
                                                        {{ $notification->email->subject }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $notification->created_at->format('M d, Y g:i A') }}</td>
                                            <td>
                                                @if($notification->is_read)
                                                    <span class="badge badge-success">Read</span>
                                                @else
                                                    <span class="badge badge-warning">Unread</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$notification->is_read)
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="markAsRead({{ $notification->id }})">
                                                        <i class="fas fa-check"></i> Mark Read
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Email Notifications</h4>
                            <p class="text-muted">You don't have any email notifications yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/email-notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error marking notification as read');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error marking notification as read');
    });
}

function markAllAsRead() {
    if (confirm('Are you sure you want to mark all notifications as read?')) {
        fetch('/email-notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error marking all notifications as read');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error marking all notifications as read');
        });
    }
}

function refreshNotifications() {
    location.reload();
}
</script>
@endsection
