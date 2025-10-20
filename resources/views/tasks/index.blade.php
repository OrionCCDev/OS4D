@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tasks</h4>
        @if(Auth::user()->isManager())
        <div class="d-flex gap-2">
            {{--  fm1.1  --}}
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>New Task
            </a>
        </div>
        @endif
    </div>


    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Statistics</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tasks as $task)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    @php
                                        $priorityColors = [
                                            1 => 'bg-danger',
                                            2 => 'bg-warning',
                                            3 => 'bg-info',
                                            4 => 'bg-primary',
                                            5 => 'bg-secondary'
                                        ];
                                        $statusColors = [
                                            'pending' => 'bg-label-secondary',
                                            'assigned' => 'bg-label-info',
                                            'in_progress' => 'bg-label-warning',
                                            'in_review' => 'bg-label-primary',
                                            'approved' => 'bg-label-success',
                                            'rejected' => 'bg-label-danger',
                                            'waiting_sending_client_consultant_approve' => 'bg-label-warning',
                                            'waiting_client_consultant_approve' => 'bg-label-info',
                                            'completed' => 'bg-label-success'
                                        ];
                                    @endphp
                                    <div class="rounded-circle {{ $priorityColors[$task->priority ?? 5] ?? 'bg-secondary' }}" style="width: 8px; height: 8px;"></div>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $task->title }}</div>
                                    @if($task->description)
                                        <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
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
                        </td>
                        <td>
                            <span class="badge {{ $task->status_badge_class }}">
                                @if($task->status === 'submitted_for_review')
                                    For Review
                                @elseif($task->status === 'waiting_sending_client_consultant_approve')
                                    Waiting to Send
                                @elseif($task->status === 'waiting_client_consultant_approve')
                                    Waiting Approval
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $task->priority_badge_class }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>
                        <td>
                            @if($task->due_date)
                                <span class="{{ $task->due_date < now() ? 'text-danger' : 'text-muted' }}">
                                    {{ $task->due_date->format('M d, Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($task->status === 'completed')
                                <span class="badge bg-success">
                                    <i class="bx bx-check"></i> Completed
                                </span>
                            @elseif($task->days_remaining !== null)
                                <span class="badge {{ $task->is_overdue ? 'bg-danger' : 'bg-info' }}">
                                    <i class="bx bx-{{ $task->is_overdue ? 'time-five' : 'timer' }}"></i>
                                    {{ abs($task->days_remaining) }}d
                                    @if($task->is_overdue) overdue @endif
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bx bxs-show"></i>
                                </a>
                                @if(Auth::user()->isManager())
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                @endif

                                @if(Auth::user()->isManager() || ($task->assigned_to === auth()->id() && !in_array($task->status, ['submitted_for_review', 'in_review', 'approved', 'completed'])))
                                    <button class="btn btn-sm btn-outline-success" onclick="changeTaskStatus({{ $task->id }})" title="Change Status">
                                        <i class="bx bx-check"></i>
                                        @if(Auth::user()->isManager())
                                            <span class="badge bg-warning ms-1" style="font-size: 0.6em;">M</span>
                                        @endif
                                    </button>
                                @endif

                                @if(auth()->user()->isManager() && !$task->assigned_to)
                                    <button class="btn btn-sm btn-outline-info" onclick="assignTask({{ $task->id }})" title="Assign Task">
                                        <i class="bx bx-user-plus"></i>
                                    </button>
                                @endif

                                @if(Auth::user()->isManager())
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this task?')" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $tasks->links() }}</div>
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
                            @foreach(\App\Models\User::where('id', '!=', auth()->id())->where('role', 'user')->orderBy('name')->get() as $user)
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

// Live notification updates
function updateNotificationCount() {
    fetch('/api/notifications/count')
        .then(response => response.json())
        .then(data => {
            document.getElementById('notification-count').textContent = data.count;
        })
        .catch(error => console.error('Error:', error));
}

// Update notification count every 30 seconds
setInterval(updateNotificationCount, 30000);

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationCount();
});
</script>
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
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="submitted_for_review">Submitted for Review</option>
                            <option value="in_review">In Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="waiting_sending_client_consultant_approve">Waiting to Send</option>
                            <option value="waiting_client_consultant_approve">Waiting Approval</option>
                            <option value="completed">Completed</option>
                        </select>
                        @if(Auth::user()->isManager())
                            <div class="form-text text-warning">
                                <i class="bx bx-info-circle me-1"></i>
                                As a manager, you can change the status of any task at any time.
                            </div>
                        @endif
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

<!-- Assign Task Modal -->
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
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Select User</option>
                            @foreach(\App\Models\User::where('role', 'user')->orderBy('name')->get() as $user)
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

<script>
// Status change
function changeTaskStatus(taskId) {
    document.getElementById('changeStatusForm').action = `/tasks/${taskId}/change-status`;
    new bootstrap.Modal(document.getElementById('changeStatusModal')).show();
}

// Assign task
function assignTask(taskId) {
    document.getElementById('assignTaskForm').action = `/tasks/${taskId}/assign`;
    new bootstrap.Modal(document.getElementById('assignTaskModal')).show();
}
</script>

@endsection


