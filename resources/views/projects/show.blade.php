@extends('layouts.app')

@php
    $priorityColors = [
        1 => 'bg-danger',
        2 => 'bg-warning',
        3 => 'bg-info',
        4 => 'bg-primary',
        5 => 'bg-secondary'
    ];
    $statusColors = [
        'todo' => 'bg-label-secondary',
        'in_progress' => 'bg-label-warning',
        'in_review' => 'bg-label-info',
        'approved' => 'bg-label-success',
        'rejected' => 'bg-label-danger',
        'done' => 'bg-label-primary'
    ];
@endphp

@section('content')
<div class="container flex-grow-1 container-p-y">
    <!-- Project Header -->
    <x-modern-breadcrumb
        title="{{ $project->name }}"
        subtitle="Status: {{ ucfirst(str_replace('_',' ', $project->status)) }}"
        icon="bx-folder-open"
        theme="projects"
        :breadcrumbs="[
            ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx-home'],
            ['title' => 'Projects', 'url' => route('projects.index'), 'icon' => 'bx-folder'],
            ['title' => $project->name, 'url' => '#', 'icon' => 'bx-folder-open']
        ]"
    />

    <!-- Current Folder Breadcrumb -->
    @if($selectedFolder)
    <div class="card mb-4">
        <div class="card-body py-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('projects.show', ['project' => $project->id]) }}" class="text-decoration-none">
                            <i class="bx bx-home me-1"></i>All Folders
                        </a>
                    </li>
                    @foreach($breadcrumbs as $crumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            @if($loop->last)
                                {{ $crumb->name }}
                            @else
                                <a href="{{ route('projects.show', ['project' => $project->id, 'folder' => $crumb->id]) }}" class="text-decoration-none">
                                    {{ $crumb->name }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>
    </div>
    @endif

    <!-- Folders Section -->
    <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="bx bx-folder me-2"></i>
                    {{ $selectedFolder ? 'Subfolders in "' . $selectedFolder->name . '"' : 'Project Folders' }}
                </h5>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-primary" href="{{ route('folders.create', ['project_id' => $project->id, 'parent_id' => $selectedFolder?->id]) }}">
                        <i class="bx bx-folder-plus me-1"></i>Add Folder
                    </a>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#foldersSection" aria-expanded="true" aria-controls="foldersSection">
                        <i class="bx bx-chevron-down" id="foldersToggleIcon"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="collapse show" id="foldersSection">
            <div class="card-body">
                @if($selectedFolder && $selectedFolder->children->count() > 0)
                    <div class="row">
                        @foreach($selectedFolder->children as $folder)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card folder-card h-100 position-relative overflow-hidden" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e5e7eb; cursor: pointer;" onclick="window.location.href='{{ route('projects.show', ['project' => $project->id, 'folder' => $folder->id]) }}'">
                                    <!-- Colorful gradient background -->
                                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>

                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="folder-icon me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bx bx-folder text-white" style="font-size: 24px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-semibold text-truncate" title="{{ $folder->name }}">{{ $folder->name }}</h6>
                                                <small class="text-muted">{{ $folder->children_count }} subfolders</small>
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                                                    <div class="fw-bold text-primary">{{ $folder->tasks_count }}</div>
                                                    <small class="text-muted">Total Tasks</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center p-2 rounded" style="background: #fff3cd;">
                                                    <div class="fw-bold text-warning">{{ $folder->incomplete_tasks_count }}</div>
                                                    <small class="text-muted">Pending</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-label-primary">
                                                <i class="bx bx-folder me-1"></i>Open
                                            </span>
                                            <a href="{{ route('folders.create', ['project_id' => $project->id, 'parent_id' => $folder->id]) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               onclick="event.stopPropagation();">
                                                <i class="bx bx-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif(!$selectedFolder && $rootFolders->count() > 0)
                    <div class="row">
                        @foreach($rootFolders as $folder)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card folder-card h-100 position-relative overflow-hidden" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e5e7eb; cursor: pointer;" onclick="window.location.href='{{ route('projects.show', ['project' => $project->id, 'folder' => $folder->id]) }}'">
                                    <!-- Colorful gradient background -->
                                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>

                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="folder-icon me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bx bx-folder text-white" style="font-size: 24px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-semibold text-truncate" title="{{ $folder->name }}">{{ $folder->name }}</h6>
                                                <small class="text-muted">{{ $folder->children_count }} subfolders</small>
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                                                    <div class="fw-bold text-primary">{{ $folder->tasks_count }}</div>
                                                    <small class="text-muted">Total Tasks</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center p-2 rounded" style="background: #fff3cd;">
                                                    <div class="fw-bold text-warning">{{ $folder->incomplete_tasks_count }}</div>
                                                    <small class="text-muted">Pending</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-label-primary">
                                                <i class="bx bx-folder me-1"></i>Open
                                            </span>
                                            <a href="{{ route('folders.create', ['project_id' => $project->id, 'parent_id' => $folder->id]) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               onclick="event.stopPropagation();">
                                                <i class="bx bx-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bx bx-folder-plus" style="font-size: 4rem; color: #d1d5db;"></i>
                        </div>
                        <h5 class="text-muted mb-2">No folders found</h5>
                        <p class="text-muted mb-4">Create your first folder to organize your project</p>
                        <a href="{{ route('folders.create', ['project_id' => $project->id, 'parent_id' => $selectedFolder?->id]) }}" class="btn btn-primary">
                            <i class="bx bx-folder-plus me-1"></i>Create Folder
                        </a>
                    </div>
                @endif
            </div>
                    </div>
                </div>

    <!-- Tasks Section -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="bx bx-task me-2"></i>
                    Tasks {{ $selectedFolder ? 'in "' . $selectedFolder->name . '" and subfolders' : '(All project folders)' }}
                    @if($selectedFolder)
                        <small class="text-muted">({{ count($descendantFolderIds) }} folders included)</small>
                    @endif
                </h5>
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#tasksSection" aria-expanded="true" aria-controls="tasksSection">
                    <i class="bx bx-chevron-down" id="tasksToggleIcon"></i>
                </button>
            </div>
        </div>
        <div class="collapse show" id="tasksSection">
            <div class="card-body p-0">
                @if($tasks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Title</th>
                                    <th class="border-0">Folder</th>
                                    <th class="border-0">Assigned</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Priority</th>
                                    <th class="border-0">Due Date</th>
                                    <th class="border-0 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tasks as $task)
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
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
                                        <td class="align-middle">
                                            <span class="badge bg-label-info">
                                                <i class="bx bx-folder me-1"></i>{{ $task->folder?->name ?? 'No Folder' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($task->creator?->name ?? 'U', 0, 1) }}</span>
                                                </div>
                                                <span>{{ $task->creator?->name ?? 'Unassigned' }}</span>
                </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge {{ $statusColors[$task->status] ?? 'bg-label-secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge {{ $priorityColors[$task->priority ?? 5] ?? 'bg-secondary' }}">
                                                {{ $task->priority ?? 5 }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            @if($task->due_date)
                                                <span class="{{ $task->due_date < now() ? 'text-danger' : 'text-muted' }}">
                                                    {{ $task->due_date->format('M d, Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="bx bxs-show"></i>
                                                </a>
                                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this task?')">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                    </form>
                                            </div>
                                </td>
                            </tr>
                                @endforeach
                        </tbody>
                    </table>
                </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div>
                    {{ $tasks->withQueryString()->links() }}
                </div>
                        @if(Auth::user()->isManager())
                        <a href="{{ route('tasks.create', ['project_id' => $project->id, 'folder_id' => $selectedFolder?->id]) }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i>Add Task
                        </a>
                        @endif
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bx bx-task" style="font-size: 4rem; color: #d1d5db;"></i>
                        </div>
                        <h5 class="text-muted mb-2">No tasks found</h5>
                        <p class="text-muted mb-4">Create your first task to get started</p>
                        @if(Auth::user()->isManager())
                        <a href="{{ route('tasks.create', ['project_id' => $project->id, 'folder_id' => $selectedFolder?->id]) }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i>Create Task
                        </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Folder Cards Styling */
.folder-card {
    border-radius: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.folder-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    border-color: #d1d5db !important;
}

.folder-card .folder-icon {
    transition: transform 0.3s ease;
}

.folder-card:hover .folder-icon {
    transform: scale(1.1);
}

/* Task Table Styling */
.table-hover tbody tr:hover {
    background-color: #f8f9fb;
}

.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 50%;
}

.avatar-sm {
    width: 1.5rem;
    height: 1.5rem;
    font-size: 0.75rem;
}

.avatar-initial {
    background-color: #e9ecef;
    color: #6c757d;
}

/* Collapse Animation */
.collapse {
    transition: height 0.35s ease;
}

/* Responsive Design */
@media (max-width: 768px) {
    .folder-card .card-body {
        padding: 1rem !important;
    }

    .folder-card .folder-icon {
        width: 40px !important;
        height: 40px !important;
    }

    .table-responsive {
        font-size: 0.875rem;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .folder-card .row.g-2 {
        margin: 0 -0.25rem;
    }

    .folder-card .col-6 {
        padding: 0 0.25rem;
    }

    .table th,
    .table td {
        padding: 0.5rem 0.25rem;
    }
}
</style>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Collapse/Expand functionality for sections
    const foldersToggle = document.getElementById('foldersSection');
    const tasksToggle = document.getElementById('tasksSection');
    const foldersIcon = document.getElementById('foldersToggleIcon');
    const tasksIcon = document.getElementById('tasksToggleIcon');

    // Folders section toggle
    foldersToggle.addEventListener('show.bs.collapse', function() {
        foldersIcon.classList.remove('bx-chevron-right');
        foldersIcon.classList.add('bx-chevron-down');
    });

    foldersToggle.addEventListener('hide.bs.collapse', function() {
        foldersIcon.classList.remove('bx-chevron-down');
        foldersIcon.classList.add('bx-chevron-right');
    });

    // Tasks section toggle
    tasksToggle.addEventListener('show.bs.collapse', function() {
        tasksIcon.classList.remove('bx-chevron-right');
        tasksIcon.classList.add('bx-chevron-down');
    });

    tasksToggle.addEventListener('hide.bs.collapse', function() {
        tasksIcon.classList.remove('bx-chevron-down');
        tasksIcon.classList.add('bx-chevron-right');
    });

    // Initialize icons based on current state
    if (foldersToggle.classList.contains('show')) {
        foldersIcon.classList.add('bx-chevron-down');
    } else {
        foldersIcon.classList.add('bx-chevron-right');
    }

    if (tasksToggle.classList.contains('show')) {
        tasksIcon.classList.add('bx-chevron-down');
    } else {
        tasksIcon.classList.add('bx-chevron-right');
    }
});
    </script>
@endsection
