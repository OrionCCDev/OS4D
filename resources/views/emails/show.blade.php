@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Email Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('email-notifications.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Notifications
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="email-details">
                                <div class="email-header mb-4">
                                    <h4>{{ $email->subject }}</h4>
                                    <div class="email-meta">
                                        <p><strong>From:</strong> {{ $email->from_email }}</p>
                                        <p><strong>To:</strong> {{ $email->to_email }}</p>
                                        @if($email->cc_emails && count($email->cc_emails) > 0)
                                            <p><strong>CC:</strong> {{ implode(', ', $email->cc_emails) }}</p>
                                        @endif
                                        <p><strong>Date:</strong> {{ $email->sent_at ? $email->sent_at->format('M d, Y g:i A') : $email->received_at->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>

                                <div class="email-body">
                                    <div class="email-content">
                                        {!! $email->body !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="email-stats">
                                <h5>Email Statistics</h5>
                                <div class="stats-list">
                                    <div class="stat-item">
                                        <i class="fas fa-paper-plane text-primary"></i>
                                        <span>Sent: {{ $email->sent_at ? $email->sent_at->format('M d, Y g:i A') : 'N/A' }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-eye text-info"></i>
                                        <span>Opened: {{ $email->opened_at ? $email->opened_at->format('M d, Y g:i A') : 'Not opened' }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-reply text-success"></i>
                                        <span>Replied: {{ $email->replied_at ? $email->replied_at->format('M d, Y g:i A') : 'No replies' }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($email->task)
                                <div class="related-task mt-4">
                                    <h5>Related Task</h5>
                                    <div class="task-info">
                                        <p><strong>Task:</strong> {{ $email->task->title }}</p>
                                        <p><strong>Status:</strong>
                                            <span class="badge badge-{{ $email->task->status === 'completed' ? 'success' : ($email->task->status === 'in_progress' ? 'warning' : 'info') }}">
                                                {{ ucfirst($email->task->status) }}
                                            </span>
                                        </p>
                                        <a href="{{ route('tasks.show', $email->task->id) }}" class="btn btn-sm btn-outline-primary">
                                            View Task
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($email->replies && $email->replies->count() > 0)
                                <div class="email-replies mt-4">
                                    <h5>Replies ({{ $email->replies->count() }})</h5>
                                    @foreach($email->replies as $reply)
                                        <div class="reply-item border-left border-primary pl-3 mb-3">
                                            <p><strong>From:</strong> {{ $reply->from_email }}</p>
                                            <p><strong>Date:</strong> {{ $reply->received_at->format('M d, Y g:i A') }}</p>
                                            <div class="reply-preview">
                                                {{ Str::limit(strip_tags($reply->body), 100) }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.email-details {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.email-header h4 {
    color: #333;
    margin-bottom: 15px;
}

.email-meta p {
    margin-bottom: 5px;
    color: #666;
}

.email-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.stats-list {
    list-style: none;
    padding: 0;
}

.stat-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 4px;
}

.stat-item i {
    margin-right: 10px;
    width: 20px;
}

.related-task, .email-replies {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.reply-item {
    background: white;
    padding: 10px;
    border-radius: 4px;
}

.reply-preview {
    color: #666;
    font-style: italic;
}
</style>
@endsection
