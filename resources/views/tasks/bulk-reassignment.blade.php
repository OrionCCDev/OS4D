@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb -->
    <x-modern-breadcrumb
        :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Users', 'url' => route('users.index')],
            ['label' => 'Bulk Task Reassignment']
        ]"
    />

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-transfer fs-3"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-1">Bulk Task Reassignment</h4>
                            <p class="text-muted mb-0">
                                <strong>From User:</strong> {{ $user->name }} ({{ $user->email }})
                                @if($user->status !== 'active')
                                    <span class="badge bg-{{ $user->status === 'resigned' ? 'danger' : 'warning' }} ms-2">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-task"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Active Tasks</p>
                            <h4 class="mb-0">{{ $tasks->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-time-five"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">In Progress</p>
                            <h4 class="mb-0">{{ $tasks->where('status', 'in_progress')->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="bx bx-error-circle"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Overdue</p>
                            <h4 class="mb-0">{{ $tasks->where('due_date', '<', now()->startOfDay())->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($tasks->count() > 0)
    <!-- Reassignment Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Select Tasks to Reassign</h5>
            <p class="text-muted mb-0">Choose the tasks you want to reassign and select the new assignee</p>
        </div>
        <div class="card-body">
            <form id="reassignmentForm">
                @csrf
                <input type="hidden" name="from_user_id" value="{{ $user->id }}">

                <!-- New Assignee Selection -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="to_user_id" class="form-label">
                            <i class="bx bx-user me-1"></i>New Assignee <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="to_user_id" name="to_user_id" required>
                            <option value="">Select a user...</option>
                            @foreach($availableUsers as $availableUser)
                                <option value="{{ $availableUser->id }}">
                                    {{ $availableUser->name }} ({{ $availableUser->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="reassignment_reason" class="form-label">
                            <i class="bx bx-note me-1"></i>Reason for Reassignment
                        </label>
                        <input type="text" class="form-control" id="reassignment_reason" name="reassignment_reason"
                               placeholder="e.g., Employee resigned, On leave, etc.">
                    </div>
                </div>

                <!-- Deactivate User Option -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="deactivate_user" name="deactivate_user" value="1">
                            <label class="form-check-label" for="deactivate_user">
                                Mark {{ $user->name }} as <strong>Inactive</strong> after reassignment
                            </label>
                            <small class="d-block text-muted mt-1">
                                This will prevent the user from accessing the system but keep their account and history intact
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Task Selection -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">
                            <i class="bx bx-list-check me-1"></i>Tasks to Reassign
                        </label>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllTasks()">
                                <i class="bx bx-check-square me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllTasks()">
                                <i class="bx bx-square me-1"></i>Deselect All
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="select_all_checkbox" onchange="toggleAllTasks(this)">
                                    </th>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tasks as $task)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="task-checkbox" name="task_ids[]" value="{{ $task->id }}" checked>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $task->title }}</h6>
                                            @if($task->description)
                                                <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $task->project->name ?? 'No Project' }}</td>
                                    <td>
                                        <span class="badge {{ $task->status_badge_class }} text-white">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $task->priority_badge_class }} text-white">
                                            {{ ucfirst($task->priority ?? 'Normal') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($task->due_date)
                                            <span class="{{ $task->is_overdue ? 'text-danger' : '' }}">
                                                {{ $task->due_date->format('M j, Y') }}
                                            </span>
                                            @if($task->is_overdue)
                                                <br><small class="badge bg-danger">Overdue</small>
                                            @endif
                                        @else
                                            <span class="text-muted">No due date</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-transfer me-1"></i>Reassign Selected Tasks
                    </button>
                </div>
            </form>
        </div>
    </div>
    @else
    <!-- No Tasks Message -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-check-circle text-success" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No Active Tasks</h4>
            <p class="text-muted">{{ $user->name }} has no active tasks to reassign.</p>
            <a href="{{ route('users.index') }}" class="btn btn-primary mt-2">
                <i class="bx bx-arrow-back me-1"></i>Back to Users
            </a>
        </div>
    </div>
    @endif
</div>

<script>
function selectAllTasks() {
    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('select_all_checkbox').checked = true;
}

function deselectAllTasks() {
    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('select_all_checkbox').checked = false;
}

function toggleAllTasks(checkbox) {
    document.querySelectorAll('.task-checkbox').forEach(taskCheckbox => {
        taskCheckbox.checked = checkbox.checked;
    });
}

document.getElementById('reassignmentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Check if at least one task is selected
    const selectedTasks = document.querySelectorAll('.task-checkbox:checked');
    if (selectedTasks.length === 0) {
        alert('Please select at least one task to reassign');
        return;
    }

    // Check if new assignee is selected
    const toUserId = document.getElementById('to_user_id').value;
    if (!toUserId) {
        alert('Please select a new assignee');
        return;
    }

    // Confirm action
    const confirmMessage = `Are you sure you want to reassign ${selectedTasks.length} task(s)?`;
    if (!confirm(confirmMessage)) {
        return;
    }

    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Reassigning...';
    submitButton.disabled = true;

    // Prepare form data
    const formData = new FormData(this);

    // Send request
    fetch('{{ route("tasks.bulk-reassign") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Restore button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;

        if (data.success) {
            // Show success message
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed';
            successAlert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            successAlert.innerHTML = `
                <i class="bx bx-check-circle me-2"></i>
                <strong>Success!</strong> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(successAlert);

            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = '{{ route("users.index") }}';
            }, 2000);
        } else {
            alert('Error: ' + (data.message || 'Failed to reassign tasks'));
        }
    })
    .catch(error => {
        // Restore button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;

        console.error('Error:', error);
        alert('An error occurred while reassigning tasks');
    });
});
</script>
@endsection

