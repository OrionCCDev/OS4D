@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <x-modern-breadcrumb
                title="Notifications"
                subtitle="Manage your notifications and alerts"
                icon="bx-bell"
                theme="notifications"
                :breadcrumbs="[
                    ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx-home'],
                    ['title' => 'Notifications', 'url' => '#', 'icon' => 'bx-bell']
                ]"
            />
        </div>
    </div>

    <!-- Notification Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-bell text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Total Unread</h6>
                            <h4 class="mb-0" id="total-unread-count">{{ $stats['unread'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-task text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Task Notifications</h6>
                            <h4 class="mb-0" id="task-unread-count">{{ $stats['task_unread'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-info rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-envelope text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Email Notifications</h6>
                            <h4 class="mb-0" id="email-unread-count">{{ $stats['email_unread'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Read</h6>
                            <h4 class="mb-0" id="read-count">{{ $stats['read'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" onclick="markAllAsRead()">
                            <i class="bx bx-check me-1"></i>Mark All as Read
                        </button>
                        <button class="btn btn-success" onclick="markAllAsRead('task')">
                            <i class="bx bx-task me-1"></i>Mark Task Notifications as Read
                        </button>
                        <button class="btn btn-info" onclick="markAllAsRead('email')">
                            <i class="bx bx-envelope me-1"></i>Mark Email Notifications as Read
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-filter me-1"></i>Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="filterNotifications('all')">All Notifications</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterNotifications('task')">Task Notifications</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterNotifications('email')">Email Notifications</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="filterNotifications('unread')">Unread Only</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-bell me-2"></i>All Notifications
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2" id="current-filter-badge">All</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshNotifications()">
                            <i class="bx bx-refresh"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="notifications-container">
                        <!-- Notifications will be loaded here -->
                    </div>

                    <div class="text-center" id="loading-spinner" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div class="text-center" id="no-notifications" style="display: none;">
                        <i class="bx bx-bell-off display-1 text-muted"></i>
                        <h5 class="mt-3">No notifications found</h5>
                        <p class="text-muted">You're all caught up!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentFilter = 'all';
let notifications = [];

// Load notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    // Refresh notifications every 30 seconds
    setInterval(refreshNotifications, 30000);
});

function loadNotifications(filter = 'all') {
    currentFilter = filter;
    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('notifications-container').innerHTML = '';
    document.getElementById('no-notifications').style.display = 'none';

    let url = '/notifications';
    if (filter !== 'all') {
        url += `?category=${filter}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notifications = data.notifications;
                displayNotifications(data.notifications);
                updateStats(data.stats);
                updateFilterBadge(filter);
            } else {
                showAlert('error', 'Failed to load notifications');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred while loading notifications');
        })
        .finally(() => {
            document.getElementById('loading-spinner').style.display = 'none';
        });
}

function displayNotifications(notifications) {
    const container = document.getElementById('notifications-container');

    if (notifications.length === 0) {
        document.getElementById('no-notifications').style.display = 'block';
        return;
    }

    container.innerHTML = notifications.map(notification => `
        <div class="notification-item border-bottom py-3 ${notification.is_read ? '' : 'bg-light'}">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar-sm bg-${notification.color} rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bx ${notification.icon} text-white"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">
                                <span class="badge bg-${notification.badge_color} me-2">${notification.category}</span>
                                ${notification.title}
                            </h6>
                            <p class="mb-1 text-muted">${notification.message}</p>
                            <small class="text-muted">${notification.time_ago}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                ${!notification.is_read ? `
                                    <li><a class="dropdown-item" href="#" onclick="markAsRead(${notification.id})">
                                        <i class="bx bx-check me-2"></i>Mark as Read
                                    </a></li>
                                ` : ''}
                                <li><a class="dropdown-item" href="#" onclick="archiveNotification(${notification.id})">
                                    <i class="bx bx-archive me-2"></i>Archive
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteNotification(${notification.id})">
                                    <i class="bx bx-trash me-2"></i>Delete
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function updateStats(stats) {
    document.getElementById('total-unread-count').textContent = stats.unread || 0;
    document.getElementById('task-unread-count').textContent = stats.task_unread || 0;
    document.getElementById('email-unread-count').textContent = stats.email_unread || 0;
    document.getElementById('read-count').textContent = stats.read || 0;
}

function updateFilterBadge(filter) {
    const badge = document.getElementById('current-filter-badge');
    const badgeText = {
        'all': 'All',
        'task': 'Tasks',
        'email': 'Emails',
        'unread': 'Unread'
    };
    badge.textContent = badgeText[filter] || 'All';
}

function filterNotifications(filter) {
    loadNotifications(filter);
}

function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Notification marked as read');
            refreshNotifications();
        } else {
            showAlert('error', data.message || 'Failed to mark notification as read');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred');
    });
}

function markAllAsRead(category = null) {
    const url = category ? `/notifications/mark-all-read?category=${category}` : '/notifications/mark-all-read';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            refreshNotifications();
        } else {
            showAlert('error', data.message || 'Failed to mark notifications as read');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred');
    });
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Notification deleted');
                refreshNotifications();
            } else {
                showAlert('error', data.message || 'Failed to delete notification');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred');
        });
    }
}

function archiveNotification(notificationId) {
    fetch(`/notifications/${notificationId}/archive`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Notification archived');
            refreshNotifications();
        } else {
            showAlert('error', data.message || 'Failed to archive notification');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred');
    });
}

function refreshNotifications() {
    loadNotifications(currentFilter);
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' :
                      type === 'error' ? 'alert-danger' :
                      type === 'warning' ? 'alert-warning' : 'alert-info';

    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="bx bx-${type === 'success' ? 'check-circle' : type === 'error' ? 'error-circle' : type === 'warning' ? 'error' : 'info-circle'} me-2"></i>
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
