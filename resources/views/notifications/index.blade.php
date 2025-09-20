@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Notifications</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="markAllAsRead()">
                <i class="bx bx-check-all me-1"></i>Mark All as Read
            </button>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to Tasks
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">All Notifications</h5>
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <div class="list-group-item {{ $notification->read ? '' : 'bg-light' }}" id="notification-{{ $notification->id }}">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            @php
                                                $iconMap = [
                                                    'task_assigned' => 'bx-user-plus',
                                                    'task_status_changed' => 'bx-edit',
                                                    'task_completed' => 'bx-check-circle',
                                                    'task_rejected' => 'bx-x-circle',
                                                ];
                                                $colorMap = [
                                                    'task_assigned' => 'text-info',
                                                    'task_status_changed' => 'text-warning',
                                                    'task_completed' => 'text-success',
                                                    'task_rejected' => 'text-danger',
                                                ];
                                            @endphp
                                            <div class="avatar avatar-sm">
                                                <span class="avatar-initial rounded-circle {{ $colorMap[$notification->type] ?? 'bg-label-primary' }}">
                                                    <i class="bx {{ $iconMap[$notification->type] ?? 'bx-bell' }}"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1 {{ $notification->read ? 'text-muted' : 'fw-semibold' }}">
                                                        {{ $notification->title }}
                                                    </h6>
                                                    <p class="mb-1 {{ $notification->read ? 'text-muted' : '' }}">
                                                        {{ $notification->message }}
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="bx bx-time me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    @if(!$notification->read)
                                                        <button class="btn btn-sm btn-outline-primary" onclick="markAsRead({{ $notification->id }})" title="Mark as Read">
                                                            <i class="bx bx-check"></i>
                                                        </button>
                                                    @endif
                                                    @if($notification->data && isset($notification->data['task_id']))
                                                        <a href="{{ route('tasks.show', $notification->data['task_id']) }}" class="btn btn-sm btn-outline-secondary" title="View Task">
                                                            <i class="bx bxs-show"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bx bx-bell-off" style="font-size: 4rem; color: #d1d5db;"></i>
                            </div>
                            <h5 class="text-muted mb-2">No notifications</h5>
                            <p class="text-muted">You're all caught up! No new notifications at the moment.</p>
                        </div>
                    @endif
                </div>
                @if($notifications->hasPages())
                    <div class="card-footer">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Notification Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Notification Stats</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="mb-1 text-primary">{{ $notifications->where('read', false)->count() }}</h4>
                                <small class="text-muted">Unread</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="mb-1 text-success">{{ $notifications->where('read', true)->count() }}</h4>
                                <small class="text-muted">Read</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="markAllAsRead()">
                            <i class="bx bx-check-all me-1"></i>Mark All as Read
                        </button>
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-task me-1"></i>View All Tasks
                        </a>
                        @if(Auth::user()->isManager())
                        <a href="{{ route('tasks.create') }}" class="btn btn-outline-success">
                            <i class="bx bx-plus me-1"></i>Create New Task
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notification = document.getElementById(`notification-${notificationId}`);
            notification.classList.remove('bg-light');
            notification.querySelector('.fw-semibold').classList.remove('fw-semibold');
            notification.querySelector('.fw-semibold').classList.add('text-muted');
            notification.querySelector('.mb-1').classList.add('text-muted');

            // Remove the mark as read button
            const markButton = notification.querySelector('button[onclick*="markAsRead"]');
            if (markButton) {
                markButton.remove();
            }

            // Update stats
            updateStats();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show updated notifications
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateStats() {
    // This would typically make an API call to get updated stats
    // For now, we'll just reload the page
    location.reload();
}
</script>
@endsection
