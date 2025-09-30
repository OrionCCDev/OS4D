@extends('layouts.header')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title">🔍 Debug Email Notifications</h3>
                </div>
                <div class="card-body">
                    <h5>Current User: {{ Auth::user()->name }} (ID: {{ Auth::id() }})</h5>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>📊 Notification Statistics:</h6>
                            <ul>
                                <li><strong>Total Notifications:</strong> {{ App\Models\EmailNotification::count() }}</li>
                                <li><strong>User Notifications:</strong> {{ App\Models\EmailNotification::where('user_id', Auth::id())->count() }}</li>
                                <li><strong>Unread Notifications:</strong> {{ App\Models\EmailNotification::where('user_id', Auth::id())->where('is_read', false)->count() }}</li>
                                <li><strong>Reply Notifications:</strong> {{ App\Models\EmailNotification::where('user_id', Auth::id())->where('notification_type', 'reply_received')->count() }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>📧 Email Statistics:</h6>
                            <ul>
                                <li><strong>Total Emails:</strong> {{ App\Models\Email::count() }}</li>
                                <li><strong>Sent Emails:</strong> {{ App\Models\Email::where('email_type', 'sent')->count() }}</li>
                                <li><strong>Received Emails:</strong> {{ App\Models\Email::where('email_type', 'received')->count() }}</li>
                                <li><strong>Tracked Emails:</strong> {{ App\Models\Email::where('is_tracked', true)->count() }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>🔔 Recent Notifications for Current User:</h6>
                            @php
                                $userNotifications = App\Models\EmailNotification::where('user_id', Auth::id())
                                    ->with(['email'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get();
                            @endphp

                            @if($userNotifications->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Type</th>
                                                <th>Message</th>
                                                <th>Read</th>
                                                <th>Created</th>
                                                <th>Email Subject</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($userNotifications as $notification)
                                                <tr class="{{ $notification->is_read ? '' : 'table-warning' }}">
                                                    <td>{{ $notification->id }}</td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $notification->notification_type }}</span>
                                                    </td>
                                                    <td>{{ Str::limit($notification->message, 50) }}</td>
                                                    <td>
                                                        @if($notification->is_read)
                                                            <span class="badge badge-success">Read</span>
                                                        @else
                                                            <span class="badge badge-warning">Unread</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $notification->created_at->format('M j, Y H:i') }}</td>
                                                    <td>
                                                        @if($notification->email)
                                                            {{ Str::limit($notification->email->subject, 30) }}
                                                        @else
                                                            <span class="text-muted">No email</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <strong>No notifications found for current user!</strong>
                                    <p>This means notifications are being created for the wrong user ID.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>🔧 Quick Actions:</h6>
                            <div class="btn-group">
                                <button class="btn btn-primary" onclick="createNotificationForCurrentUser()">
                                    Create Notification for Current User
                                </button>
                                <button class="btn btn-success" onclick="createNotificationForManager()">
                                    Create Notification for Manager (User ID 1)
                                </button>
                                <button class="btn btn-info" onclick="location.reload()">
                                    Refresh Page
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>📋 All Notifications (All Users):</h6>
                            @php
                                $allNotifications = App\Models\EmailNotification::with(['email'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(20)
                                    ->get();
                            @endphp

                            @if($allNotifications->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>User ID</th>
                                                <th>Type</th>
                                                <th>Message</th>
                                                <th>Read</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($allNotifications as $notification)
                                                <tr class="{{ $notification->user_id == Auth::id() ? 'table-success' : '' }}">
                                                    <td>{{ $notification->id }}</td>
                                                    <td>
                                                        {{ $notification->user_id }}
                                                        @if($notification->user_id == Auth::id())
                                                            <span class="badge badge-success">You</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $notification->notification_type }}</span>
                                                    </td>
                                                    <td>{{ Str::limit($notification->message, 40) }}</td>
                                                    <td>
                                                        @if($notification->is_read)
                                                            <span class="badge badge-success">Read</span>
                                                        @else
                                                            <span class="badge badge-warning">Unread</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $notification->created_at->format('M j, H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">No notifications found in the system.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createNotificationForCurrentUser() {
    fetch('/create-notification-for-user', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: {{ Auth::id() }} })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notification created for current user!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function createNotificationForManager() {
    fetch('/create-notification-for-user', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: 1 })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notification created for manager!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
@endsection
