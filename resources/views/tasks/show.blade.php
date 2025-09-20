@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </a>
                        <div class="vr"></div>
                        <nav aria-label="breadcrumb" class="mb-0">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                                <li class="breadcrumb-item active">{{ Str::limit($task->title, 50) }}</li>
                            </ol>
                        </nav>
                    </div>
                    <h2 class="mb-2 text-primary">{{ $task->title }}</h2>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        @php
                            $statusColors = [
                                'pending' => 'secondary',
                                'assigned' => 'info',
                                'in_progress' => 'warning',
                                'in_review' => 'primary',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'completed' => 'success'
                            ];
                            $priorityColors = [
                                1 => 'danger',
                                2 => 'warning',
                                3 => 'info',
                                4 => 'primary',
                                5 => 'secondary'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }} fs-6 px-3 py-2">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                        <span class="badge bg-{{ $priorityColors[$task->priority ?? 5] ?? 'secondary' }} fs-6 px-3 py-2">
                            Priority {{ $task->priority ?? 5 }}
                        </span>
                        @if($task->due_date)
                            <span class="badge bg-{{ $task->is_overdue ? 'danger' : 'success' }} fs-6 px-3 py-2">
                                <i class="bx bx-{{ $task->is_overdue ? 'time-five' : 'timer' }} me-1"></i>
                                @if($task->status === 'completed')
                                    Completed
                                @elseif($task->days_remaining !== null)
                                    {{ abs($task->days_remaining) }} {{ $task->is_overdue ? 'days overdue' : 'days left' }}
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary">
                        <i class="bx bx-edit me-1"></i>Edit Task
                    </a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?')" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger">
                            <i class="bx bx-trash me-1"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Task Overview Cards -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        <i class="bx bx-folder-open"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-muted">Project</h6>
                                    <p class="mb-0 fw-semibold">{{ $task->project?->name ?? 'No Project' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg me-3">
                                    <span class="avatar-initial rounded-circle bg-label-info">
                                        <i class="bx bx-folder"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-muted">Folder</h6>
                                    <p class="mb-0 fw-semibold">{{ $task->folder?->name ?? 'No Folder' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- People & Dates -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3 text-muted">People</h6>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($task->creator?->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <small class="text-muted">Created by</small>
                                        <p class="mb-0 fw-semibold">{{ $task->creator?->name ?? 'Unknown' }}</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        @if($task->assignee)
                                            <span class="avatar-initial rounded-circle bg-label-success">{{ substr($task->assignee->name, 0, 1) }}</span>
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-secondary">?</span>
                                        @endif
                                    </div>
                                    <div>
                                        <small class="text-muted">Assigned to</small>
                                        <p class="mb-0 fw-semibold">{{ $task->assignee?->name ?? 'Unassigned' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3 text-muted">Timeline</h6>
                            <div class="mb-2">
                                <small class="text-muted">Created</small>
                                <p class="mb-0 fw-semibold">{{ $task->created_at->format('M d, Y') }}</p>
                            </div>
                            @if($task->due_date)
                                <div class="mb-2">
                                    <small class="text-muted">Due Date</small>
                                    <p class="mb-0 fw-semibold {{ $task->due_date < now() && $task->status !== 'completed' ? 'text-danger' : '' }}">
                                        {{ $task->due_date->format('M d, Y') }}
                                    </p>
                                </div>
                            @endif
                            @if($task->completed_at)
                                <div>
                                    <small class="text-muted">Completed</small>
                                    <p class="mb-0 fw-semibold text-success">{{ $task->completed_at->format('M d, Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($task->description)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0">Description</h5>
                </div>
                <div class="card-body pt-2">
                    <div class="bg-light rounded p-3">
                        {{ $task->description }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Completion Notes -->
            @if($task->completion_notes)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0">Completion Notes</h5>
                </div>
                <div class="card-body pt-2">
                    <div class="bg-light rounded p-3">
                        {{ $task->completion_notes }}
                    </div>
                </div>
            </div>
            @endif


            <!-- Task Files -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Task Files</h5>
                        <form action="{{ route('tasks.attachments.upload', $task) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                            @csrf
                            <input type="file" name="file" class="form-control form-control-sm" required>
                            <button class="btn btn-primary btn-sm">
                                <i class="bx bx-upload me-1"></i>Upload
                            </button>
                        </form>
                    </div>
                    @if($task->attachments->count())
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bx bx-search"></i>
                                </span>
                                <input type="text" class="form-control" id="fileSearch" placeholder="Search files...">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($task->attachments->count())
                        <div class="row" id="filesContainer">
                            @foreach($task->attachments->sortByDesc('created_at') as $att)
                                <div class="col-md-6 mb-3 file-item" data-filename="{{ strtolower($att->original_name) }}">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        <i class="bx bx-file"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $att->original_name }}</h6>
                                                    <small class="text-muted d-block mb-2">
                                                        {{ number_format($att->size_bytes/1024,1) }} KB • {{ $att->mime_type }}
                                                    </small>
                                                    <small class="text-muted">
                                                        by {{ $att->uploader?->name }} • {{ $att->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2 mt-3">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ Storage::url($att->path) }}" target="_blank">
                                                    <i class="bx bxs-show me-1"></i>View
                                                </a>
                                                <a class="btn btn-sm btn-outline-success" href="{{ route('tasks.attachments.download', $att) }}">
                                                    <i class="bx bx-download me-1"></i>Download
                                                </a>
                                                @if(Auth::user()->isManager() || $att->uploaded_by === Auth::id())
                                                    <form action="{{ route('tasks.attachments.delete', [$task, $att]) }}" method="POST" onsubmit="return confirm('Delete attachment?')" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="bx bx-trash me-1"></i>Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-file" style="font-size: 3rem; color: #d1d5db;"></i>
                            <p class="text-muted mt-2">No files uploaded yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Task History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Task History</h5>
                </div>
                <div class="card-body">
                    @if($task->histories->count() > 0)
                        <div class="timeline">
                            @foreach($task->histories->sortByDesc('created_at') as $history)
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $history->description }}</h6>
                                                <div class="d-flex align-items-center gap-3 text-muted">
                                                    <small>
                                                        <i class="bx bx-user me-1"></i>{{ $history->user->name }}
                                                    </small>
                                                    <small>
                                                        <i class="bx bx-time me-1"></i>{{ $history->created_at->format('M d, Y H:i') }}
                                                    </small>
                                                </div>
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
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($task->assigned_to === auth()->id() && in_array($task->status, ['assigned', 'in_progress', 'in_review']))
                            <button class="btn btn-success" onclick="changeTaskStatus({{ $task->id }})">
                                <i class="bx bx-check me-2"></i>Change Status
                            </button>
                        @endif

                        @if(auth()->user()->isManager() && !$task->assigned_to)
                            <button class="btn btn-info" onclick="assignTask({{ $task->id }})">
                                <i class="bx bx-user-plus me-2"></i>Assign Task
                            </button>
                        @endif


                    </div>
                </div>
            </div>

            <!-- Task Stats -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Task Stats</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <h4 class="mb-1 text-primary">{{ $task->histories->count() }}</h4>
                                <small class="text-muted">Updates</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <h4 class="mb-1 text-info">{{ floor($task->created_at->diffInDays(now())) }}</h4>
                                <small class="text-muted">Days Old</small>
                            </div>
                        </div>
                    </div>

                    @if($task->completion_time)
                        <div class="bg-light rounded p-3 text-center mb-3">
                            <h4 class="mb-1 text-success">{{ $task->completion_time }}</h4>
                            <small class="text-muted">Days to Complete</small>
                        </div>
                    @endif

                    <div class="timeline-dates">
                        @if($task->assigned_at)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <small class="text-muted">Assigned</small>
                                <span class="fw-semibold">{{ $task->assigned_at->format('M d, Y') }}</span>
                            </div>
                        @endif

                        @if($task->started_at)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <small class="text-muted">Started</small>
                                <span class="fw-semibold">{{ $task->started_at->format('M d, Y') }}</span>
                            </div>
                        @endif

                        @if($task->completed_at)
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <small class="text-muted">Completed</small>
                                <span class="fw-semibold text-success">{{ $task->completed_at->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </div>
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

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 20px;
    bottom: -20px;
    width: 2px;
    background: #e9ecef;
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
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border-left: 3px solid #696cff;
    transition: all 0.2s ease;
}

.timeline-content:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card {
    transition: all 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.bg-label-primary { background-color: rgba(105, 108, 255, 0.1) !important; color: #696cff !important; }
.bg-label-info { background-color: rgba(67, 89, 126, 0.1) !important; color: #43597e !important; }
.bg-label-success { background-color: rgba(114, 225, 40, 0.1) !important; color: #72e128 !important; }
.bg-label-warning { background-color: rgba(255, 193, 7, 0.1) !important; color: #ffc107 !important; }
.bg-label-danger { background-color: rgba(220, 53, 69, 0.1) !important; color: #dc3545 !important; }
.bg-label-secondary { background-color: rgba(108, 117, 125, 0.1) !important; color: #6c757d !important; }

.timeline-dates .border-bottom:last-child {
    border-bottom: none !important;
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

// File search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('fileSearch');
    const fileItems = document.querySelectorAll('.file-item');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            fileItems.forEach(function(item) {
                const filename = item.getAttribute('data-filename');
                if (filename.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection
