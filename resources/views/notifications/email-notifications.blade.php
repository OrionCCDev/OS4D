@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i> Email Notifications
                        <span class="badge badge-primary ml-2" id="unread-count">{{ $unreadCount }}</span>
                    </h3>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshNotifications()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Tabs -->
                    <ul class="nav nav-tabs mb-3" id="notificationTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab">
                                All Notifications
                                <span class="badge badge-secondary ml-1">{{ $notifications->total() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="unread-tab" data-toggle="tab" href="#unread" role="tab">
                                Unread
                                <span class="badge badge-danger ml-1" id="unread-badge">{{ $unreadCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="replies-tab" data-toggle="tab" href="#replies" role="tab">
                                Email Replies
                                <span class="badge badge-info ml-1">{{ $notifications->where('notification_type', 'reply_received')->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="notificationTabsContent">
                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                            @include('notifications.partials.notification-list', ['notifications' => $notifications])
                        </div>
                        <div class="tab-pane fade" id="unread" role="tabpanel">
                            <div id="unread-notifications">
                                <!-- Unread notifications will be loaded here -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="replies" role="tabpanel">
                            <div id="reply-notifications">
                                <!-- Reply notifications will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($notifications->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                <!-- Notification details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewEmailBtn" style="display: none;">View Email</button>
                <button type="button" class="btn btn-success" id="viewTaskBtn" style="display: none;">View Task</button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentNotifications = @json($notifications->items());
let unreadCount = {{ $unreadCount }};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set up tab switching
    document.querySelectorAll('#notificationTabs a').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('href').substring(1);

            if (target === 'unread') {
                loadUnreadNotifications();
            } else if (target === 'replies') {
                loadReplyNotifications();
            }
        });
    });

    // Auto-refresh every 30 seconds
    setInterval(refreshNotifications, 30000);
});

function loadUnreadNotifications() {
    fetch('{{ route("email-monitoring.notifications") }}?unread=1')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('unread-notifications');
        container.innerHTML = renderNotificationList(data.data);
    })
    .catch(error => console.error('Error loading unread notifications:', error));
}

function loadReplyNotifications() {
    fetch('{{ route("email-monitoring.notifications") }}?type=reply_received')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('reply-notifications');
        container.innerHTML = renderNotificationList(data.data);
    })
    .catch(error => console.error('Error loading reply notifications:', error));
}

function renderNotificationList(notifications) {
    if (!notifications || notifications.length === 0) {
        return '<div class="text-center text-muted py-4"><i class="fas fa-bell-slash fa-3x mb-3"></i><p>No notifications found</p></div>';
    }

    return notifications.map(notification => `
        <div class="notification-item ${notification.is_read ? '' : 'unread'}" data-id="${notification.id}">
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <div class="notification-icon">
                        ${getNotificationIcon(notification.notification_type)}
                    </div>
                </div>
                <div class="flex-grow-1 ml-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${notification.message}</h6>
                            <p class="mb-1 text-muted small">${formatDate(notification.created_at)}</p>
                            ${notification.email ? `<p class="mb-0 small text-info">Email: ${notification.email.subject}</p>` : ''}
                        </div>
                        <div class="notification-actions">
                            ${!notification.is_read ? `<button class="btn btn-sm btn-outline-primary" onclick="markAsRead(${notification.id})">Mark Read</button>` : ''}
                            <button class="btn btn-sm btn-outline-info" onclick="viewNotification(${notification.id})">View</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function getNotificationIcon(type) {
    const icons = {
        'reply_received': '<i class="fas fa-reply text-success"></i>',
        'email_received': '<i class="fas fa-envelope text-primary"></i>',
        'email_opened': '<i class="fas fa-eye text-info"></i>',
        'default': '<i class="fas fa-bell text-warning"></i>'
    };
    return icons[type] || icons.default;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return date.toLocaleDateString();
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
            // Update UI
            const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('unread');
                const markReadBtn = notificationElement.querySelector('button[onclick*="markAsRead"]');
                if (markReadBtn) markReadBtn.remove();
            }

            // Update counters
            unreadCount--;
            updateUnreadCounters();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
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
            // Update UI
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                const markReadBtn = item.querySelector('button[onclick*="markAsRead"]');
                if (markReadBtn) markReadBtn.remove();
            });

            // Update counters
            unreadCount = 0;
            updateUnreadCounters();
        }
    })
    .catch(error => console.error('Error marking all notifications as read:', error));
}

function viewNotification(notificationId) {
    const notification = currentNotifications.find(n => n.id === notificationId);
    if (!notification) return;

    const modalBody = document.getElementById('notificationModalBody');
    modalBody.innerHTML = `
        <div class="notification-detail">
            <h5>${notification.message}</h5>
            <p class="text-muted">${formatDate(notification.created_at)}</p>
            ${notification.email ? `
                <div class="mt-3">
                    <h6>Email Details:</h6>
                    <p><strong>Subject:</strong> ${notification.email.subject}</p>
                    <p><strong>From:</strong> ${notification.email.from_email}</p>
                    <p><strong>To:</strong> ${notification.email.to_email}</p>
                </div>
            ` : ''}
        </div>
    `;

    // Show/hide action buttons
    const viewEmailBtn = document.getElementById('viewEmailBtn');
    const viewTaskBtn = document.getElementById('viewTaskBtn');

    viewEmailBtn.style.display = notification.email ? 'inline-block' : 'none';
    viewTaskBtn.style.display = notification.email && notification.email.task_id ? 'inline-block' : 'none';

    if (notification.email) {
        viewEmailBtn.onclick = () => window.open(`{{ route("emails.show", ":id") }}`.replace(':id', notification.email.id), '_blank');
    }

    if (notification.email && notification.email.task_id) {
        viewTaskBtn.onclick = () => window.open(`/tasks/${notification.email.task_id}`, '_blank');
    }

    $('#notificationModal').modal('show');
}

function refreshNotifications() {
    fetch('{{ route("email-monitoring.unread-count") }}')
    .then(response => response.json())
    .then(data => {
        unreadCount = data.count;
        updateUnreadCounters();
    })
    .catch(error => console.error('Error refreshing notifications:', error));
}

function updateUnreadCounters() {
    document.getElementById('unread-count').textContent = unreadCount;
    document.getElementById('unread-badge').textContent = unreadCount;

    // Update navigation bell if it exists
    const navBell = document.querySelector('.nav-bell-count');
    if (navBell) {
        navBell.textContent = unreadCount;
        navBell.style.display = unreadCount > 0 ? 'inline' : 'none';
    }
}
</script>

<style>
.notification-item {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.notification-actions {
    display: flex;
    gap: 5px;
}

.notification-actions .btn {
    padding: 2px 8px;
    font-size: 12px;
}

.tab-content {
    min-height: 400px;
}

.badge {
    font-size: 0.75em;
}

@media (max-width: 768px) {
    .notification-item {
        padding: 10px;
    }

    .notification-actions {
        flex-direction: column;
        margin-top: 10px;
    }

    .notification-actions .btn {
        width: 100%;
        margin-bottom: 5px;
    }
}
</style>
@endsection
