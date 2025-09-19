@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $task->title }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                    <li class="breadcrumb-item active">{{ $task->title }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back
            </a>
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i>Edit
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Task Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Task Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Project</label>
                            <p class="mb-0">
                                <span class="badge bg-label-primary">{{ $task->project?->name }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Folder</label>
                            <p class="mb-0">
                                <span class="badge bg-label-info">{{ $task->folder?->name ?? 'No Folder' }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <p class="mb-0">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-label-secondary',
                                        'assigned' => 'bg-label-info',
                                        'in_progress' => 'bg-label-warning',
                                        'in_review' => 'bg-label-primary',
                                        'approved' => 'bg-label-success',
                                        'rejected' => 'bg-label-danger',
                                        'completed' => 'bg-label-success'
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$task->status] ?? 'bg-label-secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Priority</label>
                            <p class="mb-0">
                                @php
                                    $priorityColors = [
                                        1 => 'bg-danger',
                                        2 => 'bg-warning',
                                        3 => 'bg-info',
                                        4 => 'bg-primary',
                                        5 => 'bg-secondary'
                                    ];
                                @endphp
                                <span class="badge {{ $priorityColors[$task->priority ?? 5] ?? 'bg-secondary' }}">
                                    {{ $task->priority ?? 5 }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Created By</label>
                            <p class="mb-0">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($task->creator?->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <span>{{ $task->creator?->name ?? 'Unknown' }}</span>
                                </div>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Assigned To</label>
                            <p class="mb-0">
                                @if($task->assignee)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($task->assignee->name, 0, 1) }}</span>
                                        </div>
                                        <span>{{ $task->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Due Date</label>
                            <p class="mb-0">
                                @if($task->due_date)
                                    <span class="{{ $task->due_date < now() ? 'text-danger' : 'text-muted' }}">
                                        {{ $task->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">No due date</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Created At</label>
                            <p class="mb-0">{{ $task->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($task->description)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <div class="border rounded p-3 bg-light">
                            {{ $task->description }}
                        </div>
                    </div>
                    @endif

                    @if($task->completion_notes)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Completion Notes</label>
                        <div class="border rounded p-3 bg-light">
                            {{ $task->completion_notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Task Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Task Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-{{ $task->status_badge_class }}">
                                        <i class="bx bx-{{ $task->status === 'completed' ? 'check' : 'clock' }}"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Status</h6>
                                    <span class="badge {{ $task->status_badge_class }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-{{ $task->priority_badge_class }}">
                                        <i class="bx bx-flag"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">Priority</h6>
                                    <span class="badge {{ $task->priority_badge_class }}">{{ ucfirst($task->priority) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-{{ $task->is_overdue ? 'danger' : 'success' }}">
                                        <i class="bx bx-{{ $task->is_overdue ? 'time-five' : 'timer' }}"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        @if($task->status === 'completed')
                                            Completion Time
                                        @else
                                            {{ $task->is_overdue ? 'Overdue' : 'Days Remaining' }}
                                        @endif
                                    </h6>
                                    <span class="text-{{ $task->is_overdue ? 'danger' : ($task->status === 'completed' ? 'success' : 'muted') }}">
                                        @if($task->status === 'completed' && $task->completion_time)
                                            {{ $task->completion_time }} days
                                        @elseif($task->days_remaining !== null)
                                            {{ abs($task->days_remaining) }} days
                                            @if($task->is_overdue)
                                                overdue
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attachments</h5>
                    <form action="{{ route('tasks.attachments.upload', $task) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                        @csrf
                        <input type="file" name="file" class="form-control" required>
                        <button class="btn btn-primary">Upload</button>
                    </form>
                </div>
                <div class="card-body">
                    @if($task->attachments->count())
                        <ul class="list-group">
                            @foreach($task->attachments as $att)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="bx bx-paperclip"></i>
                                        <div>
                                            <div class="fw-semibold">{{ $att->original_name }}</div>
                                            <small class="text-muted">{{ number_format($att->size_bytes/1024,1) }} KB • {{ $att->mime_type }} • by {{ $att->uploader?->name }} • {{ $att->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ Storage::disk($att->disk)->url($att->path) }}" target="_blank">
                                            <i class="bx bx-show"></i> View
                                        </a>
                                        <a class="btn btn-sm btn-outline-success" href="{{ route('tasks.attachments.download', $att) }}">
                                            <i class="bx bx-download"></i> Download
                                        </a>
                                        @if(Auth::user()->isManager() || $att->uploaded_by === Auth::id())
                                            <form action="{{ route('tasks.attachments.delete', [$task, $att]) }}" method="POST" onsubmit="return confirm('Delete attachment?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bx bx-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted">No attachments yet.</div>
                    @endif
                </div>
            </div>

            <!-- Task History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Task History</h5>
                </div>
                <div class="card-body">
                    @if($task->histories->count() > 0)
                        <div class="timeline">
                            @foreach($task->histories as $history)
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $history->description }}</h6>
                                                <p class="text-muted mb-1">
                                                    <i class="bx bx-user me-1"></i>{{ $history->user->name }}
                                                </p>
                                                <small class="text-muted">
                                                    <i class="bx bx-time me-1"></i>{{ $history->created_at->format('M d, Y H:i') }}
                                                </small>
                                            </div>
                                            <span class="badge bg-label-secondary">{{ ucfirst(str_replace('_', ' ', $history->action)) }}</span>
                                        </div>
                                        @if($history->old_value || $history->new_value)
                                            <div class="mt-2">
                                                @if($history->old_value)
                                                    <span class="badge bg-label-danger me-1">From: {{ $history->old_value }}</span>
                                                @endif
                                                @if($history->new_value)
                                                    <span class="badge bg-label-success">To: {{ $history->new_value }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-history" style="font-size: 3rem; color: #d1d5db;"></i>
                            <p class="text-muted mt-2">No history available for this task</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if($task->assigned_to === auth()->id() && in_array($task->status, ['assigned', 'in_progress', 'in_review']))
                        <button class="btn btn-success w-100 mb-2" onclick="changeTaskStatus({{ $task->id }})">
                            <i class="bx bx-check me-1"></i>Change Status
                        </button>
                    @endif

                    @if(auth()->user()->isManager() && !$task->assigned_to)
                        <button class="btn btn-info w-100 mb-2" onclick="assignTask({{ $task->id }})">
                            <i class="bx bx-user-plus me-1"></i>Assign Task
                        </button>
                    @endif

                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bx bx-edit me-1"></i>Edit Task
                    </a>

                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline w-100">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger w-100" onclick="return confirm('Delete this task?')">
                            <i class="bx bx-trash me-1"></i>Delete Task
                        </button>
                    </form>
                </div>
            </div>

            <!-- Task Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Task Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="mb-1 text-primary">{{ $task->histories->count() }}</h4>
                                <small class="text-muted">Updates</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="mb-1 text-info">{{ $task->created_at->diffInDays(now()) }}</h4>
                                <small class="text-muted">Days Old</small>
                            </div>
                        </div>
                    </div>

                    @if($task->assigned_at)
                        <div class="mb-2">
                            <small class="text-muted">Assigned:</small><br>
                            <span class="fw-semibold">{{ $task->assigned_at->format('M d, Y H:i') }}</span>
                        </div>
                    @endif

                    @if($task->started_at)
                        <div class="mb-2">
                            <small class="text-muted">Started:</small><br>
                            <span class="fw-semibold">{{ $task->started_at->format('M d, Y H:i') }}</span>
                        </div>
                    @endif

                    @if($task->completed_at)
                        <div class="mb-2">
                            <small class="text-muted">Completed:</small><br>
                            <span class="fw-semibold">{{ $task->completed_at->format('M d, Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Assignment Modal -->
<div class="modal fade" id="assignTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignTaskForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign to User</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Select user</option>
                            @foreach(\App\Models\User::where('id', '!=', auth()->id())->orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Task Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="changeStatusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="in_progress">In Progress</option>
                            <option value="in_review">In Review</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    background: #696cff;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #e7e7ff;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 3px solid #696cff;
}
</style>

<script>
// Task assignment
function assignTask(taskId) {
    document.getElementById('assignTaskForm').action = `/tasks/${taskId}/assign`;
    new bootstrap.Modal(document.getElementById('assignTaskModal')).show();
}

// Status change
function changeTaskStatus(taskId) {
    document.getElementById('changeStatusForm').action = `/tasks/${taskId}/change-status`;
    new bootstrap.Modal(document.getElementById('changeStatusModal')).show();
}
</script>
@endsection
