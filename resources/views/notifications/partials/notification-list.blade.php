@if($notifications->count() > 0)
    <div class="notification-list">
        @foreach($notifications as $notification)
            <div class="notification-item {{ $notification->is_read ? '' : 'unread' }}" data-id="{{ $notification->id }}">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="notification-icon">
                            @if($notification->notification_type === 'reply_received')
                                <i class="fas fa-reply text-success"></i>
                            @elseif($notification->notification_type === 'email_received')
                                <i class="fas fa-envelope text-primary"></i>
                            @elseif($notification->notification_type === 'email_opened')
                                <i class="fas fa-eye text-info"></i>
                            @else
                                <i class="fas fa-bell text-warning"></i>
                            @endif
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $notification->message }}</h6>
                                <p class="mb-1 text-muted small">{{ $notification->created_at->diffForHumans() }}</p>
                                @if($notification->email)
                                    <p class="mb-0 small text-info">
                                        <i class="fas fa-envelope"></i> {{ Str::limit($notification->email->subject, 50) }}
                                    </p>
                                @endif
                                @if($notification->email && $notification->email->task)
                                    <p class="mb-0 small text-success">
                                        <i class="fas fa-tasks"></i> Task: {{ Str::limit($notification->email->task->title, 30) }}
                                    </p>
                                @endif
                            </div>
                            <div class="notification-actions">
                                @if(!$notification->is_read)
                                    <button class="btn btn-sm btn-outline-primary" onclick="markAsRead({{ $notification->id }})">
                                        <i class="fas fa-check"></i> Mark Read
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-outline-info" onclick="viewNotification({{ $notification->id }})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                @if($notification->email)
                                    <a href="{{ route('emails.show', $notification->email->id) }}" class="btn btn-sm btn-outline-success" target="_blank">
                                        <i class="fas fa-envelope-open"></i> Email
                                    </a>
                                @endif
                                @if($notification->email && $notification->email->task)
                                    <a href="/tasks/{{ $notification->email->task->id }}" class="btn btn-sm btn-outline-warning" target="_blank">
                                        <i class="fas fa-tasks"></i> Task
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
    <div class="text-center text-muted py-5">
        <i class="fas fa-bell-slash fa-3x mb-3"></i>
        <h5>No notifications yet</h5>
        <p>You'll receive notifications here when someone replies to your emails.</p>
    </div>
@endif
