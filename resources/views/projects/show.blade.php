@extends('layouts.app')

{{-- Using standardized Task model color methods --}}

@section('content')
<div class="container flex-grow-1 container-p-y">
    <!-- Project Header -->
    <x-modern-breadcrumb
        title="{{ $project->name }}"
        subtitle="Status: {{ ucfirst(str_replace('_',' ', $project->status)) }}"
        icon="bx bx-folder-open"
        theme="projects"
        :breadcrumbs="[
            ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['title' => 'Projects', 'url' => route('projects.index'), 'icon' => 'bx bx-folder'],
            ['title' => $project->name, 'url' => '#', 'icon' => 'bx bx-folder-open']
        ]"
    />

    <!-- Project Actions -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1">Project Actions</h6>
                    <small class="text-muted">Export comprehensive project report with all data, tasks, and history</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('reports.projects.full-report', $project) }}" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i>Export Full Report
                    </a>
                    <a href="{{ route('reports.projects.progress', ['project_id' => $project->id]) }}" class="btn btn-outline-primary">
                        <i class="bx bx-chart me-1"></i>View Progress Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Contractors Section -->
    @if($project->contractors->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="bx bx-user me-2"></i>
                    Project Contractors
                </h5>
                <span class="badge bg-primary">{{ $project->contractors->count() }} assigned</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($project->contractors as $contractor)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar" style="width: 40px; height: 40px; background: {{ $contractor->type === 'orion staff' ? '#0d6efd' : ($contractor->type === 'client' ? '#198754' : ($contractor->type === 'other' ? '#6c757d' : '#0dcaf0')) }}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bx bx-user text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $contractor->name }}</h6>
                                <small class="text-muted">{{ $contractor->email }}</small>
                                @if($contractor->company_name)
                                    <br><small class="text-muted">{{ $contractor->company_name }}</small>
                                @endif
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge bg-{{ $contractor->type === 'orion staff' ? 'primary' : ($contractor->type === 'client' ? 'success' : ($contractor->type === 'other' ? 'secondary' : 'info')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $contractor->type)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

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

    <!-- Folders and Files Section -->
    <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="bx bx-folder me-2"></i>
                    {{ $selectedFolder ? 'Subfolders in "' . $selectedFolder->name . '"' : 'Project Folders & Files' }}
                </h5>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-primary" href="{{ route('folders.create', ['project_id' => $project->id, 'parent_id' => $selectedFolder?->id]) }}">
                        <i class="bx bx-folder-plus me-1"></i>Add Folder
                    </a>
                    @if(Auth::user()->isManager())
                    <button class="btn btn-sm btn-primary" onclick="openFileUploadModal()">
                        <i class="bx bx-upload me-1"></i>Upload File
                    </button>
                    @endif
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#foldersSection" aria-expanded="true" aria-controls="foldersSection">
                        <i class="bx bx-chevron-down" id="foldersToggleIcon"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="collapse show" id="foldersSection">
            <div class="card-body">
                <!-- Files Container -->
                <div id="filesContainer" class="mb-4">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading files...</span>
                        </div>
                    </div>
                </div>

                @if($selectedFolder && $selectedFolder->children->count() > 0)
                    <div class="row" id="foldersGrid">
                        @foreach($selectedFolder->children as $folder)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card folder-card h-100 position-relative overflow-hidden" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e5e7eb; cursor: pointer;" onclick="window.location.href='{{ route('projects.show', ['project' => $project->id, 'folder' => $folder->id]) }}'">
                                    <!-- Colorful gradient background -->
                                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>

                                    <!-- Edit and Delete Icons -->
                                    <div class="position-absolute top-0 end-0 p-2" style="z-index: 10;">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('folders.edit', $folder->id) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               onclick="event.stopPropagation();"
                                               title="Edit Folder">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="event.stopPropagation(); confirmDeleteFolder('{{ $folder->id }}', '{{ addslashes($folder->name) }}');"
                                                    title="Delete Folder">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="folder-icon me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bx bx-folder text-white" style="font-size: 24px;"></i>
                                            </div>
                                            <div class="flex-grow-1" style="min-width: 0; padding-right: 90px; word-wrap: break-word;">
                                                <h6 class="mb-1 fw-semibold" title="{{ $folder->name }}" style="overflow-wrap: break-word; word-break: break-word; line-height: 1.4;">{{ $folder->name }}</h6>
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
                    <div class="row" id="foldersGrid">
                        @foreach($rootFolders as $folder)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card folder-card h-100 position-relative overflow-hidden" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e5e7eb; cursor: pointer;" onclick="window.location.href='{{ route('projects.show', ['project' => $project->id, 'folder' => $folder->id]) }}'">
                                    <!-- Colorful gradient background -->
                                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>

                                    <!-- Edit and Delete Icons -->
                                    <div class="position-absolute top-0 end-0 p-2" style="z-index: 10;">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('folders.edit', $folder->id) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               onclick="event.stopPropagation();"
                                               title="Edit Folder">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="event.stopPropagation(); confirmDeleteFolder('{{ $folder->id }}', '{{ addslashes($folder->name) }}');"
                                                    title="Delete Folder">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="folder-icon me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bx bx-folder text-white" style="font-size: 24px;"></i>
                                            </div>
                                            <div class="flex-grow-1" style="min-width: 0; padding-right: 90px; word-wrap: break-word;">
                                                <h6 class="mb-1 fw-semibold" title="{{ $folder->name }}" style="overflow-wrap: break-word; word-break: break-word; line-height: 1.4;">{{ $folder->name }}</h6>
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
                @endif

                <!-- Empty state for folders only shown if no folders exist -->
                @php
                    $hasFolders = ($selectedFolder && $selectedFolder->children->count() > 0) || (!$selectedFolder && $rootFolders->count() > 0);
                @endphp

                @if(!$hasFolders)
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

    <!-- File Upload Modal -->
    @if(Auth::user()->isManager())
    <div class="modal fade" id="fileUploadModal" tabindex="-1" aria-labelledby="fileUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileUploadModalLabel">
                        <i class="bx bx-upload me-2"></i>Upload File
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="fileUploadForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <div class="mb-3">
                            <label for="fileInput" class="form-label">File</label>
                            <input type="file" class="form-control" id="fileInput" name="file" required>
                            <small class="text-muted">Maximum file size: 100MB</small>
                        </div>
                        <div class="mb-3">
                            <label for="displayNameInput" class="form-label">Display Name <span class="text-muted">(optional)</span></label>
                            <input type="text" class="form-control" id="displayNameInput" name="display_name" placeholder="Leave empty to use original name">
                        </div>
                        <div class="mb-3">
                            <label for="fileDescriptionInput" class="form-label">Description <span class="text-muted">(optional)</span></label>
                            <textarea class="form-control" id="fileDescriptionInput" name="description" rows="3" placeholder="Add a description for this file"></textarea>
                        </div>
                        <input type="hidden" name="folder_id" id="currentFolderId" value="{{ $selectedFolder?->id }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-upload me-1"></i>Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit File Modal -->
    <div class="modal fade" id="editFileModal" tabindex="-1" aria-labelledby="editFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFileModalLabel">
                        <i class="bx bx-edit me-2"></i>Edit File
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editFileForm">
                    <div class="modal-body">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="editDisplayNameInput" class="form-label">Display Name</label>
                            <input type="text" class="form-control" id="editDisplayNameInput" name="display_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editFileDescriptionInput" class="form-label">Description</label>
                            <textarea class="form-control" id="editFileDescriptionInput" name="description" rows="3"></textarea>
                        </div>
                        <input type="hidden" id="editFileId" name="file_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

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
                <div class="d-flex gap-2">
                    @if(Auth::user()->isManager())
                    <a href="{{ route('tasks.create', ['project_id' => $project->id, 'folder_id' => $selectedFolder?->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-plus me-1"></i>Add New Task
                    </a>
                    @endif
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#tasksSection" aria-expanded="true" aria-controls="tasksSection">
                        <i class="bx bx-chevron-down" id="tasksToggleIcon"></i>
                    </button>
                </div>
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
                                                    <div class="rounded-circle {{ $task->priority_badge_class }}" style="width: 8px; height: 8px;"></div>
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
                                                <i class="bx bx-folder me-1"></i>{{ $task->folder?->name ?? 'Main Folder' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($task->assignee?->name ?? 'U', 0, 1) }}</span>
                                                </div>
                                                <span>{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge {{ $task->status_badge_class }} text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 600;">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge {{ $task->priority_badge_class }} text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 600;">
                                                {{ ucfirst($task->priority ?? 'Normal') }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            @if($task->due_date)
                                                <span class="{{ $task->is_overdue ? 'text-danger' : 'text-muted' }}">
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
                                                <a href="{{ route('tasks.edit', ['task' => $task, 'redirect_to' => 'project.folder', 'folder_id' => $selectedFolder ? $selectedFolder->id : null]) }}" class="btn btn-sm btn-outline-secondary">
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
                    {{ $tasks->withQueryString()->links('vendor.pagination.bootstrap-5') }}
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

    .folder-card .flex-grow-1 {
        padding-right: 110px !important;
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

    .folder-card .flex-grow-1 {
        padding-right: 120px !important;
    }

    .table th,
    .table td {
        padding: 0.5rem 0.25rem;
    }
}

/* Pagination fixes */
.pagination .page-link {
    font-size: 14px !important;
    padding: 0.5rem 0.75rem !important;
}

.pagination .page-link i {
    font-size: 14px !important;
    line-height: 1 !important;
}

.pagination .page-item.active .page-link {
    background-color: #696cff !important;
    border-color: #696cff !important;
    color: white !important;
}

.pagination .page-link:hover {
    background-color: #e1e4e8 !important;
    border-color: rgba(67, 89, 113, 0.3) !important;
}
</style>

    <!-- Delete Folder Confirmation Modal -->
    <div class="modal fade" id="deleteFolderModal" tabindex="-1" aria-labelledby="deleteFolderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteFolderModalLabel">
                        <i class="bx bx-error-circle text-danger me-2"></i>Confirm Folder Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Warning:</strong> This action cannot be undone!
                    </div>
                    <p>You are about to delete the folder <strong id="folderNameToDelete"></strong>.</p>
                    <p>This will permanently delete:</p>
                    <ul class="list-unstyled">
                        <li><i class="bx bx-folder text-warning me-2"></i>The folder and all its subfolders</li>
                        <li><i class="bx bx-task text-primary me-2"></i>All tasks within this folder</li>
                        <li><i class="bx bx-folder-open text-secondary me-2"></i>Associated files and directories</li>
                    </ul>
                    <p class="text-muted small">All data associated with this folder will be permanently removed from the system.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <form id="deleteFolderForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bx bx-trash me-1"></i>Delete Folder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
// Global manager status check
const isManager = {{ Auth::user()->isManager() ? 'true' : 'false' }};

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

    // Files section toggle
    const filesToggle = document.getElementById('filesSection');
    const filesIcon = document.getElementById('filesToggleIcon');

    if (filesToggle) {
        filesToggle.addEventListener('show.bs.collapse', function() {
            filesIcon.classList.remove('bx-chevron-right');
            filesIcon.classList.add('bx-chevron-down');
        });

        filesToggle.addEventListener('hide.bs.collapse', function() {
            filesIcon.classList.remove('bx-chevron-down');
            filesIcon.classList.add('bx-chevron-right');
        });

        if (filesToggle.classList.contains('show')) {
            filesIcon.classList.add('bx-chevron-down');
        } else {
            filesIcon.classList.add('bx-chevron-right');
        }

        // Load files on page load
        console.log('Page loaded, calling loadFiles()');
        loadFiles();
    }
});

// Function to reload files after upload
function reloadFiles() {
    console.log('reloadFiles() called');
    loadFiles();
}

// Folder deletion confirmation function
function confirmDeleteFolder(folderId, folderName) {
    document.getElementById('folderNameToDelete').textContent = folderName;
    const form = document.getElementById('deleteFolderForm');
    form.action = `/folders/${folderId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteFolderModal'));
    modal.show();
}

// File Management Functions
function loadFiles() {
    const projectId = {{ $project->id }};
    const folderId = {{ $selectedFolder?->id ?? 'null' }};
    const container = document.getElementById('filesContainer');

    // Show loading spinner
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading files...</span></div></div>';

    fetch(`/projects/${projectId}/files?folder=${folderId ? folderId : ''}`, {
        method: 'GET',
        credentials: 'include', // Include cookies for auth
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error('Failed to load files: ' + text);
                });
            }
            return response.json();
        })
        .then(files => {
            console.log('Files loaded successfully:', files);
            console.log('Files count:', Array.isArray(files) ? files.length : 'Not an array');
            displayFiles(files);
        })
        .catch(error => {
            console.error('Error loading files:', error);
            container.innerHTML = '<p class="text-muted text-center">Failed to load files: ' + error.message + '</p>';
        });
}

function displayFiles(files) {
    console.log('displayFiles() called with:', files);
    const container = document.getElementById('filesContainer');

    if (!Array.isArray(files)) {
        console.error('Files is not an array:', typeof files, files);
        container.innerHTML = '<p class="text-danger text-center">Invalid response format</p>';
        return;
    }

    if (files.length === 0) {
        console.log('No files to display');
        container.innerHTML = '';
        return;
    }

    console.log('Displaying', files.length, 'files');

    let html = '<h6 class="mb-3"><i class="bx bx-file me-2"></i>Files (' + files.length + ')</h6><div class="row g-3 mb-4">';
    files.forEach(file => {
        const iconClass = getFileIconClass(file.mime_type);
        html += `
            <div class="col-md-6 col-lg-4" data-file-id="${file.id}">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md">
                                    <div class="avatar-initial bg-label-primary rounded">
                                        <i class="${iconClass}"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3" style="min-width: 0;">
                                <h6 class="mb-1 text-truncate" title="${file.display_name}">${file.display_name || file.original_name}</h6>
                                <p class="text-muted small mb-0">${file.human_readable_size}</p>
                            </div>
                        </div>
                        ${file.description ? `<p class="small text-muted mb-2">${file.description}</p>` : ''}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <i class="bx bx-user me-1"></i>${file.uploader?.name || 'Unknown'}
                            </small>
                            ${isManager ? `
                            <div class="btn-group btn-group-sm">
                                <a href="${file.url}" target="_blank" class="btn btn-outline-primary" title="Download">
                                    <i class="bx bx-download"></i>
                                </a>
                                <button onclick="openEditFileModal(${file.id}, '${(file.display_name || file.original_name).replace(/'/g, "\\'")}', '${(file.description || '').replace(/'/g, "\\'")}')" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button onclick="confirmDeleteFile(${file.id}, '${(file.display_name || file.original_name).replace(/'/g, "\\'")}')" class="btn btn-outline-danger" title="Delete">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>` : `
                            <a href="${file.url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-download me-1"></i>Download
                            </a>`}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function getFileIconClass(mimeType) {
    if (!mimeType) return 'bx bx-file';

    if (mimeType.includes('pdf')) return 'bx bxs-file-pdf';
    if (mimeType.includes('image')) return 'bx bxs-file-image';
    if (mimeType.includes('word') || mimeType.includes('document')) return 'bx bxs-file-doc';
    if (mimeType.includes('sheet') || mimeType.includes('excel')) return 'bx bxs-file-blank';
    if (mimeType.includes('zip') || mimeType.includes('archive')) return 'bx bxs-file-archive';
    if (mimeType.includes('video')) return 'bx bxs-file';
    if (mimeType.includes('text')) return 'bx bxs-file-txt';

    return 'bx bx-file';
}

function openFileUploadModal() {
    const modal = new bootstrap.Modal(document.getElementById('fileUploadModal'));
    modal.show();
}

function openEditFileModal(fileId, displayName, description) {
    document.getElementById('editFileId').value = fileId;
    document.getElementById('editDisplayNameInput').value = displayName;
    document.getElementById('editFileDescriptionInput').value = description;
    const modal = new bootstrap.Modal(document.getElementById('editFileModal'));
    modal.show();
}

function confirmDeleteFile(fileId, fileName) {
    if (confirm(`Are you sure you want to delete "${fileName}"?`)) {
        deleteFile(fileId);
    }
}

function deleteFile(fileId) {
    const projectId = {{ $project->id }};

    fetch(`/projects/${projectId}/files/${fileId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadFiles();
            alert('File deleted successfully');
        } else {
            alert('Error deleting file');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting file');
    });
}

// File upload form handler
document.getElementById('fileUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const projectId = {{ $project->id }};
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';

    fetch(`/projects/${projectId}/files`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Server error:', text);
                throw new Error('Upload failed: ' + response.statusText);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Upload response:', data);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('fileUploadModal')).hide();
            this.reset();
            loadFiles();
            alert('File uploaded successfully');
        } else {
            alert('Error uploading file: ' + (data.error || data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Error uploading file: ' + error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Edit file form handler
document.getElementById('editFileForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const fileId = document.getElementById('editFileId').value;
    const projectId = {{ $project->id }};
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    const formData = {
        display_name: document.getElementById('editDisplayNameInput').value,
        description: document.getElementById('editFileDescriptionInput').value
    };

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    fetch(`/projects/${projectId}/files/${fileId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editFileModal')).hide();
            loadFiles();
            alert('File updated successfully');
        } else {
            alert('Error updating file');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating file');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
    </script>
@endsection
