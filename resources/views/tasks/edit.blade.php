@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <h4 class="mb-3">Edit Task</h4>

    @if($task->status === 'in_review' && !Auth::user()->isManager())
        <div class="alert alert-warning" role="alert">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Task Under Review:</strong> This task is currently under review and cannot be edited until a manager changes its status.
        </div>
    @endif

    <div class="card p-3">
        <form method="POST" action="{{ route('tasks.update', $task) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="{{ request()->query('redirect_to', 'tasks.index') }}">
            @if(Auth::user()->isManager())
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select" required>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id', $task->project_id)==$project->id)>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Folder</label>
                    <select name="folder_id" class="form-select">
                        <option value="">None</option>
                        @foreach($folders as $f)
                            <option value="{{ $f->id }}" @selected(old('folder_id', $task->folder_id)==$f->id)>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $task->title) }}" required>
            </div>
            @else
            <!-- Non-managers see read-only project and folder info -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Project</label>
                    <input type="text" class="form-control" value="{{ $task->project->name }}" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Folder</label>
                    <input type="text" class="form-control" value="{{ $task->folder->name ?? 'Main Folder' }}" readonly>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" value="{{ $task->title }}" readonly>
            </div>
            @endif
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $task->description) }}</textarea>
            </div>
            <div class="row">
                @if(Auth::user()->isManager())
                <div class="col-md-3 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', optional($task->start_date)->format('Y-m-d')) }}">
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Cannot be before project start date</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        @php($priorities = ['low'=>'Low','normal'=>'Normal','medium'=>'Medium','high'=>'High','urgent'=>'Urgent','critical'=>'Critical'])
                        @foreach($priorities as $value=>$label)
                            <option value="{{ $value }}" @selected(old('priority', $task->priority)==$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <i class="bx bx-user me-1"></i>Assigned To
                        @if(old('assigned_to', $task->assigned_to) != $task->assigned_to)
                            <span class="badge bg-warning ms-1">Reassigning</span>
                        @endif
                    </label>
                    <select name="assigned_to" id="assigned_to" class="form-select">
                        <option value="">Unassigned</option>
                        @foreach(\App\Models\User::where('status', 'active')->where('role', '!=', 'admin')->orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" @selected(old('assigned_to', $task->assigned_to) == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                                @if($user->id == $task->assigned_to)
                                    - Current
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i>
                        @if($task->assignee)
                            Currently: <strong>{{ $task->assignee->name }}</strong>
                        @else
                            Currently unassigned
                        @endif
                    </small>
                </div>
                @else
                <div class="col-md-3 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="text" class="form-control" value="{{ optional($task->start_date)->format('M d, Y') ?? 'Not set' }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="text" class="form-control" value="{{ optional($task->due_date)->format('M d, Y') ?? 'Not set' }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Priority</label>
                    <input type="text" class="form-control" value="{{ ucfirst($task->priority) }}" readonly>
                </div>
                @endif
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['pending','assigned','in_progress','in_review','approved','rejected','completed'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $task->status)===$status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Add Attachments</label>
                <input type="file" name="attachments[]" class="form-control" multiple>
                <small class="text-muted">You can select multiple files. Max size 1GB per file.</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ $redirectUrl }}" class="btn btn-outline-secondary">
                    <i class="bx bx-x me-1"></i>Cancel
                </a>
                <button class="btn btn-primary">
                    <i class="bx bx-check me-1"></i>Update
                </button>
            </div>
        </form>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<script>
// Show reassignment warning when changing assignee
document.addEventListener('DOMContentLoaded', function() {
    const assignedToSelect = document.getElementById('assigned_to');

    if (assignedToSelect) {
        const originalAssignee = {{ $task->assigned_to ?? 'null' }};

        assignedToSelect.addEventListener('change', function() {
            const newAssignee = parseInt(this.value) || null;
            const form = this.closest('form');
            const submitButton = form.querySelector('button[type="submit"]');

            if (newAssignee !== originalAssignee) {
                // Show reassignment indicator
                submitButton.innerHTML = '<i class="bx bx-transfer me-1"></i>Update & Reassign';
                submitButton.classList.remove('btn-primary');
                submitButton.classList.add('btn-warning');

                // Show confirmation
                form.addEventListener('submit', function(e) {
                    const selectedOption = assignedToSelect.options[assignedToSelect.selectedIndex];
                    const newUserName = selectedOption.text;

                    if (!confirm(`Are you sure you want to reassign this task to ${newUserName}?`)) {
                        e.preventDefault();
                    }
                }, { once: true });
            } else {
                // Restore original button
                submitButton.innerHTML = '<i class="bx bx-check me-1"></i>Update';
                submitButton.classList.remove('btn-warning');
                submitButton.classList.add('btn-primary');
            }
        });
    }
});
</script>
@endsection


