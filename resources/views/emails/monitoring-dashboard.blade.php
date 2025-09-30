@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📧 Email Reply Monitoring Dashboard</h3>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="triggerMonitoring()">
                            <i class="fas fa-sync"></i> Check for Replies
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="total-sent-emails">{{ $stats['total_sent_emails'] ?? 0 }}</h3>
                                    <p>Total Sent Emails</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 id="total-replies">{{ $stats['total_replies'] ?? 0 }}</h3>
                                    <p>Total Replies</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-reply"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 id="reply-rate">{{ $stats['reply_rate'] ?? 0 }}%</h3>
                                    <p>Reply Rate</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 id="pending-replies">{{ $stats['pending_replies'] ?? 0 }}</h3>
                                    <p>Pending Replies</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Emails and Notifications -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Recent Sent Emails</h3>
                                </div>
                                <div class="card-body">
                                    @if($recentEmails->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th>Sent</th>
                                                        <th>Replies</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentEmails as $email)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('emails.show', $email->id) }}">
                                                                {{ Str::limit($email->subject, 30) }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $email->sent_at->format('M d, H:i') }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ $email->replies->count() > 0 ? 'success' : 'secondary' }}">
                                                                {{ $email->replies->count() }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($email->replied_at)
                                                                <span class="badge badge-success">Replied</span>
                                                            @else
                                                                <span class="badge badge-warning">Pending</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">No sent emails found.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Recent Reply Notifications</h3>
                                    <div class="card-tools">
                                        <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">
                                            Mark All Read
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($recentNotifications->count() > 0)
                                        <div class="list-group">
                                            @foreach($recentNotifications as $notification)
                                            <div class="list-group-item {{ $notification->is_read ? '' : 'bg-light' }}">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">{{ $notification->message }}</h6>
                                                    <small>{{ $notification->created_at->diffForHumans() }}</small>
                                                </div>
                                                @if($notification->email)
                                                    <p class="mb-1">
                                                        <small class="text-muted">
                                                            Subject: {{ $notification->email->subject }}
                                                        </small>
                                                    </p>
                                                @endif
                                                @if(!$notification->is_read)
                                                    <button class="btn btn-sm btn-outline-primary" onclick="markAsRead({{ $notification->id }})">
                                                        Mark as Read
                                                    </button>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">No reply notifications found.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Setup Instructions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">📋 Setup Instructions</h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h5>To enable email reply tracking, you need to:</h5>
                                        <ol>
                                            <li><strong>Create email account:</strong> Set up <code>designers@yourdomain.com</code> in cPanel</li>
                                            <li><strong>Configure webhook:</strong> Point email service to <code>{{ url('/email/webhook/incoming') }}</code></li>
                                            <li><strong>Set up cron job:</strong> Run <code>php artisan email:monitor-replies</code> every 5 minutes</li>
                                            <li><strong>Test the system:</strong> Send an email and reply to it</li>
                                        </ol>
                                        <p class="mb-0">
                                            <strong>Webhook URL:</strong>
                                            <code>{{ url('/email/webhook/incoming') }}</code>
                                            <button class="btn btn-sm btn-outline-primary ml-2" onclick="testWebhook()">Test Webhook</button>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function triggerMonitoring() {
    fetch('{{ route("email-monitoring.trigger") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Email monitoring completed successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error triggering email monitoring');
    });
}

function markAsRead(notificationId) {
    fetch(`{{ route("email-monitoring.notifications.mark-read", ":id") }}`.replace(':id', notificationId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
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
    fetch('{{ route("email-monitoring.notifications.mark-all-read") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
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

function testWebhook() {
    fetch('{{ route("email.webhook.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({test: 'webhook'})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Webhook test successful!');
        } else {
            alert('Webhook test failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Webhook test failed');
    });
}

// Auto-refresh stats every 30 seconds
setInterval(function() {
    fetch('{{ route("email-monitoring.stats") }}')
    .then(response => response.json())
    .then(data => {
        document.getElementById('total-sent-emails').textContent = data.total_sent_emails || 0;
        document.getElementById('total-replies').textContent = data.total_replies || 0;
        document.getElementById('reply-rate').textContent = (data.reply_rate || 0) + '%';
        document.getElementById('pending-replies').textContent = data.pending_replies || 0;
    })
    .catch(error => console.error('Error updating stats:', error));
}, 30000);
</script>
@endsection
