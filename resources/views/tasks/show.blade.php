@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <x-modern-breadcrumb title="{{ $task->title }}" subtitle="Task details and management" icon="bx bx-task"
                theme="tasks" :breadcrumbs="[
                    ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                    ['title' => 'Tasks', 'url' => route('tasks.index'), 'icon' => 'bx bx-list-ul'],
                    ['title' => Str::limit($task->title, 30), 'url' => '#', 'icon' => 'bx bx-task']
                ]" />
            <div class="d-flex align-items-center gap-3 flex-wrap">
                @php
                $statusColors = [
                'pending' => 'secondary',
                'assigned' => 'info',
                'accepted' => 'primary',
                'in_progress' => 'warning',
                'workingon' => 'warning',
                'submitted_for_review' => 'primary',
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
                    @if($task->status === 'submitted_for_review')
                    For Review
                    @else
                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    @endif
                </span>
                <span class="badge bg-{{ $priorityColors[$task->priority ?? 5] ?? 'secondary' }} fs-6 px-3 py-2">
                    Priority {{ $task->priority ?? 5 }}
                </span>
                @if($task->start_date)
                @php
                $now = now()->startOfDay();
                $startDate = \Carbon\Carbon::parse($task->start_date)->startOfDay();
                $daysUntilStart = $now->diffInDays($startDate, false);
                $isAccepted = in_array($task->status, ['accepted', 'in_progress', 'workingon', 'submitted_for_review',
                'in_review', 'approved', 'completed']);
                $isApproaching = $daysUntilStart <= 3 && $daysUntilStart>= 0;
                    $isOverdue = $daysUntilStart < 0; // Only show overdue to start if: // 1. Task is not accepted/in
                        progress AND // 2. Start date has passed AND // 3. Task is assigned (not pending/unassigned)
                        $shouldShowOverdue=$isOverdue && !$isAccepted && $task->status === 'assigned';
                        @endphp

                        @if($shouldShowOverdue)
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bx bx-time-five me-1"></i>
                            {{ abs($daysUntilStart) }} days overdue to start
                        </span>
                        @elseif($isAccepted && $daysUntilStart <= 0) {{-- Don't show anything if task is accepted and
                            start date has passed --}} @elseif($daysUntilStart>= 0)
                            <span
                                class="badge bg-{{ $isApproaching && !$isAccepted ? 'warning' : 'info' }} fs-6 px-3 py-2">
                                <i class="bx bx-timer me-1"></i>
                                @if($daysUntilStart == 0)
                                Starts today
                                @elseif($daysUntilStart == 1)
                                Starts tomorrow
                                @else
                                {{ $daysUntilStart }} days until start
                                @endif
                            </span>
                            @endif
                            @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            @if(Auth::user()->isManager() || ($task->status !== 'submitted_for_review' && $task->status !== 'in_review'
            && $task->status !== 'approved' && $task->status !== 'completed'))
            <a href="{{ route('tasks.edit', ['task' => $task, 'redirect_to' => 'project.show']) }}"
                class="btn btn-primary">
                <i class="bx bx-edit me-1"></i>Edit Task
            </a>
            @if(Auth::user()->canDelete())
            <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this task?')" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">
                    <i class="bx bx-trash me-1"></i>Delete
                </button>
            </form>
            @elseif(Auth::user()->isSubAdmin())
            @include('partials.delete-request-button', [
            'type' => 'task',
            'id' => $task->id,
            'label' => $task->title,
            'class' => 'btn btn-outline-danger',
            'icon' => 'bx bx-trash',
            'text' => 'Request Delete'
            ])
            @endif
            @endif
            {{-- Send Free Mail button hidden as requested --}}
            {{-- @if(Auth::user()->id === $task->assigned_to || Auth::user()->isManager())
            <a href="{{ route('tasks.free-mail', $task) }}" class="btn btn-outline-primary">
                <i class="bx bx-mail-send me-1"></i>Send Free Mail
            </a>
            @endif --}}
        </div>
    </div>
</div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bx bx-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bx bx-error me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($task->status === 'submitted_for_review' && !Auth::user()->isManager())
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="bx bx-lock-alt me-3" style="font-size: 2rem;"></i>
                <div>
                    <h6 class="mb-1"><strong>Task Under Review</strong></h6>
                    <p class="mb-0">This task has been submitted for review. Only managers can edit, delete, upload
                        files, or change the status until the review is complete.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<div class="container">


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
                                    <p class="mb-0 fw-semibold">{{ $task->folder?->name ?? 'Main Folder' }}</p>
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
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{
                                            substr($task->creator?->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <small class="text-muted">Created by</small>
                                        <p class="mb-0 fw-semibold">{{ $task->creator?->name ?? 'Unknown' }}</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        @if($task->assignee)
                                        <span class="avatar-initial rounded-circle bg-label-success">{{
                                            substr($task->assignee->name, 0, 1) }}</span>
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
                            @if($task->start_date)
                            <div class="mb-2">
                                <small class="text-muted">Start Date</small>
                                <p class="mb-0 fw-semibold">{{ $task->start_date->format('M d, Y') }}</p>
                            </div>
                            @endif
                            @if($task->due_date)
                            <div class="mb-2">
                                <small class="text-muted">Due Date</small>
                                <p class="mb-0 fw-semibold {{ $task->is_overdue ? 'text-danger' : '' }}">
                                    {{ $task->due_date->format('M d, Y') }}
                                </p>
                            </div>
                            @endif
                            @if($task->start_date && $task->due_date)
                            <div class="mb-2">
                                <small class="text-muted">Planned Duration</small>
                                <p class="mb-0 fw-semibold text-info">
                                    @php
                                    $startDate = \Carbon\Carbon::parse($task->start_date)->startOfDay();
                                    $dueDate = \Carbon\Carbon::parse($task->due_date)->startOfDay();
                                    $taskDuration = $startDate->diffInDays($dueDate);
                                    @endphp
                                    @if($taskDuration == 0)
                                    Same day
                                    @elseif($taskDuration == 1)
                                    1 day
                                    @else
                                    {{ $taskDuration }} days
                                    @endif
                                </p>
                            </div>
                            @endif
                            @if($task->start_date && $task->completed_at)
                            <div class="mb-2">
                                <small class="text-muted">Actual Duration</small>
                                <p class="mb-0 fw-semibold text-success">{{ $task->actual_duration_formatted }}</p>
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
            <div class="card mb-4 border-0 shadow-lg"
                style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div
                        class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
                        <h4 class="mb-0 fw-bold text-dark" style="font-size: 1.75rem;">
                            <i class="bx bx-folder-open me-2 text-primary"></i>Task Files
                        </h4>
                        @if(Auth::user()->isManager() || ($task->status !== 'submitted_for_review' && $task->status !==
                        'in_review' && $task->status !== 'approved' && $task->status !== 'completed'))
                        <div class="upload-section" id="uploadSection">
                            <form action="{{ route('tasks.attachments.upload', $task) }}" method="POST"
                                enctype="multipart/form-data" id="uploadForm">
                                @csrf
                                <div class="upload-area" id="uploadArea">
                                    <div class="upload-content">
                                        <div class="upload-icon">
                                            <i class="bx bx-cloud-upload"></i>
                                        </div>
                                        <h6 class="upload-title">Drop files here or click to browse</h6>
                                        <p class="upload-subtitle">Supports multiple files • All file types • Max 50MB
                                            per file</p>
                                    </div>
                                    <input type="file" name="files[]" id="fileInput" class="form-control d-none"
                                        accept="*/*" multiple required>
                                </div>
                                <div class="upload-actions mt-3" id="uploadActions" style="display: none;">
                                    <div
                                        class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                                        <div class="file-info-display d-flex align-items-center">
                                            <i class="bx bx-file me-2"></i>
                                            <span class="file-name text-primary fw-bold">No files chosen</span>
                                            <small class="file-size text-muted ms-2"></small>
                                        </div>
                                        <div class="upload-buttons d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                id="changeFile">
                                                <i class="bx bx-refresh me-1"></i>Change
                                            </button>
                                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                                <i class="bx bx-upload me-1"></i>Upload
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        @else
                        <div class="alert alert-warning alert-sm mb-0 py-2"
                            style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 1px solid #ffc107;">
                            <i class="bx bx-lock me-1"></i>
                            <small><strong>File uploads disabled</strong> - Task is under review. Only managers can
                                upload files.</small>
                        </div>
                        @endif
                    </div>
                    @if($task->attachments->count())
                    <div class="mb-4">
                        <div class="input-group search-container" style="max-width: 450px;">
                            <span class="input-group-text border-end-0"
                                style="border: 2px solid #e2e8f0; border-radius: 12px 0 0 12px;">
                                <i class="bx bx-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="fileSearch"
                                placeholder="Search files by name..."
                                style="border: 2px solid #e2e8f0; border-radius: 0 12px 12px 0; padding: 14px 16px; font-size: 0.95rem;">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="clearSearch"
                                style="border: 2px solid #e2e8f0; border-left: none; border-radius: 0 12px 12px 0; display: none;">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                        <div class="search-results-info mt-2" id="searchResultsInfo" style="display: none;">
                            <small class="text-muted">
                                <span id="resultsCount">0</span> files found
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-body pt-0">
                    @if($task->attachments->count())
                    <div class="files-grid" id="filesContainer">
                        @foreach($task->attachments->sortByDesc('created_at') as $att)
                        @php
                        $isRecent = $att->created_at->diffInHours(now()) < 24; $isOwnFile=$att->uploaded_by ===
                            Auth::id();
                            $fileExtension = strtolower(pathinfo($att->original_name, PATHINFO_EXTENSION));
                            $fileIcon = 'bx-file';
                            $fileTypeClass = 'file';

                            if (in_array($fileExtension, ['pdf'])) {
                            $fileIcon = 'bx-file-blank';
                            $fileTypeClass = 'pdf';
                            } elseif (in_array($fileExtension, ['doc', 'docx'])) {
                            $fileIcon = 'bx-file-doc';
                            $fileTypeClass = 'doc';
                            } elseif (in_array($fileExtension, ['xls', 'xlsx'])) {
                            $fileIcon = 'bx-file-spreadsheet';
                            $fileTypeClass = 'xls';
                            } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'])) {
                            $fileIcon = 'bx-image';
                            $fileTypeClass = 'image';
                            } elseif (in_array($fileExtension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                            $fileIcon = 'bx-archive';
                            $fileTypeClass = 'archive';
                            } elseif (in_array($fileExtension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'])) {
                            $fileIcon = 'bx-video';
                            $fileTypeClass = 'video';
                            } elseif (in_array($fileExtension, ['mp3', 'wav', 'flac', 'aac', 'ogg'])) {
                            $fileIcon = 'bx-music';
                            $fileTypeClass = 'music';
                            } elseif (in_array($fileExtension, ['txt', 'rtf'])) {
                            $fileIcon = 'bx-file-txt';
                            $fileTypeClass = 'text';
                            } elseif (in_array($fileExtension, ['ppt', 'pptx'])) {
                            $fileIcon = 'bx-file-blank';
                            $fileTypeClass = 'presentation';
                            } elseif (in_array($fileExtension, ['ai', 'eps', 'psd'])) {
                            $fileIcon = 'bx-paint';
                            $fileTypeClass = 'design';
                            }
                            @endphp
                            <div class="file-card-wrapper file-item"
                                data-filename="{{ strtolower($att->original_name) }}">
                                <div class="file-card file-type-{{ $fileTypeClass }}">
                                    <!-- Card Timestamp Header -->
                                    <div class="card-timestamp-header">
                                        <div class="timestamp-content">
                                            <div class="timestamp-date">{{ $att->created_at->format('M d, Y') }}</div>
                                            <div class="timestamp-time">{{ $att->created_at->format('H:i') }}</div>
                                        </div>
                                    </div>

                                    <!-- File Card Header -->
                                    <div class="file-card-header">
                                        <div class="file-icon-wrapper">
                                            <div class="file-icon">
                                                <i class="bx {{ $fileIcon }}"></i>
                                            </div>
                                            <div class="file-info">
                                                <h6 class="file-name">{{ $att->original_name }}</h6>
                                                <div class="file-details">{{ number_format($att->size_bytes/1024,1) }}
                                                    KB • {{ $att->mime_type }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- File Card Body -->
                                    <div class="file-card-body">
                                        @if(Auth::user()->isManager())
                                        <!-- Manager Controls for Required Files -->
                                        <div class="manager-controls mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input required-toggle" type="checkbox"
                                                    id="required_{{ $att->id }}" data-attachment-id="{{ $att->id }}" {{
                                                    $att->required_for_email ? 'checked' : '' }}>
                                                <label class="form-check-label" for="required_{{ $att->id }}">
                                                    <strong>Required for Email</strong>
                                                </label>
                                            </div>
                                        </div>
                                        @else
                                        <!-- User View - Show if file is required -->
                                        @if($att->required_for_email)
                                        <div class="required-badge mb-2">
                                            <span class="badge bg-success">
                                                <i class="bx bx-check-circle"></i> Required for Email
                                            </span>
                                        </div>
                                        @endif
                                        @endif
                                        <div class="file-meta">
                                            <div class="meta-badge uploader-badge">
                                                <i class="bx bx-user"></i>
                                                <span>by {{ $att->uploader?->name }}</span>
                                            </div>
                                            @if($isRecent)
                                            <div class="meta-badge recent-badge">
                                                <i class="bx bx-time"></i>
                                                <span>Recent</span>
                                            </div>
                                            @endif
                                        </div>
                                        {{-- <div class="date-section">
                                            <div class="date-info">
                                                <i class="bx bx-calendar"></i>
                                                <div>
                                                    <div class="date-text">{{ $att->created_at->format('M d, Y') }}
                                                    </div>
                                                    <div class="time-text">{{ $att->created_at->format('H:i') }}</div>
                                                </div>
                                            </div>
                                        </div> --}}
                                    </div>

                                    <!-- File Card Footer -->
                                    <div class="file-card-footer">
                                        <div class="file-actions">
                                            <a class="action-btn view-btn" href="{{ Storage::url($att->path) }}"
                                                target="_blank">
                                                <i class="bx bx-show"></i>
                                                <span>View</span>
                                            </a>
                                            <a class="action-btn download-btn"
                                                href="{{ route('tasks.attachments.download', $att) }}">
                                                <i class="bx bx-download"></i>
                                                <span>Download</span>
                                            </a>
                                            @php($currentUser = Auth::user())
                                            @if(($currentUser->isManager() && $currentUser->canDelete()) ||
                                            (!$currentUser->isManager() && $att->uploaded_by === $currentUser->id &&
                                            $task->status !== 'submitted_for_review' && $task->status !== 'in_review' &&
                                            $task->status !== 'approved' && $task->status !== 'completed'))
                                            <form action="{{ route('tasks.attachments.delete', [$task, $att]) }}"
                                                method="POST" onsubmit="return confirm('Delete attachment?')"
                                                class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button class="action-btn delete-btn" type="submit">
                                                    <i class="bx bx-trash"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                            @elseif($currentUser->isManager() && $currentUser->isSubAdmin())
                                            <button class="action-btn delete-btn delete-request-trigger" type="button"
                                                data-bs-toggle="modal" data-bs-target="#deleteRequestModal"
                                                data-target-type="project_file" data-target-id="{{ $att->id }}"
                                                data-target-label="{{ $att->display_name ?? $att->original_name }}"
                                                data-redirect="{{ url()->current() }}" title="Request deletion">
                                                <i class="bx bx-trash"></i>
                                                <span>Request Delete</span>
                                            </button>
                                            @elseif($currentUser->isManager())
                                            <span class="action-btn delete-btn disabled"
                                                title="You do not have permission to delete attachments."
                                                aria-disabled="true">
                                                <i class="bx bx-trash"></i>
                                                <span>Delete</span>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <div class="mb-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center"
                                    style="width: 120px; height: 120px;">
                                    <i class="bx bx-file text-muted" style="font-size: 3rem;"></i>
                                </div>
                            </div>
                            <h5 class="text-muted mb-2">No files uploaded yet</h5>
                            <p class="text-muted mb-0">Upload your first file to get started</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- JavaScript for file input handling -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Enhanced file input and drag-drop handling
                    const fileInput = document.getElementById('fileInput');
                    const fileNameSpan = document.querySelector('.file-name');
                    const fileSizeSpan = document.querySelector('.file-size');
                    const uploadArea = document.getElementById('uploadArea');
                    const uploadActions = document.getElementById('uploadActions');
                    const uploadSection = document.getElementById('uploadSection');
                    const changeFileBtn = document.getElementById('changeFile');

                    // Format file size
                    function formatFileSize(bytes) {
                        if (bytes === 0) return '0 Bytes';
                        const k = 1024;
                        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                    }

                    // Update file display for multiple files
                    function updateFileDisplay(files) {
                        if (files && files.length > 0) {
                            if (files.length === 1) {
                                fileNameSpan.textContent = files[0].name;
                                fileSizeSpan.textContent = '(' + formatFileSize(files[0].size) + ')';
                            } else {
                                fileNameSpan.textContent = files.length + ' files selected';
                                const totalSize = Array.from(files).reduce((sum, file) => sum + file.size, 0);
                                fileSizeSpan.textContent = '(' + formatFileSize(totalSize) + ' total)';
                            }
                            fileNameSpan.classList.remove('text-muted');
                            fileNameSpan.classList.add('text-primary', 'fw-bold');
                            uploadActions.style.display = 'block';
                            uploadArea.style.display = 'none';
                        } else {
                            fileNameSpan.textContent = 'No files chosen';
                            fileNameSpan.classList.remove('text-primary', 'fw-bold');
                            fileNameSpan.classList.add('text-muted');
                            fileSizeSpan.textContent = '';
                            uploadActions.style.display = 'none';
                            uploadArea.style.display = 'block';
                        }
                    }

                    // File input handling
                    if (fileInput) {
                        fileInput.addEventListener('change', function() {
                            updateFileDisplay(this.files);
                        });
                    }

                    // Change file button
                    if (changeFileBtn) {
                        changeFileBtn.addEventListener('click', function() {
                            fileInput.click();
                        });
                    }

                    // Drag and drop functionality
                    if (uploadArea) {
                        // Prevent default drag behaviors
                        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                            uploadArea.addEventListener(eventName, preventDefaults, false);
                            document.body.addEventListener(eventName, preventDefaults, false);
                        });

                        // Highlight drop area when item is dragged over it
                        ['dragenter', 'dragover'].forEach(eventName => {
                            uploadArea.addEventListener(eventName, highlight, false);
                        });

                        ['dragleave', 'drop'].forEach(eventName => {
                            uploadArea.addEventListener(eventName, unhighlight, false);
                        });

                        // Handle dropped files
                        uploadArea.addEventListener('drop', handleDrop, false);

                        function preventDefaults(e) {
                            e.preventDefault();
                            e.stopPropagation();
                        }

                        function highlight(e) {
                            uploadArea.classList.add('drag-over');
                        }

                        function unhighlight(e) {
                            uploadArea.classList.remove('drag-over');
                        }

                        function handleDrop(e) {
                            const dt = e.dataTransfer;
                            const files = dt.files;

                            if (files.length > 0) {
                                fileInput.files = files;
                                updateFileDisplay(files);
                            }
                        }

                        // Click to browse
                        uploadArea.addEventListener('click', function() {
                            fileInput.click();
                        });
                    }

                    // Enhanced file search functionality
                    const fileSearch = document.getElementById('fileSearch');
                    const fileItems = document.querySelectorAll('.file-item');
                    const clearSearchBtn = document.getElementById('clearSearch');
                    const searchResultsInfo = document.getElementById('searchResultsInfo');
                    const resultsCount = document.getElementById('resultsCount');

                    if (fileSearch && fileItems.length > 0) {
                        fileSearch.addEventListener('input', function() {
                            const searchTerm = this.value.toLowerCase().trim();
                            let visibleCount = 0;

                            // Show/hide clear button
                            if (searchTerm.length > 0) {
                                clearSearchBtn.style.display = 'block';
                                searchResultsInfo.style.display = 'block';
                            } else {
                                clearSearchBtn.style.display = 'none';
                                searchResultsInfo.style.display = 'none';
                            }

                            fileItems.forEach(function(item) {
                                const filename = item.getAttribute('data-filename');
                                const fileCard = item.querySelector('.file-card');

                                if (filename.includes(searchTerm)) {
                                    item.style.display = 'block';
                                    fileCard.style.opacity = '1';
                                    fileCard.style.transform = 'translateY(0)';
                                    visibleCount++;
                                } else {
                                    item.style.display = 'none';
                                }
                            });

                            // Update results count
                            if (searchTerm.length > 0) {
                                resultsCount.textContent = visibleCount;
                                if (visibleCount === 0) {
                                    resultsCount.textContent = 'No';
                                    searchResultsInfo.innerHTML = '<small class="text-muted text-danger"><i class="bx bx-info-circle me-1"></i>No files found matching "' + searchTerm + '"</small>';
                                } else {
                                    searchResultsInfo.innerHTML = '<small class="text-muted"><span id="resultsCount">' + visibleCount + '</span> files found</small>';
                                }
                            }
                        });

                        // Clear search functionality
                        clearSearchBtn.addEventListener('click', function() {
                            fileSearch.value = '';
                            fileSearch.dispatchEvent(new Event('input'));
                            fileSearch.focus();
                        });

                        // Search on Enter key
                        fileSearch.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                this.blur();
                            }
                        });
                    }
                });

                // Manager Controls for Required Files
                @if(Auth::user()->isManager())
                // Handle required toggle switches - Auto-save on change
                document.querySelectorAll('.required-toggle').forEach(toggle => {
                    toggle.addEventListener('change', function() {
                        const attachmentId = this.dataset.attachmentId;
                        const isRequired = this.checked;

                        // Save the requirement status immediately
                        saveAttachmentRequirement(attachmentId, isRequired);
                    });
                });

                // Function to save attachment requirement
                function saveAttachmentRequirement(attachmentId, isRequired) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(`/tasks/{{ $task->id }}/attachments/${attachmentId}/mark-required`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            required_for_email: isRequired,
                            required_notes: null
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            const action = isRequired ? 'marked as required' : 'unmarked as required';
                            showToast('success', `File ${action} for email successfully.`);
                        } else {
                            showToast('error', data.message || 'Failed to update file requirement');
                            // Revert the toggle if save failed
                            const toggle = document.getElementById(`required_${attachmentId}`);
                            toggle.checked = !isRequired;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'Failed to update file requirement');
                        // Revert the toggle if save failed
                        const toggle = document.getElementById(`required_${attachmentId}`);
                        toggle.checked = !isRequired;
                    });
                }

                // Toast notification function
                function showToast(type, message) {
                    // Create toast element
                    const toast = document.createElement('div');
                    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
                    toast.setAttribute('role', 'alert');
                    toast.innerHTML = `
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-error-circle'} me-2"></i>
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    `;

                    // Add to page
                    document.body.appendChild(toast);

                    // Initialize and show toast
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.show();

                    // Remove from DOM after hiding
                    toast.addEventListener('hidden.bs.toast', () => {
                        document.body.removeChild(toast);
                    });
                }
                @endif
            </script>

            <!-- Task History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Task History</h5>
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                            data-bs-target="#taskHistoryCollapse" aria-expanded="true"
                            aria-controls="taskHistoryCollapse">
                            <i class="bx bx-chevron-down" id="historyToggleIcon"></i>
                            <span id="historyToggleText">Minimize</span>
                        </button>
                    </div>
                </div>
                <div class="collapse show" id="taskHistoryCollapse">
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
                                                    <i class="bx bx-user me-1"></i>{{ $history->user->name ?? 'System'
                                                    }}
                                                </small>
                                                <small>
                                                    <i class="bx bx-time me-1"></i>{{ $history->created_at->format('M d,
                                                    Y g:i A') }}
                                                </small>
                                            </div>
                                        </div>
                                        <span class="badge bg-label-secondary">{{ ucfirst(str_replace('_', ' ',
                                            $history->action)) }}</span>
                                    </div>

                                    {{-- Show comments/notes from metadata --}}
                                    @if($history->metadata && is_array($history->metadata))
                                    @if(isset($history->metadata['completion_notes']))
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <strong class="text-primary"><i class="bx bx-message-detail me-1"></i>Completion
                                            Notes:</strong>
                                        <p class="mb-0 mt-1">{{ $history->metadata['completion_notes'] }}</p>
                                    </div>
                                    @endif
                                    {{-- Only show notes in metadata if they're not already included in the description
                                    --}}
                                    @if(isset($history->metadata['notes']) && $history->metadata['notes'] &&
                                    !str_contains($history->description, $history->metadata['notes']))
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <strong class="text-info"><i
                                                class="bx bx-comment-detail me-1"></i>Notes:</strong>
                                        <p class="mb-0 mt-1">{{ $history->metadata['notes'] }}</p>
                                    </div>
                                    @endif
                                    @if(isset($history->metadata['internal_notes']) &&
                                    $history->metadata['internal_notes'])
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <strong class="text-primary"><i class="bx bx-message-detail me-1"></i>Internal
                                            Notes:</strong>
                                        <p class="mb-0 mt-1">{{ $history->metadata['internal_notes'] }}</p>
                                    </div>
                                    @endif
                                    @if(isset($history->metadata['client_response_notes']) &&
                                    $history->metadata['client_response_notes'] && !str_contains($history->description,
                                    $history->metadata['client_response_notes']))
                                    <div class="mt-2 p-2 bg-primary bg-opacity-10 rounded border border-primary">
                                        <strong class="text-primary"><i class="bx bx-user me-1"></i>Client
                                            Response:</strong>
                                        <p class="mb-0 mt-1">{{ $history->metadata['client_response_notes'] }}</p>
                                    </div>
                                    @endif
                                    @if(isset($history->metadata['consultant_response_notes']) &&
                                    $history->metadata['consultant_response_notes'] &&
                                    !str_contains($history->description,
                                    $history->metadata['consultant_response_notes']))
                                    <div class="mt-2 p-2 bg-info bg-opacity-10 rounded border border-info">
                                        <strong class="text-info"><i class="bx bx-user-check me-1"></i>Consultant
                                            Response:</strong>
                                        <p class="mb-0 mt-1">{{ $history->metadata['consultant_response_notes'] }}</p>
                                    </div>
                                    @endif
                                    @if(isset($history->metadata['manager_override_notes']) &&
                                    $history->metadata['manager_override_notes'] && !str_contains($history->description,
                                    $history->metadata['manager_override_notes']))
                                    <div class="mt-2 p-2 bg-danger bg-opacity-10 rounded border border-danger">
                                        <strong class="text-danger"><i class="bx bx-shield-x me-1"></i>Manager
                                            Override:</strong>
                                        <p class="mb-0 mt-1">{{ $history->metadata['manager_override_notes'] }}</p>
                                    </div>
                                    @endif
                                    @if(isset($history->metadata['combined_response_status']))
                                    <div class="mt-2">
                                        <strong>Combined Status:</strong>
                                        <span class="badge bg-info">{{ $history->metadata['combined_response_status']
                                            }}</span>
                                    </div>
                                    @endif

                                    {{-- Special display for email marked sent actions --}}
                                    @if($history->action === 'email_marked_sent' &&
                                    isset($history->metadata['email_subject']))
                                    <div class="mt-2 p-2 bg-success bg-opacity-10 rounded border border-success">
                                        <strong class="text-success"><i class="bx bx-check-double me-1"></i>Email
                                            Details:</strong>
                                        <div class="mt-1">
                                            <small><strong>Subject:</strong> {{ $history->metadata['email_subject']
                                                }}</small><br>
                                            <small><strong>To:</strong> {{ implode(', ', $history->metadata['email_to']
                                                ?? []) }}</small><br>
                                            @if(!empty($history->metadata['email_cc']))
                                            <small><strong>Cc:</strong> {{ implode(', ', $history->metadata['email_cc'])
                                                }}</small><br>
                                            @endif
                                            @if(!empty($history->metadata['email_bcc']))
                                            <small><strong>Bcc:</strong> {{ implode(', ',
                                                $history->metadata['email_bcc']) }}</small><br>
                                            @endif
                                            <small><strong>Sent Via:</strong> {{ ucfirst(str_replace('_', ' ',
                                                $history->metadata['sent_via'] ?? 'Unknown')) }}</small><br>
                                            @if($history->metadata['has_attachments'] ?? false)
                                            <small><strong>Attachments:</strong> {{
                                                $history->metadata['attachment_count'] ?? 0 }} file(s)</small>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    @if($history->action === 'require_resubmit_enhanced' &&
                                    isset($history->metadata['resubmit_notes']))
                                    <div class="mt-2 p-2 bg-warning bg-opacity-10 rounded border border-warning">
                                        <strong class="text-warning"><i class="bx bx-refresh me-1"></i>Resubmission
                                            Instructions:</strong>
                                        <div class="mt-1">
                                            <p class="mb-2">{{ $history->metadata['resubmit_notes'] }}</p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small><strong>Priority:</strong> {{
                                                        ucfirst($history->metadata['priority'] ?? 'Normal')
                                                        }}</small><br>
                                                    @if($history->metadata['due_date'])
                                                    <small><strong>Due Date:</strong> {{
                                                        \Carbon\Carbon::parse($history->metadata['due_date'])->format('M
                                                        d, Y') }}</small><br>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    @if($history->metadata['file_count'] > 0)
                                                    <small><strong>Reference Files:</strong> {{
                                                        $history->metadata['file_count'] }} file(s) attached</small><br>
                                                    @endif
                                                    <small><strong>Manager:</strong> {{
                                                        $history->metadata['manager_override_by'] ?? 'Unknown'
                                                        }}</small>
                                                </div>
                                            </div>
                                            @if(isset($history->metadata['client_notes']) ||
                                            isset($history->metadata['consultant_notes']))
                                            <div class="mt-2 pt-2 border-top">
                                                <small class="text-muted">
                                                    <strong>Based on feedback:</strong>
                                                    @if($history->metadata['client_notes'])
                                                    Client: {{ $history->metadata['client_notes'] }}
                                                    @endif
                                                    @if($history->metadata['consultant_notes'])
                                                    @if($history->metadata['client_notes']) | @endif
                                                    Consultant: {{ $history->metadata['consultant_notes'] }}
                                                    @endif
                                                </small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    @endif

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
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- USER ACTIONS -->
                        @php
                        $viewerId = auth()->id();
                        $isAssignee = $task->assigned_to === $viewerId;
                        $taskStatus = $task->status;
                        @endphp

                        @if($isAssignee)
                        {{-- Time Extension Request Button --}}
                        @if($taskStatus !== 'completed')
                        @php
                        $hasPendingRequest = $task->timeExtensionRequests()->where('status', 'pending')->exists();
                        @endphp
                        @if(!$hasPendingRequest)
                        <button class="btn btn-outline-warning w-100 mb-2"
                            onclick="showTimeExtensionModal({{ $task->id }})">
                            <i class="bx bx-time me-2"></i>Request Time Extension
                        </button>
                        @else
                        <div class="alert alert-info mb-2 p-2">
                            <i class="bx bx-hourglass me-2"></i>
                            <small>Time extension request pending review</small>
                        </div>
                        @endif
                        @endif

                        @if($taskStatus === 'assigned')
                        <form action="{{ route('tasks.accept', $task) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bx bx-check-circle me-2"></i>Accept Task & Start Working
                            </button>
                        </form>
                        <small class="text-muted text-center">Click to accept this task and start working on it</small>

                        @elseif($taskStatus === 'in_progress')
                        <button class="btn btn-primary w-100" onclick="submitForReview({{ $task->id }})">
                            <i class="bx bx-send me-2"></i>Submit for Review
                        </button>
                        <small class="text-muted text-center">Complete your work and submit it for manager
                            review</small>

                        @elseif($taskStatus === 'submitted_for_review')
                        <div class="alert alert-warning text-center mb-0">
                            <i class="bx bx-time-five me-2"></i>
                            <strong>Waiting for Manager</strong><br>
                            <small>Task submitted for review. Manager will review and start the review process.</small>
                        </div>

                        @elseif($taskStatus === 'in_review')
                        <div class="alert alert-info text-center mb-0">
                            <i class="bx bx-search me-2"></i>
                            <strong>Under Review</strong><br>
                            <small>Manager is currently reviewing your task.</small>
                        </div>

                        @elseif($taskStatus === 'ready_for_email')
                        <div class="alert alert-success text-center mb-3">
                            <i class="bx bx-check-circle me-2"></i>
                            <strong>Internally Approved!</strong><br>
                            <small>Manager approved. Now send confirmation email to clients/consultants.</small>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('tasks.prepare-email', $task) }}" class="btn btn-primary">
                                <i class="bx bx-envelope me-2"></i>Prepare Confirmation Email
                            </a>
                        </div>
                        <small class="text-muted text-center mt-2 d-block">You can also assign contractors during email
                            preparation</small>

                        @elseif($taskStatus === 'in_review_after_client_consultant_reply')
                        <div class="alert alert-warning text-center mb-0">
                            <i class="bx bx-message-dots me-2"></i>
                            <strong>Processing Client Feedback</strong><br>
                            <small>Client/consultant has responded. Manager is processing the feedback.</small>
                        </div>

                        @elseif($taskStatus === 'rejected')
                        <div class="alert alert-danger text-center mb-0">
                            <i class="bx bx-x-circle me-2"></i>
                            <strong>Task Rejected</strong><br>
                            <small>This task has been rejected. Please contact your manager for details.</small>
                        </div>

                        @elseif($taskStatus === 'completed')
                        <div class="alert alert-success text-center mb-0">
                            <i class="bx bx-check-circle me-2"></i>
                            <strong>Task Completed</strong><br>
                            <small>Congratulations! This task has been successfully completed.</small>
                        </div>

                        @elseif($taskStatus === 'pending')
                        <div class="alert alert-secondary text-center mb-0">
                            <i class="bx bx-time me-2"></i>
                            <strong>Task Pending</strong><br>
                            <small>This task is pending assignment or approval.</small>
                        </div>

                        @elseif($taskStatus === 're_submit_required')
                        <div class="alert alert-warning text-center mb-3">
                            <i class="bx bx-refresh me-2"></i>
                            <strong>Resubmission Required</strong><br>
                            <small>Manager has requested changes after client/consultant review</small>
                        </div>

                        @if($task->manager_override_notes)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Manager Notes:</h6>
                                <p class="mb-0">{{ $task->manager_override_notes }}</p>
                            </div>
                        </div>
                        @endif

                        @if($task->combined_response_status)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Client/Consultant Feedback:</h6>
                                <span class="badge bg-info mb-2">{{ $task->combined_response_status }}</span>

                                @if($task->client_response_notes)
                                <div class="mt-2">
                                    <strong>Client Notes:</strong>
                                    <p class="mb-0 text-muted">{{ $task->client_response_notes }}</p>
                                </div>
                                @endif

                                @if($task->consultant_response_notes)
                                <div class="mt-2">
                                    <strong>Consultant Notes:</strong>
                                    <p class="mb-0 text-muted">{{ $task->consultant_response_notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <form action="{{ route('tasks.resubmit', $task) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bx bx-edit me-2"></i>Start Working on Changes
                            </button>
                        </form>
                        <small class="text-muted text-center mt-2 d-block">Click to return to in-progress status and
                            make the requested changes</small>

                        @elseif($taskStatus === 'on_client_consultant_review' || $task->combined_response_status)
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bx bx-envelope me-2"></i>Client/Consultant Responses</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('tasks.client-response', $task) }}" method="POST" class="mb-3">
                                    @csrf
                                    <label class="form-label fw-semibold"><i class="bx bx-user me-1"></i>Client
                                        Status:</label>
                                    <select name="client_response_status" class="form-select form-select-sm mb-2">
                                        <option value="pending" {{ ($task->client_response_status ?? 'pending') ===
                                            'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ ($task->client_response_status ?? 'pending') ===
                                            'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ ($task->client_response_status ?? 'pending') ===
                                            'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    <textarea name="client_response_notes" class="form-control form-control-sm mb-2"
                                        rows="2"
                                        placeholder="Client response notes/comments...">{{ $task->client_response_notes ?? '' }}</textarea>
                                    <button type="submit" class="btn btn-sm btn-primary w-100"><i
                                            class="bx bx-save me-1"></i>Save Client Response</button>
                                </form>

                                <hr>

                                <form action="{{ route('tasks.consultant-response', $task) }}" method="POST"
                                    class="mb-3">
                                    @csrf
                                    <label class="form-label fw-semibold"><i
                                            class="bx bx-user-check me-1"></i>Consultant Status:</label>
                                    <select name="consultant_response_status" class="form-select form-select-sm mb-2">
                                        <option value="pending" {{ ($task->consultant_response_status ?? 'pending') ===
                                            'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ ($task->consultant_response_status ?? 'pending') ===
                                            'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ ($task->consultant_response_status ?? 'pending') ===
                                            'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    <textarea name="consultant_response_notes" class="form-control form-control-sm mb-2"
                                        rows="2"
                                        placeholder="Consultant response notes/comments...">{{ $task->consultant_response_notes ?? '' }}</textarea>
                                    <button type="submit" class="btn btn-sm btn-success w-100"><i
                                            class="bx bx-save me-1"></i>Save Consultant Response</button>
                                </form>

                                @if($task->combined_response_status)
                                <div class="alert alert-info mb-2">
                                    <strong>Combined Status:</strong><br>
                                    <span class="badge bg-info">{{ $task->combined_response_status }}</span>
                                </div>
                                @endif

                                <form id="finishReviewForm" action="{{ route('tasks.finish-review', $task) }}"
                                    method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bx bx-check-double me-2"></i>Finish Review & Notify Manager
                                    </button>
                                </form>
                                <small class="text-muted text-center mt-2 d-block">Will automatically save current
                                    client & consultant responses</small>
                            </div>
                        </div>
                        @endif
                        @else
                        @if(in_array($taskStatus, ['pending', 'assigned', null]))
                        <div class="alert alert-info text-center mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Task Assigned</strong><br>
                            <small>This task has been assigned to {{ $task->assignee->name ?? 'another user' }}. Waiting
                                for them to accept and start working.</small>
                        </div>
                        @elseif($taskStatus === 'on_client_consultant_review' || $task->combined_response_status)
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bx bx-envelope me-2"></i>Client/Consultant Responses</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-2">
                                    <strong>Status:</strong>
                                    <span class="badge bg-info">{{ $task->combined_response_status ?? 'Pending
                                        Responses' }}</span>
                                </div>
                                <p class="mb-0 text-muted">Only the assigned user or manager can update responses.</p>
                            </div>
                        </div>
                        @endif
                        @endif

                        <!-- MANAGER ACTIONS -->
                        @if(auth()->user()->isManager())
                        {{-- Check for pending time extension requests --}}
                        @php
                        $pendingExtensionRequests = $task->timeExtensionRequests()->where('status', 'pending')->get();
                        @endphp

                        @if($pendingExtensionRequests->count() > 0)
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0">
                                    <i class="bx bx-time me-2"></i>Pending Time Extension Requests ({{
                                    $pendingExtensionRequests->count() }})
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($pendingExtensionRequests as $request)
                                <div class="alert alert-light mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong>{{ $request->requester->name }}</strong> requested
                                            <span class="badge bg-warning">{{ $request->requested_days }} days</span>
                                        </div>
                                        <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-3"><strong>Reason:</strong> {{ $request->reason }}</p>

                                    <form onsubmit="reviewTimeExtension(event, {{ $task->id }}, {{ $request->id }})">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Decision:</label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="action_{{ $request->id }}"
                                                    id="approve_{{ $request->id }}" value="approve" checked>
                                                <label class="btn btn-outline-success"
                                                    for="approve_{{ $request->id }}">Approve</label>

                                                <input type="radio" class="btn-check" name="action_{{ $request->id }}"
                                                    id="reject_{{ $request->id }}" value="reject">
                                                <label class="btn btn-outline-danger"
                                                    for="reject_{{ $request->id }}">Reject</label>
                                            </div>
                                        </div>

                                        <div class="mb-3" id="approve_inputs_{{ $request->id }}">
                                            <label class="form-label">Days to Approve:</label>
                                            <input type="number" class="form-control"
                                                name="approved_days_{{ $request->id }}"
                                                value="{{ $request->requested_days }}" min="1" max="365">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Manager Notes:</label>
                                            <textarea class="form-control" name="manager_notes_{{ $request->id }}"
                                                rows="2" placeholder="Optional notes for the user..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bx bx-check me-1"></i>Submit Decision
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Status: Submitted for Review - Manager can start review --}}
                        @if($task->status === 'submitted_for_review')
                        <div class="alert alert-primary text-center mb-3">
                            <i class="bx bx-clipboard me-2"></i>
                            <strong>Task Submitted for Review</strong><br>
                            <small>User: {{ $task->assignee->name ?? 'Unknown' }}</small>
                        </div>
                        @if($task->completion_notes)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Completion Notes:</h6>
                                <p class="mb-0">{{ $task->completion_notes }}</p>
                            </div>
                        </div>
                        @endif
                        <form action="{{ route('tasks.start-review', $task) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info w-100">
                                <i class="bx bx-play-circle me-2"></i>Start Review
                            </button>
                        </form>
                        <small class="text-muted text-center">Click to begin reviewing this task</small>

                        {{-- Status: In Review - Manager can approve internally --}}
                        @elseif($task->status === 'in_review')
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0"><i class="bx bx-search me-2"></i>Internal Approval</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('tasks.internal-approval', $task) }}" method="POST">
                                    @csrf
                                    <label class="form-label fw-semibold">Decision:</label>
                                    <select name="internal_status" class="form-select mb-2" required>
                                        <option value="approved">Approve</option>
                                        <option value="rejected">Reject</option>
                                    </select>
                                    <textarea name="internal_notes" class="form-control mb-2" rows="3"
                                        placeholder="Internal approval notes/feedback..." required></textarea>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bx bx-check-circle me-2"></i>Submit Internal Approval
                                    </button>
                                </form>
                                <small class="text-muted text-center mt-2 d-block">Approve = Ready for client email |
                                    Reject = Back to user</small>
                            </div>
                        </div>

                        {{-- Status: Ready for Email - Waiting for user to send --}}
                        @elseif($task->status === 'ready_for_email')
                        <div class="alert alert-info text-center mb-0">
                            <i class="bx bx-envelope me-2"></i>
                            <strong>Ready for Email</strong><br>
                            <small>Waiting for user to prepare and send confirmation email.</small>
                        </div>

                        {{-- Status: In Review After Client/Consultant Reply - Manager can complete or request resubmit
                        --}}
                        @elseif($task->status === 'in_review_after_client_consultant_reply')
                        <div class="alert alert-info text-center mb-3">
                            <i class="bx bx-clipboard me-2"></i>
                            <strong>Client/Consultant Review Completed</strong><br>
                            <small>Review feedback collected from client and consultant</small>
                        </div>

                        @if($task->combined_response_status)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Combined Response Status:</h6>
                                <span class="badge bg-info mb-2">{{ $task->combined_response_status }}</span>

                                @if($task->client_response_notes)
                                <div class="mt-2">
                                    <strong>Client Notes:</strong>
                                    <p class="mb-0 text-muted">{{ $task->client_response_notes }}</p>
                                </div>
                                @endif

                                @if($task->consultant_response_notes)
                                <div class="mt-2">
                                    <strong>Consultant Notes:</strong>
                                    <p class="mb-0 text-muted">{{ $task->consultant_response_notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="d-grid gap-2">
                            <!-- Mark as Completed Button -->
                            <button class="btn btn-success w-100" onclick="markAsCompleted({{ $task->id }})">
                                <i class="bx bx-check-circle me-2"></i>Mark as Completed
                            </button>
                            <small class="text-muted text-center">Task meets requirements - mark as complete</small>

                            <hr>

                            <!-- Require Resubmit Button -->
                            <button class="btn btn-warning w-100" data-bs-toggle="modal"
                                data-bs-target="#requireResubmitModal">
                                <i class="bx bx-refresh me-2"></i>Request Resubmission
                            </button>
                            <small class="text-muted text-center">Task needs changes - send back to user</small>
                        </div>

                        {{-- Manager Override Options --}}
                        @elseif($task->combined_response_status)
                        <div class="card border-danger mb-3">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0"><i class="bx bx-shield me-2"></i>Manager Override</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning mb-3">
                                    <strong>Combined Status:</strong><br>
                                    <span class="badge bg-info">{{ $task->combined_response_status }}</span>
                                </div>
                                <form action="{{ route('tasks.manager-override', $task) }}" method="POST">
                                    @csrf
                                    <label class="form-label fw-semibold">Override Action:</label>
                                    <select name="manager_override_status" class="form-select mb-2" required>
                                        <option value="reject">Reject Task</option>
                                        <option value="reset_for_review">Reset for Review</option>
                                    </select>
                                    <textarea name="manager_override_notes" class="form-control mb-2" rows="3"
                                        placeholder="Reason for override..." required></textarea>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="bx bx-shield-x me-2"></i>Apply Override
                                    </button>
                                </form>
                                <small class="text-muted text-center mt-2 d-block">Override will send task back to
                                    user</small>
                            </div>
                        </div>
                        @endif

                        {{-- Manager can assign unassigned tasks --}}
                        @if(!$task->assigned_to)
                        <button class="btn btn-info w-100" onclick="assignTask({{ $task->id }})">
                            <i class="bx bx-user-plus me-2"></i>Assign Task
                        </button>
                        @endif
                        @endif

                        <!-- Status change for managers (override) -->
                        @if(Auth::user()->isManager())
                        <hr>
                        <button class="btn btn-outline-secondary w-100" onclick="changeTaskStatus({{ $task->id }})">
                            <i class="bx bx-edit me-2"></i>Manual Status Change
                            <span class="badge bg-warning ms-1">Manager</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Contractors Assigned -->
            @if($task->contractors->count() > 0)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bx bx-group me-2"></i>Assigned Contractors</h5>
                </div>
                <div class="card-body">
                    @foreach($task->contractors as $contractor)
                    <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($contractor->name, 0,
                                1) }}</span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $contractor->name }}</h6>
                            <small class="text-muted">{{ ucfirst($contractor->type) }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Internal Approval Status -->
            @if($task->internal_status && $task->internal_status !== 'pending')
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bx bx-check-shield me-2"></i>Internal Approval</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $task->internal_status === 'approved' ? 'success' : 'danger' }}">
                            {{ ucfirst($task->internal_status) }}
                        </span>
                    </div>
                    @if($task->internal_notes)
                    <div class="mb-2">
                        <strong>Notes:</strong>
                        <p class="mb-0 text-muted">{{ $task->internal_notes }}</p>
                    </div>
                    @endif
                    @if($task->internalApprover)
                    <div>
                        <strong>By:</strong> {{ $task->internalApprover->name }}<br>
                        <small class="text-muted">{{ $task->internal_updated_at?->format('M d, Y H:i') }}</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Client/Consultant Response Status -->
            @if($task->combined_response_status)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="bx bx-message-check me-2"></i>External Approval</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Combined Status:</strong>
                        <span class="badge bg-info">{{ $task->combined_response_status }}</span>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Client:</strong><br>
                            <span
                                class="badge bg-{{ ($task->client_response_status ?? 'pending') === 'approved' ? 'success' : (($task->client_response_status ?? 'pending') === 'rejected' ? 'danger' : 'secondary') }}">
                                {{ ucfirst($task->client_response_status ?? 'pending') }}
                            </span>
                            @if($task->client_response_notes)
                            <p class="mb-0 mt-1"><small class="text-muted">{{ $task->client_response_notes }}</small>
                            </p>
                            @endif
                        </div>
                        <div class="col-6">
                            <strong>Consultant:</strong><br>
                            <span
                                class="badge bg-{{ ($task->consultant_response_status ?? 'pending') === 'approved' ? 'success' : (($task->consultant_response_status ?? 'pending') === 'rejected' ? 'danger' : 'secondary') }}">
                                {{ ucfirst($task->consultant_response_status ?? 'pending') }}
                            </span>
                            @if($task->consultant_response_notes)
                            <p class="mb-0 mt-1"><small class="text-muted">{{ $task->consultant_response_notes
                                    }}</small></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Manager Override Status -->
            @if($task->manager_override_status && $task->manager_override_status !== 'none')
            <div class="card mb-4 border-0 shadow-sm border-danger">
                <div class="card-header bg-danger text-white border-0">
                    <h5 class="mb-0"><i class="bx bx-shield-x me-2"></i>Manager Override</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Action:</strong>
                        <span class="badge bg-{{ $task->manager_override_status === 'reject' ? 'danger' : 'warning' }}">
                            {{ ucfirst(str_replace('_', ' ', $task->manager_override_status)) }}
                        </span>
                    </div>
                    @if($task->manager_override_notes)
                    <div class="mb-2">
                        <strong>Notes:</strong>
                        <p class="mb-0 text-muted">{{ $task->manager_override_notes }}</p>
                    </div>
                    @endif
                    @if($task->managerOverrideBy)
                    <div>
                        <strong>By:</strong> {{ $task->managerOverrideBy->name }}<br>
                        <small class="text-muted">{{ $task->manager_override_updated_at?->format('M d, Y H:i')
                            }}</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif


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
                                @if($task->start_date && $task->due_date)
                                @php
                                $startDate = \Carbon\Carbon::parse($task->start_date)->startOfDay();
                                $dueDate = \Carbon\Carbon::parse($task->due_date)->startOfDay();
                                $taskDuration = $startDate->diffInDays($dueDate);
                                @endphp
                                <h4 class="mb-1 text-info">{{ $taskDuration }}</h4>
                                <small class="text-muted">Task Duration (Days)</small>
                                @else
                                <h4 class="mb-1 text-muted">N/A</h4>
                                <small class="text-muted">Duration Not Set</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    @php
                    $taskScoreData = $task->getTaskScore($task->assignee);
                    $taskScore = $taskScoreData['score'];
                    $scoreExplanations = $task->getTaskScoreExplanation($task->assignee);
                    @endphp

                    <div class="row g-3 align-items-stretch">
                        <div class="col-12 col-md-5">
                            <div
                                class="bg-light rounded p-3 h-100 d-flex flex-column justify-content-center text-center">
                                <div class="mb-1 text-muted">Current Task Score</div>
                                <div class="display-6 fw-bold text-primary">{{ $taskScore }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-7">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-2">Score Breakdown</div>
                                @if(!empty($scoreExplanations))
                                <ul class="mb-0 ps-3">
                                    @foreach($scoreExplanations as $explanation)
                                    <li class="mb-1">{!! $explanation !!}</li>
                                    @endforeach
                                </ul>
                                @else
                                <div class="text-muted">No score components yet.</div>
                                @endif
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
                            <div class="text-end">
                                <span class="fw-semibold">{{ $task->started_at->format('M d, Y') }}</span>
                                @if($task->assigned_at)
                                @php
                                $assignedToStarted = $task->assigned_at->diffInDays($task->started_at);
                                @endphp
                                <br><small class="text-info">+{{ $assignedToStarted }}d from assignment</small>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($task->completed_at)
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <small class="text-muted">Completed</small>
                            <div class="text-end">
                                <span class="fw-semibold text-success">{{ $task->completed_at->format('M d, Y')
                                    }}</span>
                                @if($task->started_at)
                                @php
                                $startedToCompleted = $task->started_at->diffInDays($task->completed_at);
                                @endphp
                                <br><small class="text-success">+{{ $startedToCompleted }}d from start</small>
                                @endif
                                @if($task->assigned_at)
                                @php
                                $totalDuration = $task->assigned_at->diffInDays($task->completed_at);
                                @endphp
                                <br><small class="text-primary"><strong>Total: {{ $totalDuration }}d</strong></small>
                                @endif
                            </div>
                        </div>
                        @elseif($task->started_at)
                        {{-- Show current duration for in-progress tasks --}}
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <small class="text-muted">Current Duration</small>
                            <div class="text-end">
                                @if($task->assigned_at)
                                @php
                                $currentDuration = $task->assigned_at->diffInDays(now());
                                @endphp
                                <span class="fw-semibold text-warning">{{ $currentDuration }}d</span>
                                <br><small class="text-muted">since assignment</small>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Require Resubmission Modal -->
<div class="modal fade" id="requireResubmitModal" tabindex="-1" aria-labelledby="requireResubmitModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="requireResubmitModalLabel">
                    <i class="bx bx-refresh me-2"></i>Request Task Resubmission
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="requireResubmitForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Task Control:</strong> You are taking control of this task to request changes from the
                        user. The task will be returned to the user for resubmission.
                    </div>

                    <!-- Task Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Task Title:</label>
                            <p class="form-control-plaintext">{{ $task->title }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assigned To:</label>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="form-control-plaintext mb-0">{{ $task->assignee->name ?? 'Unassigned' }}</p>
                                @if(auth()->user()->role === 'manager' || auth()->user()->role === 'admin')
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="showReassignModal()">
                                    <i class="bx bx-transfer me-1"></i>Reassign
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Current Status -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Status:</label>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-info me-2">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            @if($task->combined_response_status)
                            <span class="badge bg-secondary">{{ $task->combined_response_status }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Client/Consultant Feedback -->
                    @if($task->combined_response_status)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Client/Consultant Feedback:</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    @if($task->client_response_notes)
                                    <div class="col-md-6">
                                        <strong class="text-primary">Client Notes:</strong>
                                        <p class="mb-0 text-muted">{{ $task->client_response_notes }}</p>
                                    </div>
                                    @endif
                                    @if($task->consultant_response_notes)
                                    <div class="col-md-6">
                                        <strong class="text-info">Consultant Notes:</strong>
                                        <p class="mb-0 text-muted">{{ $task->consultant_response_notes }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Resubmission Notes -->
                    <div class="mb-3">
                        <label for="resubmit_notes" class="form-label fw-semibold">
                            <i class="bx bx-message-detail me-1"></i>Resubmission Instructions <span
                                class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="resubmit_notes" name="resubmit_notes" rows="4"
                            placeholder="Explain what changes are needed. Be specific about requirements, corrections, or improvements..."
                            required></textarea>
                        <div class="form-text">Provide clear instructions for the user on what needs to be changed or
                            improved.</div>
                    </div>

                    <!-- File Uploads -->
                    <div class="mb-3">
                        <label for="resubmit_attachments" class="form-label fw-semibold">
                            <i class="bx bx-paperclip me-1"></i>Attach Files (Optional)
                        </label>
                        <input type="file" class="form-control" id="resubmit_attachments" name="resubmit_attachments[]"
                            multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar">
                        <div class="form-text">Upload reference files, examples, or documents that will help the user
                            understand the requirements.</div>
                    </div>

                    <!-- Priority Level -->
                    <div class="mb-3">
                        <label for="resubmit_priority" class="form-label fw-semibold">
                            <i class="bx bx-flag me-1"></i>Priority Level
                        </label>
                        <select class="form-select" id="resubmit_priority" name="resubmit_priority">
                            <option value="normal">Normal Priority</option>
                            <option value="high">High Priority</option>
                            <option value="urgent">Urgent Priority</option>
                        </select>
                        <div class="form-text">Set the priority level for this resubmission request.</div>
                    </div>

                    <!-- Due Date for Resubmission -->
                    <div class="mb-3">
                        <label for="resubmit_due_date" class="form-label fw-semibold">
                            <i class="bx bx-calendar me-1"></i>Resubmission Due Date (Optional)
                        </label>
                        <input type="date" class="form-control" id="resubmit_due_date" name="resubmit_due_date"
                            min="{{ date('Y-m-d') }}">
                        <div class="form-text">Set a specific due date for the resubmission. Leave empty for no specific
                            deadline.</div>
                    </div>

                    <!-- Action Summary -->
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <i class="bx bx-info-circle me-1"></i>What will happen:
                        </h6>
                        <ul class="mb-0">
                            <li>Task status will change to <strong>"Re-submit Required"</strong></li>
                            <li>User will be notified to make the requested changes</li>
                            <li>User will be able to upload new files and resubmit</li>
                            <li>Task will return to <strong>"Submitted for Review"</strong> status after resubmission
                            </li>
                            <li>All actions will be recorded in task history</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-refresh me-1"></i>Request Resubmission
                    </button>
                </div>
            </form>
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
                            @foreach(\App\Models\User::where('id', '!=', auth()->id())->whereIn('role', ['user',
                            'sub-admin'])->orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
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
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="submitted_for_review">Submitted for Review</option>
                            <option value="in_review">In Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
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
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Manager Controls Styling */
    .manager-controls {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .manager-controls .form-check-label {
        font-weight: 600;
        color: #495057;
        margin-left: 8px;
        font-size: 0.9rem;
    }

    .manager-controls .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }

    .manager-controls .form-check-input {
        transform: scale(1.1);
    }

    /* Required Badge Styling */
    .required-badge .badge {
        font-size: 0.8rem;
        padding: 6px 10px;
    }

    /* Toast Container */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055;
    }

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
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

    .bg-label-primary {
        background-color: rgba(105, 108, 255, 0.1) !important;
        color: #696cff !important;
    }

    .bg-label-info {
        background-color: rgba(67, 89, 126, 0.1) !important;
        color: #43597e !important;
    }

    .bg-label-success {
        background-color: rgba(114, 225, 40, 0.1) !important;
        color: #72e128 !important;
    }

    .bg-label-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
        color: #ffc107 !important;
    }

    .bg-label-danger {
        background-color: rgba(220, 53, 69, 0.1) !important;
        color: #dc3545 !important;
    }

    .bg-label-secondary {
        background-color: rgba(108, 117, 125, 0.1) !important;
        color: #6c757d !important;
    }

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

// Submit for review
function submitForReview(taskId) {
    // Set the form action
    const form = document.getElementById('submitReviewForm');
    if (form) {
        form.action = `/tasks/${taskId}/submit-review`;
    } else {
        alert('Form not found. Please refresh the page and try again.');
        return;
    }

    // Show the modal
    const modal = document.getElementById('submitReviewModal');
    if (modal) {
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    } else {
        alert('Modal not found. Please refresh the page and try again.');
    }
}



// Approve task
function approveTask(taskId) {
    try {
        const form = document.getElementById('approveTaskForm');
        const modal = document.getElementById('approveTaskModal');

        if (!form) {
            console.error('Approve form not found');
            alert('Error: Approval form not found. Please refresh the page.');
            return;
        }

        if (!modal) {
            console.error('Approve modal not found');
            alert('Error: Approval modal not found. Please refresh the page.');
            return;
        }

        // Set the form action
        form.action = `/tasks/${taskId}/approve`;

        // Ensure CSRF token is present
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = form.querySelector('input[name="_token"]');
            if (csrfInput) {
                csrfInput.value = csrfToken.getAttribute('content');
            }
        }

        // Show the modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();

        console.log('Approval modal opened for task:', taskId);
        console.log('Form action set to:', form.action);
        console.log('CSRF token:', csrfToken ? csrfToken.getAttribute('content') : 'Not found');

        // Add form submission handler with AJAX
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            console.log('Form submission started for task:', taskId);

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Approving...';

            // Prepare form data
            const formData = new FormData(form);
            console.log('Form data:', Object.fromEntries(formData));

            // Submit via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);

                if (response.ok) {
                    return response.json().then(data => {
                        console.log('Approval successful!', data);
                        // Close modal
                        bootstrapModal.hide();
                        // Show success message
                        showMessage(data.message || 'Task approved successfully!', 'success');
                        // Reload page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    });
                } else {
                    // For error responses, try to get JSON first, then fallback to text
                    return response.text().then(text => {
                        console.error('Approval failed:', text);
                        try {
                            const data = JSON.parse(text);
                            throw new Error(data.message || 'Approval failed');
                        } catch (parseError) {
                            throw new Error(text || 'Approval failed');
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Approval error:', error);
                showMessage('Failed to approve task: ' + error.message, 'error');

                // Reset button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });

    } catch (error) {
        console.error('Error opening approval modal:', error);
        alert('Error opening approval dialog. Please refresh the page and try again.');
    }
}

// Function to show messages
function showMessage(message, type = 'info') {
    // Remove any existing messages
    const existingMessages = document.querySelectorAll('.alert-message');
    existingMessages.forEach(msg => msg.remove());

    // Create new message
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show alert-message`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';

    alertDiv.innerHTML = `
        <i class="bx bx-${type === 'success' ? 'check-circle' : type === 'error' ? 'error' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    document.body.appendChild(alertDiv);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Reject task
function rejectTask(taskId) {
    try {
        const form = document.getElementById('rejectTaskForm');
        const modal = document.getElementById('rejectTaskModal');

        if (!form) {
            console.error('Reject form not found');
            alert('Error: Rejection form not found. Please refresh the page.');
            return;
        }

        if (!modal) {
            console.error('Reject modal not found');
            alert('Error: Rejection modal not found. Please refresh the page.');
            return;
        }

        // Set the form action
        form.action = `/tasks/${taskId}/reject`;

        // Ensure CSRF token is present
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = form.querySelector('input[name="_token"]');
            if (csrfInput) {
                csrfInput.value = csrfToken.getAttribute('content');
            }
        }

        // Show the modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();

        console.log('Rejection modal opened for task:', taskId);
        console.log('Form action set to:', form.action);
        console.log('CSRF token:', csrfToken ? csrfToken.getAttribute('content') : 'Not found');

        // Add form submission handler with AJAX
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            console.log('Rejection form submission started for task:', taskId);

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Rejecting...';

            // Prepare form data
            const formData = new FormData(form);
            console.log('Form data:', Object.fromEntries(formData));

            // Submit via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);

                if (response.ok) {
                    return response.json().then(data => {
                        console.log('Rejection successful!', data);
                        // Close modal
                        bootstrapModal.hide();
                        // Show success message
                        showMessage(data.message || 'Task rejected successfully!', 'success');
                        // Reload page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    });
                } else {
                    // For error responses, try to get JSON first, then fallback to text
                    return response.text().then(text => {
                        console.error('Rejection failed:', text);
                        try {
                            const data = JSON.parse(text);
                            throw new Error(data.message || 'Rejection failed');
                        } catch (parseError) {
                            throw new Error(text || 'Rejection failed');
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Rejection error:', error);
                showMessage('Failed to reject task: ' + error.message, 'error');

                // Reset button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });

    } catch (error) {
        console.error('Error opening rejection modal:', error);
        alert('Error opening rejection dialog. Please refresh the page and try again.');
    }
}

// Send approval email
function sendApprovalEmail(taskId) {
    if (confirm('Send approval email to the assigned user?')) {
        fetch(`/tasks/${taskId}/send-approval-email`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Approval email sent successfully!');
                location.reload();
            } else {
                alert('Failed to send email: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending email. Please try again.');
        });
    }
}

// Send rejection email
function sendRejectionEmail(taskId) {
    if (confirm('Send rejection email to the assigned user?')) {
        fetch(`/tasks/${taskId}/send-rejection-email`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rejection email sent successfully!');
                location.reload();
            } else {
                alert('Failed to send email: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending email. Please try again.');
        });
    }
}

// Move to waiting sending approval
function moveToWaitingSendingApproval(taskId) {
    if (confirm('Move this task to waiting for sending client/consultant approval?')) {
        fetch(`/tasks/${taskId}/move-to-waiting-sending-approval`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Task moved to waiting for sending approval successfully!');
                location.reload();
            } else {
                alert('Failed to move task: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error moving task. Please try again.');
        });
    }
}

// Send for client/consultant approval
function sendForClientConsultantApproval(taskId) {
    if (confirm('Send this task for client and consultant approval?')) {
        fetch(`/tasks/${taskId}/send-for-client-consultant-approval`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Task sent for client/consultant approval successfully!');
                location.reload();
            } else {
                alert('Failed to send task: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending task. Please try again.');
        });
    }
}

// Mark task as completed (Manager action after client/consultant review)
function markAsCompleted(taskId) {
    // Show modal for completion notes
    const modal = document.getElementById('markCompletedModal');
    if (modal) {
        // Set up form submission
        const form = document.getElementById('markCompletedForm');
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                submitMarkCompleted(taskId);
            };
        }

        // Show Bootstrap modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    } else {
        // Fallback to prompt if modal doesn't exist
        const notes = prompt('Optional completion notes:');
        if (notes !== null) {
            submitMarkCompleted(taskId, notes);
        }
    }
}

function submitMarkCompleted(taskId, notes = null) {
    const formData = new FormData();

    if (notes === null) {
        // Get notes from modal form
        const notesInput = document.getElementById('completion_notes');
        if (notesInput) {
            notes = notesInput.value;
        }
    }

    if (notes) {
        formData.append('completion_notes', notes);
    }

    fetch(`/tasks/${taskId}/mark-completed`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);

        if (data.success) {
            // Close modal
            closeMarkCompletedModal();

            // Show success message
            alert('Task marked as completed successfully!');
            location.reload();
        } else {
            alert('Failed to mark task as completed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error marking task as completed. Please try again. Error: ' + error.message);
    });
}

function closeMarkCompletedModal() {
    const modal = document.getElementById('markCompletedModal');
    if (modal) {
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
            bootstrapModal.hide();
        }
    }
}

// Handle require resubmission form submission
document.getElementById('requireResubmitForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const taskId = {{ $task->id }};

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Processing...';
    submitBtn.disabled = true;

    fetch(`/tasks/${taskId}/require-resubmit`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('requireResubmitModal'));
            modal.hide();

            // Show success message
            showNotification('Task sent back for resubmission successfully!', 'success');

            // Reload page after a short delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Failed to require resubmission: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error requiring resubmission. Please try again.', 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Notification helper function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
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

    // Task History Accordion Toggle
    const historyCollapse = document.getElementById('taskHistoryCollapse');
    const historyToggleIcon = document.getElementById('historyToggleIcon');
    const historyToggleText = document.getElementById('historyToggleText');

    if (historyCollapse && historyToggleIcon && historyToggleText) {
        historyCollapse.addEventListener('show.bs.collapse', function() {
            historyToggleIcon.className = 'bx bx-chevron-down';
            historyToggleText.textContent = 'Minimize';
        });

        historyCollapse.addEventListener('hide.bs.collapse', function() {
            historyToggleIcon.className = 'bx bx-chevron-right';
            historyToggleText.textContent = 'Maximize';
        });
    }

});
</script>

<!-- Submit for Review Modal -->
<div class="modal fade" id="submitReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Task for Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="submitReviewForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Completion Notes</label>
                        <textarea name="completion_notes" class="form-control" rows="4"
                            placeholder="Describe what was completed, any challenges faced, or additional information..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit for Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Task Modal -->
<div class="modal fade" id="approveTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveTaskForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="bx bx-check-circle me-2"></i>
                        Are you sure you want to approve this task? This will mark it as completed and notify all
                        stakeholders.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Approval Notes (Optional)</label>
                        <textarea name="approval_notes" class="form-control" rows="3"
                            placeholder="Any additional comments or feedback..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Task Modal -->
<div class="modal fade" id="rejectTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectTaskForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-error me-2"></i>
                        Please provide feedback on why this task is being rejected. The assigned user will need to
                        address these issues and resubmit.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_notes" class="form-control" rows="4"
                            placeholder="Please explain why this task is being rejected and what needs to be improved..."
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Auto-save client and consultant responses when finishing review
document.addEventListener('DOMContentLoaded', function() {
    const finishReviewForm = document.getElementById('finishReviewForm');

    if (finishReviewForm) {
        finishReviewForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get current values from the dropdowns and textareas
            const clientStatusSelect = document.querySelector('select[name="client_response_status"]');
            const clientNotesTextarea = document.querySelector('textarea[name="client_response_notes"]');
            const consultantStatusSelect = document.querySelector('select[name="consultant_response_status"]');
            const consultantNotesTextarea = document.querySelector('textarea[name="consultant_response_notes"]');

            // Create hidden inputs to send with the finish review form
            if (clientStatusSelect) {
                const hiddenClientStatus = document.createElement('input');
                hiddenClientStatus.type = 'hidden';
                hiddenClientStatus.name = 'client_response_status';
                hiddenClientStatus.value = clientStatusSelect.value;
                finishReviewForm.appendChild(hiddenClientStatus);
            }

            if (clientNotesTextarea) {
                const hiddenClientNotes = document.createElement('input');
                hiddenClientNotes.type = 'hidden';
                hiddenClientNotes.name = 'client_response_notes';
                hiddenClientNotes.value = clientNotesTextarea.value;
                finishReviewForm.appendChild(hiddenClientNotes);
            }

            if (consultantStatusSelect) {
                const hiddenConsultantStatus = document.createElement('input');
                hiddenConsultantStatus.type = 'hidden';
                hiddenConsultantStatus.name = 'consultant_response_status';
                hiddenConsultantStatus.value = consultantStatusSelect.value;
                finishReviewForm.appendChild(hiddenConsultantStatus);
            }

            if (consultantNotesTextarea) {
                const hiddenConsultantNotes = document.createElement('input');
                hiddenConsultantNotes.type = 'hidden';
                hiddenConsultantNotes.name = 'consultant_response_notes';
                hiddenConsultantNotes.value = consultantNotesTextarea.value;
                finishReviewForm.appendChild(hiddenConsultantNotes);
            }

            // Now submit the form
            finishReviewForm.submit();
        });
    }
});

// Task Reassignment Functions
function showReassignModal() {
    // Show modal directly
    const modal = new bootstrap.Modal(document.getElementById('reassignTaskModal'));
    modal.show();
}

document.getElementById('reassignTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const newAssigneeId = document.getElementById('new_assignee_id').value;
    if (!newAssigneeId) {
        alert('Please select a user to reassign the task to');
        return;
    }

    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Reassigning...';
    submitButton.disabled = true;

    const formData = new FormData(this);

    fetch('{{ route("tasks.reassign", $task) }}', {
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
            bootstrap.Modal.getInstance(document.getElementById('reassignTaskModal')).hide();

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

            // Reload after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            alert('Error: ' + (data.message || 'Failed to reassign task'));
        }
    })
    .catch(error => {
        // Restore button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;

        console.error('Error:', error);
        alert('An error occurred while reassigning the task');
    });
});
</script>

<!-- Reassign Task Modal -->
<div class="modal fade" id="reassignTaskModal" tabindex="-1" aria-labelledby="reassignTaskModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reassignTaskModalLabel">
                    <i class="bx bx-transfer me-2"></i>Reassign Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reassignTaskForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_assignee_id" class="form-label">
                            New Assignee <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="new_assignee_id" name="new_assignee_id" required>
                            <option value="">Select a user...</option>
                            @foreach(\App\Models\User::where('status', 'active')->where('role', '!=',
                            'admin')->orderBy('name')->get() as $user)
                            @if($user->id !== $task->assignee_id)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reassignment_reason" class="form-label">
                            Reason for Reassignment
                        </label>
                        <textarea class="form-control" id="reassignment_reason" name="reassignment_reason" rows="3"
                            placeholder="Optional: Explain why this task is being reassigned"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        The new assignee will be notified about this task assignment.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-transfer me-1"></i>Reassign Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Completed Modal -->
<div class="modal fade" id="markCompletedModal" tabindex="-1" aria-labelledby="markCompletedModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="markCompletedModalLabel">
                    <i class="bx bx-check-circle me-2"></i>Mark Task as Completed
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeMarkCompletedModal()"
                    aria-label="Close"></button>
            </div>
            <form id="markCompletedForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Task Completion:</strong> You are about to mark this task as completed. This action will
                        finalize the task and notify all stakeholders.
                    </div>

                    <div class="mb-3">
                        <label for="completion_notes" class="form-label">
                            <i class="bx bx-note me-1"></i>Completion Notes (Optional)
                        </label>
                        <textarea class="form-control" id="completion_notes" name="completion_notes" rows="4"
                            placeholder="Add any final notes about the task completion..."></textarea>
                        <div class="form-text">These notes will be recorded in the task history and visible to all team
                            members.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeMarkCompletedModal()">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle me-1"></i>Mark as Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Time Extension Request Modal -->
<div class="modal fade" id="timeExtensionModal" tabindex="-1" aria-labelledby="timeExtensionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="timeExtensionModalLabel">
                    <i class="bx bx-time me-2"></i>Request Time Extension
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="timeExtensionForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Note:</strong> Your request will be sent to managers for review. Please provide a
                        detailed explanation for the extension needed.
                    </div>

                    <div class="mb-3">
                        <label for="requested_days" class="form-label">
                            Number of Days <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="requested_days" name="requested_days" min="1"
                            max="365" value="3" required>
                        <div class="form-text">Enter the number of additional days you need (1-365)</div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">
                            Reason for Extension <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="reason" name="reason" rows="4"
                            placeholder="Please explain why you need this time extension..." required></textarea>
                        <div class="form-text">Provide a detailed explanation to help managers understand your request.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-time me-1"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Time Extension Request
let currentTaskId = null;

function showTimeExtensionModal(taskId) {
    currentTaskId = taskId;
    const modal = new bootstrap.Modal(document.getElementById('timeExtensionModal'));
    modal.show();
}

document.getElementById('timeExtensionForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = {
        requested_days: document.getElementById('requested_days').value,
        reason: document.getElementById('reason').value
    };

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Submitting...';

    try {
        const response = await fetch(`/tasks/${currentTaskId}/request-time-extension`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('timeExtensionModal')).hide();

            // Show success message
            alert('Time extension request submitted successfully! Managers have been notified.');

            // Reload page to show updated status
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to submit request'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while submitting the request');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Reset form when modal is hidden
document.getElementById('timeExtensionModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('timeExtensionForm').reset();
    document.getElementById('requested_days').value = '3';
});

// Manager review time extension request
async function reviewTimeExtension(event, taskId, requestId) {
    event.preventDefault();

    const form = event.target;
    const action = form.querySelector(`input[name="action_${requestId}"]:checked`).value;
    const approvedDays = form.querySelector(`input[name="approved_days_${requestId}"]`).value;
    const managerNotes = form.querySelector(`textarea[name="manager_notes_${requestId}"]`).value;

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Processing...';

    try {
        const response = await fetch(`/tasks/${taskId}/review-time-extension`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                request_id: requestId,
                action: action,
                approved_days: action === 'approve' ? approvedDays : null,
                manager_notes: managerNotes
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Time extension ' + (action === 'approve' ? 'approved' : 'rejected') + ' successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to process request'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while processing the request');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}
</script>

@endsection
