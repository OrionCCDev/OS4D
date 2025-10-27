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
            ['title' => 'Projects', 'url' => route('user.projects.index'), 'icon' => 'bx bx-folder'],
            ['title' => $project->name, 'url' => '#', 'icon' => 'bx bx-folder-open']
        ]"
    />


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
                        <a href="{{ route('user.projects.show', ['project' => $project->id]) }}" class="text-decoration-none">
                            <i class="bx bx-home me-1"></i>All Folders
                        </a>
                    </li>
                    @foreach($breadcrumbs as $crumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            <a href="{{ route('user.projects.show', ['project' => $project->id, 'folder' => $crumb->id]) }}" class="text-decoration-none {{ $loop->last ? 'active' : '' }}">
                                {{ $crumb->name }}
                            </a>
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
                    {{ $selectedFolder ? 'Subfolders in "' . $selectedFolder->name . '"' : 'Project Folders' }}
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#foldersSection" aria-expanded="true" aria-controls="foldersSection">
                        <i class="bx bx-chevron-down" id="foldersToggleIcon"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="collapse show" id="foldersSection">
            <div class="card-body">

                @if($selectedFolder && $selectedFolder->children->count() > 0)
                    <div class="row" id="foldersGrid">
                        @foreach($selectedFolder->children as $folder)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card folder-card h-100 position-relative overflow-hidden" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e5e7eb; cursor: pointer;" onclick="window.location.href='{{ route('user.projects.show', ['project' => $project->id, 'folder' => $folder->id]) }}'">
                                    <!-- Colorful gradient background -->
                                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>

                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="folder-icon me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bx bx-folder text-white" style="font-size: 24px;"></i>
                                            </div>
                                            <div class="flex-grow-1" style="min-width: 0; word-wrap: break-word;">
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

                                        <div class="d-flex justify-content-start align-items-center">
                                            <span class="badge bg-label-primary">
                                                <i class="bx bx-folder me-1"></i>Open
                                            </span>
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
                                <div class="card folder-card h-100 position-relative overflow-hidden" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e5e7eb; cursor: pointer;" onclick="window.location.href='{{ route('user.projects.show', ['project' => $project->id, 'folder' => $folder->id]) }}'">
                                    <!-- Colorful gradient background -->
                                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>

                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="folder-icon me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bx bx-folder text-white" style="font-size: 24px;"></i>
                                            </div>
                                            <div class="flex-grow-1" style="min-width: 0; word-wrap: break-word;">
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

                                        <div class="d-flex justify-content-start align-items-center">
                                            <span class="badge bg-label-primary">
                                                <i class="bx bx-folder me-1"></i>Open
                                            </span>
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
                        <p class="text-muted mb-4">No folders exist in this project yet</p>
                    </div>
                @endif
            </div>
                    </div>
                </div>

    <!-- Project Files Section - Shows all files from all folders -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="bx bx-file me-2"></i>
                    Project Files (From All Folders)
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filesSection" aria-expanded="true" aria-controls="filesSection">
                        <i class="bx bx-chevron-down" id="filesToggleIcon"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="collapse show" id="filesSection">
            <div class="card-body">
                <!-- Files Container for all files -->
                <div id="allFilesContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading files...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Section -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="bx bx-task me-2"></i>
                    My Tasks {{ $selectedFolder ? 'in "' . $selectedFolder->name . '" and subfolders' : '(All project folders)' }}
                    @if($selectedFolder)
                        <small class="text-muted">({{ count($descendantFolderIds) }} folders included)</small>
                    @endif
                </h5>
                <div class="d-flex gap-2">
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
                                            </div>
                                        </td>
                            </tr>
                                @endforeach
                        </tbody>
                    </table>
                </div>
                    <div class="card-footer">
                        {{ $tasks->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bx bx-task" style="font-size: 4rem; color: #d1d5db;"></i>
                        </div>
                        <h5 class="text-muted mb-2">No tasks found</h5>
                        <p class="text-muted mb-4">You don't have any tasks in this {{ $selectedFolder ? 'folder' : 'project' }}</p>
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

/* File Card Hover Effects */
.file-card-gradient {
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.file-card-gradient:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
}

.file-shine-overlay {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg,
        transparent,
        rgba(255, 255, 255, 0.4) 50%,
        transparent);
    transition: left 0.5s;
    pointer-events: none;
    z-index: 2;
}

.file-card-gradient:hover .file-shine-overlay {
    left: 100%;
}

.file-card-gradient:hover .avatar-initial {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.file-card-gradient .avatar-initial {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
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

    // Files section toggle
    const filesToggle = document.getElementById('filesSection');
    const filesIcon = document.getElementById('filesToggleIcon');

    console.log('Checking filesToggle and filesIcon:', filesToggle, filesIcon);

    if (filesToggle && filesIcon) {
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
    }

    // Load files on page load - check if allFilesContainer exists
    const filesContainer = document.getElementById('allFilesContainer');
    console.log('allFilesContainer element:', filesContainer);

    if (filesContainer) {
        console.log('DOMContentLoaded: Loading files section for project {{ $project->id }}');
        if (typeof loadFiles === 'function') {
            loadFiles();
        } else {
            console.error('loadFiles function not defined!');
        }
    } else {
        console.error('allFilesContainer element not found!');
    }
});

// Function to reload files after upload
function reloadFiles() {
    console.log('reloadFiles() called');
    loadFiles();
}

// File Management Functions
function loadFiles() {
    console.log('=== loadFiles() STARTED ===');
    const projectId = {{ $project->id }};
    const container = document.getElementById('allFilesContainer');
    console.log('Project ID:', projectId);
    console.log('Container:', container);

    // Show loading spinner
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading files...</span></div></div>';

    // Load files from all folders (no folder parameter)
    fetch(`/user/projects/${projectId}/files`, {
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
    const container = document.getElementById('allFilesContainer');

    if (!container) {
        console.error('filesContainer element not found!');
        return;
    }

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

    console.log('Displaying', files.length, 'files to container');

    let html = '<h6 class="mb-3"><i class="bx bx-file me-2"></i>Files (' + files.length + ')</h6><div class="row g-3 mb-4">';
    files.forEach(file => {
        console.log('Processing file:', file.id, file.display_name || file.original_name);
        const iconClass = getFileIconClass(file.mime_type || '');
        const displayName = file.display_name || file.original_name || 'Unknown file';
        const fileSize = file.human_readable_size || '0 B';
        const fileUrl = `/user/projects/${projectId}/files/${file.id}/download`;
        const uploaderName = (file.uploader && file.uploader.name) || 'Unknown';
        const description = file.description || '';

        html += `
            <div class="col-md-6 col-lg-4" data-file-id="${file.id}">
                <div class="card h-100 border-0 shadow-sm file-card-gradient" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); position: relative; overflow: hidden; transition: all 0.3s ease;">
                    <div class="file-shine-overlay"></div>
                    <div class="card-body" style="position: relative; z-index: 1;">
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md">
                                    <div class="avatar-initial bg-white shadow-sm rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <i class="${iconClass} text-white"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3" style="min-width: 0;">
                                <h6 class="mb-1 text-truncate" title="${displayName}">${displayName}</h6>
                                <p class="text-muted small mb-0">${fileSize}</p>
                            </div>
                        </div>
                        ${description ? `<p class="small text-muted mb-2">${description}</p>` : ''}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <i class="bx bx-user me-1"></i>` + uploaderName + `
                            </small>
                            <a href="${fileUrl}" download="${displayName}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-download me-1"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    console.log('Setting container.innerHTML, length:', html.length);
    container.innerHTML = html;
    console.log('Container updated, check if files are visible now');
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

    </script>
@endsection
