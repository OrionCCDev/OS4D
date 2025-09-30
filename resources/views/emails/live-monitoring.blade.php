@extends('layouts.header')

@section('title', 'Live Email Monitoring')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-envelope me-2"></i>
                        Live Email Monitoring - designers@orion-contracting.com
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="triggerMonitoring()">
                            <i class="bx bx-refresh me-1"></i>Refresh Now
                        </button>
                        <button class="btn btn-success btn-sm" onclick="createTestNotifications()">
                            <i class="bx bx-test-tube me-1"></i>Test Notifications
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Live Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="total-emails">{{ $stats['total_emails'] ?? 0 }}</h4>
                                    <small>Total Emails</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="replies-today">{{ $stats['replies_today'] ?? 0 }}</h4>
                                    <small>Replies Today</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="new-emails-today">{{ $stats['new_emails_today'] ?? 0 }}</h4>
                                    <small>New Emails Today</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="unread-notifications">{{ $unreadCount ?? 0 }}</h4>
                                    <small>Unread Notifications</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Status -->
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="bx bx-info-circle me-2"></i>
                        <div>
                            <strong>Live Monitoring Active</strong>
                            <br>
                            <small>Last updated: <span id="last-update">{{ $stats['last_monitoring'] ?? 'Never' }}</span></small>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" id="monitoringTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                                <i class="bx bx-bell me-1"></i>My Notifications ({{ $unreadCount ?? 0 }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="recent-emails-tab" data-bs-toggle="tab" data-bs-target="#recent-emails" type="button" role="tab">
                                <i class="bx bx-envelope me-1"></i>Recent Emails
                            </button>
                        </li>
                        @if(Auth::user()->isManager())
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="all-emails-tab" data-bs-toggle="tab" data-bs-target="#all-emails" type="button" role="tab">
                                <i class="bx bx-list-ul me-1"></i>All Emails (Manager)
                            </button>
                        </li>
                        @endif
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="monitoringTabsContent">
                        <!-- Notifications Tab -->
                        <div class="tab-pane fade show active" id="notifications" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Your Email Notifications</h6>
                                <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                                    <i class="bx bx-check me-1"></i>Mark All Read
                                </button>
                            </div>
                            <div id="notifications-list">
                                @if($userNotifications->count() > 0)
                                    @foreach($userNotifications as $notification)
                                    <div class="notification-item p-3 border-bottom {{ $notification->is_read ? '' : 'bg-light' }}"
                                         onclick="markAsRead({{ $notification->id }})">
                                        <div class="d-flex align-items-start">
                                            <div class="notification-icon me-3">
                                                @if($notification->notification_type === 'reply_received')
                                                    <i class="bx bx-reply text-success fs-4"></i>
                                                @else
                                                    <i class="bx bx-envelope text-primary fs-4"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 {{ $notification->is_read ? 'text-muted' : 'fw-semibold' }}">
                                                    {{ $notification->message }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </small>
                                                @if($notification->email)
                                                <div class="mt-1">
                                                    <small class="text-info">
                                                        <i class="bx bx-envelope me-1"></i>
                                                        {{ $notification->email->subject }}
                                                    </small>
                                                </div>
                                                @endif
                                            </div>
                                            @if(!$notification->is_read)
                                            <div class="unread-indicator">
                                                <span class="badge bg-warning">New</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bx bx-bell-off fs-1 mb-3"></i>
                                    <h6>No notifications yet</h6>
                                    <p class="mb-0">You'll see email notifications here when replies are received.</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Recent Emails Tab -->
                        <div class="tab-pane fade" id="recent-emails" role="tabpanel">
                            <h6 class="mb-3">Recent Emails Received</h6>
                            <div id="recent-emails-list">
                                @if($recentEmails->count() > 0)
                                    @foreach($recentEmails as $email)
                                    <div class="email-item p-3 border-bottom">
                                        <div class="d-flex align-items-start">
                                            <div class="email-icon me-3">
                                                @if($email->reply_to_email_id)
                                                    <i class="bx bx-reply text-success fs-4"></i>
                                                @else
                                                    <i class="bx bx-envelope text-primary fs-4"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $email->subject }}</h6>
                                                <p class="text-muted small mb-1">
                                                    From: {{ $email->from_email }} |
                                                    To: {{ $email->to_email }}
                                                </p>
                                                <small class="text-muted">
                                                    {{ $email->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <div class="email-actions">
                                                <button class="btn btn-outline-primary btn-sm" onclick="viewEmail({{ $email->id }})">
                                                    <i class="bx bx-show me-1"></i>View
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bx bx-envelope-open fs-1 mb-3"></i>
                                    <h6>No emails yet</h6>
                                    <p class="mb-0">Recent emails will appear here.</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if(Auth::user()->isManager())
                        <!-- All Emails Tab (Manager Only) -->
                        <div class="tab-pane fade" id="all-emails" role="tabpanel">
                            <h6 class="mb-3">All Emails (Manager View)</h6>
                            <div id="all-emails-list">
                                <div class="text-center py-4">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading all emails...</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 30 seconds
setInterval(function() {
    refreshStats();
    refreshNotifications();
}, 30000);

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    refreshStats();
    refreshNotifications();

    @if(Auth::user()->isManager())
    loadAllEmails();
    @endif
});

function refreshStats() {
    fetch('{{ route("live-monitoring.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-emails').textContent = data.stats.total_emails;
                document.getElementById('replies-today').textContent = data.stats.replies_today;
                document.getElementById('new-emails-today').textContent = data.stats.new_emails_today;
                document.getElementById('last-update').textContent = data.timestamp;
            }
        })
        .catch(error => console.error('Error refreshing stats:', error));
}

function refreshNotifications() {
    fetch('{{ route("live-monitoring.notifications") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('unread-notifications').textContent = data.count;
                updateNotificationsList(data.data);
            }
        })
        .catch(error => console.error('Error refreshing notifications:', error));
}

function updateNotificationsList(notifications) {
    const container = document.getElementById('notifications-list');

    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="bx bx-bell-off fs-1 mb-3"></i>
                <h6>No notifications yet</h6>
                <p class="mb-0">You'll see email notifications here when replies are received.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = notifications.map(n => `
        <div class="notification-item p-3 border-bottom ${n.is_read ? '' : 'bg-light'}"
             onclick="markAsRead(${n.id})">
            <div class="d-flex align-items-start">
                <div class="notification-icon me-3">
                    ${n.notification_type === 'reply_received' ?
                        '<i class="bx bx-reply text-success fs-4"></i>' :
                        '<i class="bx bx-envelope text-primary fs-4"></i>'
                    }
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 ${n.is_read ? 'text-muted' : 'fw-semibold'}">
                        ${n.message}
                    </h6>
                    <small class="text-muted">
                        ${new Date(n.created_at).toLocaleString()}
                    </small>
                    ${n.email ? `
                    <div class="mt-1">
                        <small class="text-info">
                            <i class="bx bx-envelope me-1"></i>
                            ${n.email.subject}
                        </small>
                    </div>
                    ` : ''}
                </div>
                ${!n.is_read ? `
                <div class="unread-indicator">
                    <span class="badge bg-warning">New</span>
                </div>
                ` : ''}
            </div>
        </div>
    `).join('');
}

function triggerMonitoring() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Monitoring...';
    btn.disabled = true;

    fetch('{{ route("live-monitoring.trigger") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Live monitoring completed successfully!');
            refreshStats();
            refreshNotifications();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error during monitoring');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function createTestNotifications() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Creating...';
    btn.disabled = true;

    fetch('{{ route("live-monitoring.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Test notifications created successfully!');
            refreshStats();
            refreshNotifications();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error creating test notifications');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function markAsRead(notificationId) {
    fetch(`{{ route("live-monitoring.mark-read", ":id") }}`.replace(':id', notificationId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshNotifications();
        }
    })
    .catch(error => console.error('Error marking as read:', error));
}

function markAllAsRead() {
    fetch('{{ route("live-monitoring.mark-all-read") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'All notifications marked as read');
            refreshNotifications();
        }
    })
    .catch(error => console.error('Error marking all as read:', error));
}

@if(Auth::user()->isManager())
function loadAllEmails() {
    fetch('{{ route("live-monitoring.all-emails") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAllEmailsList(data.emails.data);
            }
        })
        .catch(error => console.error('Error loading all emails:', error));
}

function updateAllEmailsList(emails) {
    const container = document.getElementById('all-emails-list');

    if (emails.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="bx bx-envelope-open fs-1 mb-3"></i>
                <h6>No emails found</h6>
            </div>
        `;
        return;
    }

    container.innerHTML = emails.map(email => `
        <div class="email-item p-3 border-bottom">
            <div class="d-flex align-items-start">
                <div class="email-icon me-3">
                    ${email.reply_to_email_id ?
                        '<i class="bx bx-reply text-success fs-4"></i>' :
                        '<i class="bx bx-envelope text-primary fs-4"></i>'
                    }
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${email.subject}</h6>
                    <p class="text-muted small mb-1">
                        From: ${email.from_email} |
                        To: ${email.to_email}
                        ${email.user ? ` | Sent by: ${email.user.name}` : ''}
                    </p>
                    <small class="text-muted">
                        ${new Date(email.created_at).toLocaleString()}
                    </small>
                </div>
                <div class="email-actions">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewEmail(${email.id})">
                        <i class="bx bx-show me-1"></i>View
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}
@endif

function viewEmail(emailId) {
    // Open email details in modal or new page
    window.open(`/emails/${emailId}`, '_blank');
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
.notification-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.email-item {
    transition: background-color 0.2s;
}

.email-item:hover {
    background-color: #f8f9fa;
}

.unread-indicator {
    flex-shrink: 0;
}

.notification-icon, .email-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f8f9fa;
}
</style>
@endsection
