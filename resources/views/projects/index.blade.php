@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Projects</h4>
            <p class="text-muted mb-0">{{ $projects->total() }} projects total</p>
        </div>
        <div class="d-flex gap-2">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary active" id="grid-view">
                    <i class="bx bx-grid-alt"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" id="list-view">
                    <i class="bx bx-list-ul"></i>
                </button>
            </div>
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>New Project
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search projects..." id="project-search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="status-filter">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="sort-filter">
                        <option value="name">Sort by Name</option>
                        <option value="created_at">Sort by Created</option>
                        <option value="start_date">Sort by Start Date</option>
                        <option value="status">Sort by Status</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" id="clear-filters">
                        <i class="bx bx-x me-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row grid-view" id="projects-grid">
        @foreach($projects as $project)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4 project-card"
                 data-status="{{ $project->status }}"
                 data-name="{{ strtolower($project->name) }}">
                <div class="card project-card-inner h-100 position-relative overflow-hidden"
                     style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e5e7eb;">

                    <!-- Project Link Overlay -->
                    <a href="{{ route('projects.show', $project) }}" class="stretched-link"></a>

                    <!-- Status Indicator Strip -->
                    <div class="position-absolute top-0 start-0 w-100 {{ [
                        'draft' => 'bg-secondary',
                        'active' => 'bg-success',
                        'on_hold' => 'bg-warning',
                        'completed' => 'bg-primary',
                        'cancelled' => 'bg-danger',
                    ][$project->status] ?? 'bg-secondary' }}" style="height: 4px;"></div>



                    <div class="card-body text-center d-flex flex-column p-4">
                        <!-- Grid View Content -->
                        <div class="grid-view-content">
                            <!-- Project Avatar with Flexible Title -->
                            @php($displayName = $project->short_code ? strtoupper($project->short_code) : strtoupper($project->name))
                            @php($colors = [
                                'draft' => ['bg' => 'linear-gradient(135deg, #f3f4f6, #e5e7eb)', 'text' => '#6b7280'],
                                'active' => ['bg' => 'linear-gradient(135deg, #d1fae5, #a7f3d0)', 'text' => '#059669'],
                                'on_hold' => ['bg' => 'linear-gradient(135deg, #fef3c7, #fde68a)', 'text' => '#d97706'],
                                'completed' => ['bg' => 'linear-gradient(135deg, #dbeafe, #bfdbfe)', 'text' => '#2563eb'],
                                'cancelled' => ['bg' => 'linear-gradient(135deg, #fee2e2, #fecaca)', 'text' => '#dc2626'],
                            ])
                            @php($projectColors = $colors[$project->status] ?? $colors['draft'])

                            <div class="position-relative mb-3">
                                <div class="project-title-container rounded-3 d-flex align-items-center justify-content-center mx-auto position-relative"
                                     style="min-width: 80px; min-height: 80px; max-width: 200px; background: {{ $projectColors['bg'] }}; color: {{ $projectColors['text'] }}; font-weight: 700; border: 2px solid rgba(255,255,255,0.8); box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 12px 16px; word-break: break-word; text-align: center; line-height: 1.2;">
                                    <span class="project-title-text" style="font-size: {{ strlen($displayName) <= 6 ? '24px' : (strlen($displayName) <= 12 ? '18px' : '14px') }};">
                                        {{ $displayName }}
                                    </span>
                                </div>
                                <!-- Progress Ring (if project has progress data) -->
                                @if(isset($project->progress))
                                <div class="progress-ring-container position-absolute top-0 start-50 translate-middle-x" style="pointer-events: none;">
                                    <svg width="100%" height="100%" viewBox="0 0 100 100" style="position: absolute; top: -10px; left: -10px; transform: rotate(-90deg);">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="2"/>
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="{{ $projectColors['text'] }}" stroke-width="2"
                                                stroke-dasharray="{{ 2 * 3.14159 * 45 }}"
                                                stroke-dashoffset="{{ 2 * 3.14159 * 45 * (1 - ($project->progress ?? 0) / 100) }}"
                                                stroke-linecap="round" opacity="0.6"/>
                                    </svg>
                                </div>
                                @endif
                            </div>

                            <!-- Project Info -->
                            <div class="flex-grow-1 d-flex flex-column">
                                <h5 class="card-title mb-2 fw-semibold text-truncate" style="min-height: 1.5rem;" title="{{ $project->name }}">
                                    {{ $project->name }}
                                </h5>

                                <p class="text-muted small mb-3 flex-grow-1" style="min-height: 2.5rem; line-height: 1.4;">
                                    {{ Str::limit($project->description ?? 'No description available', 80) }}
                                </p>

                                <!-- Status Badge -->
                                <div class="mb-3">
                                    <span class="badge rounded-pill {{ [
                                        'draft' => 'bg-label-secondary',
                                        'active' => 'bg-label-success',
                                        'on_hold' => 'bg-label-warning',
                                        'completed' => 'bg-label-primary',
                                        'cancelled' => 'bg-label-danger',
                                    ][$project->status] ?? 'bg-label-secondary' }} px-3 py-2">
                                        <i class="bx {{ [
                                            'draft' => 'bx-edit-alt',
                                            'active' => 'bx-play',
                                            'on_hold' => 'bx-pause',
                                            'completed' => 'bx-check',
                                            'cancelled' => 'bx-x',
                                        ][$project->status] ?? 'bx-circle' }} me-1" style="font-size: 12px;"></i>
                                        {{ [
                                            'draft' => 'Draft',
                                            'active' => 'Active',
                                            'on_hold' => 'On Hold',
                                            'completed' => 'Completed',
                                            'cancelled' => 'Cancelled',
                                        ][$project->status] ?? ucfirst(str_replace('_',' ', (string) $project->status)) }}
                                    </span>
                                </div>

                                <!-- Project Meta Info -->
                                <div class="small text-start mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-calendar text-muted me-2"></i>
                                            <span class="text-muted">Start:</span>
                                        </div>
                                        <span class="fw-medium">{{ optional($project->start_date)->format('M d, Y') ?: '—' }}</span>
                                    </div>

                                    @if($project->end_date)
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-calendar-check text-muted me-2"></i>
                                            <span class="text-muted">End:</span>
                                        </div>
                                        <span class="fw-medium">{{ $project->end_date->format('M d, Y') }}</span>
                                    </div>

                                    <!-- Days Counter -->
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bx {{ $project->is_overdue ? 'bx-time-five text-danger' : 'bx-timer text-success' }} me-2"></i>
                                            <span class="text-muted">{{ $project->is_overdue ? 'Overdue:' : 'Remaining:' }}</span>
                                        </div>
                                        <span class="fw-medium {{ $project->is_overdue ? 'text-danger' : 'text-success' }}">
                                            {{ $project->is_overdue ? $project->days_past . ' days' : $project->days_remaining . ' days' }}
                                        </span>
                                    </div>
                                    @endif

                                    @if(isset($project->team_members_count))
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-group text-muted me-2"></i>
                                            <span class="text-muted">Team:</span>
                                        </div>
                                        <span class="fw-medium">{{ $project->team_members_count }} members</span>
                                    </div>
                                    @endif

                                    @if(isset($project->tasks_count))
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-task text-muted me-2"></i>
                                            <span class="text-muted">Tasks:</span>
                                        </div>
                                        <span class="fw-medium">{{ $project->completed_tasks_count ?? 0 }}/{{ $project->tasks_count }}</span>
                                    </div>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2 mt-auto">
                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-primary flex-fill position-relative" style="z-index: 2;">
                                        <i class="bx bxs-show me-1"></i>Open
                                    </a>
                                    <a href="{{ route('projects.edit', $project) }}"
                                       class="btn btn-outline-secondary position-relative"
                                       style="z-index: 2;"
                                       onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('projects.edit', $project) }}';">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    @if(auth()->user()->canDelete())
                                        <button type="button"
                                                class="btn btn-outline-danger position-relative"
                                                style="z-index: 2;"
                                                onclick="event.preventDefault(); event.stopPropagation(); confirmDeleteProject('{{ $project->id }}', '{{ addslashes($project->name) }}', {{ $project->tasks()->count() }}, {{ $project->folders()->count() }});">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @elseif(auth()->user()->isSubAdmin())
                                        @include('partials.delete-request-button', [
                                            'type' => 'project',
                                            'id' => $project->id,
                                            'label' => $project->name,
                                            'class' => 'btn btn-outline-danger position-relative',
                                            'attributes' => 'style="z-index: 2;" onclick="event.preventDefault(); event.stopPropagation();" ',
                                            'text' => '',
                                            'icon' => 'bx bx-trash'
                                        ])
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- List View Content -->
                        <div class="list-view-content" style="display: none;">
                            <!-- Project Info Column -->
                            <div class="project-info-column">
                                <div class="project-title-container rounded-3 d-flex align-items-center justify-content-center position-relative"
                                     style="background: {{ $projectColors['bg'] }}; color: {{ $projectColors['text'] }}; font-weight: 700; border: 1px solid rgba(255,255,255,0.8); padding: 8px;">
                                    <span class="project-title-text">{{ $displayName }}</span>
                                </div>
                                <div class="project-details">
                                    <h5 class="card-title" title="{{ $project->name }}">{{ $project->name }}</h5>
                                </div>
                            </div>

                            <!-- Description Column -->
                            <div class="description-column">
                                <p class="text-muted small">{{ Str::limit($project->description ?? 'No description available', 120) }}</p>
                            </div>

                            <!-- Status Column -->
                            <div class="status-column">
                                <span class="badge {{ [
                                    'draft' => 'bg-label-secondary',
                                    'active' => 'bg-label-success',
                                    'on_hold' => 'bg-label-warning',
                                    'completed' => 'bg-label-primary',
                                    'cancelled' => 'bg-label-danger',
                                ][$project->status] ?? 'bg-label-secondary' }}">
                                    <i class="bx {{ [
                                        'draft' => 'bx-edit-alt',
                                        'active' => 'bx-play',
                                        'on_hold' => 'bx-pause',
                                        'completed' => 'bx-check',
                                        'cancelled' => 'bx-x',
                                    ][$project->status] ?? 'bx-circle' }} me-1"></i>
                                    {{ [
                                        'draft' => 'Draft',
                                        'active' => 'Active',
                                        'on_hold' => 'On Hold',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ][$project->status] ?? ucfirst(str_replace('_',' ', (string) $project->status)) }}
                                </span>
                            </div>

                            <!-- Date Column -->
                            <div class="date-column">
                                <div class="small">{{ optional($project->start_date)->format('M d, Y') ?: '—' }}</div>
                                @if($project->end_date)
                                <div class="small text-muted">{{ $project->end_date->format('M d, Y') }}</div>
                                <div class="small {{ $project->is_overdue ? 'text-danger' : 'text-success' }}">
                                    {{ $project->is_overdue ? $project->days_past . ' days overdue' : $project->days_remaining . ' days left' }}
                                </div>
                                @endif
                            </div>

                            <!-- Team Column -->
                            <div class="team-column">
                                {{ isset($project->team_members_count) ? $project->team_members_count . ' members' : '—' }}
                            </div>

                            <!-- Actions Column -->
                            <div class="actions-column">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="btn btn-primary btn-sm position-relative"
                                   style="z-index: 2;"
                                   onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('projects.show', $project) }}';">
                                    <i class="bx bxs-show"></i>
                                </a>
                                <a href="{{ route('projects.edit', $project) }}"
                                   class="btn btn-outline-secondary btn-sm position-relative"
                                   style="z-index: 2;"
                                   onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('projects.edit', $project) }}';">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @if(auth()->user()->canDelete())
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm position-relative"
                                            style="z-index: 2;"
                                            onclick="event.preventDefault(); event.stopPropagation(); confirmDeleteProject('{{ $project->id }}', '{{ addslashes($project->name) }}', {{ $project->tasks()->count() }}, {{ $project->folders()->count() }});">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                @elseif(auth()->user()->isSubAdmin())
                                    @include('partials.delete-request-button', [
                                        'type' => 'project',
                                        'id' => $project->id,
                                        'label' => $project->name,
                                        'class' => 'btn btn-outline-danger btn-sm position-relative',
                                        'attributes' => 'style="z-index: 2;" onclick="event.preventDefault(); event.stopPropagation();" ',
                                        'text' => '',
                                        'icon' => 'bx bx-trash'
                                    ])
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Empty State -->
    @if($projects->isEmpty())
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="bx bx-folder-plus" style="font-size: 4rem; color: #d1d5db;"></i>
        </div>
        <h5 class="text-muted mb-2">No projects found</h5>
        <p class="text-muted mb-4">Get started by creating your first project</p>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>Create Project
        </a>
    </div>
    @endif

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $projects->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProjectModalLabel">
                    <i class="bx bx-error-circle text-danger me-2"></i>Confirm Project Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                <p>You are about to delete the project <strong id="projectNameToDelete"></strong>.</p>
                <p>This will permanently delete:</p>
                <ul class="list-unstyled">
                    <li><i class="bx bx-task text-primary me-2"></i><span id="tasksCountToDelete">0</span> tasks</li>
                    <li><i class="bx bx-folder text-warning me-2"></i><span id="foldersCountToDelete">0</span> folders</li>
                    <li><i class="bx bx-group text-info me-2"></i>All team member associations</li>
                    <li><i class="bx bx-folder-open text-secondary me-2"></i>Project files and directories</li>
                </ul>
                <p class="text-muted small">All data associated with this project will be permanently removed from the system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <form id="deleteProjectForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i>Delete Project
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Button group styling */
.btn-group .btn.active {
    background-color: #696cff;
    border-color: #696cff;
    color: white;
    box-shadow: 0 2px 4px rgba(105, 108, 255, 0.4);
}

.btn-group .btn:not(.active) {
    background-color: transparent;
    border-color: #d9dee3;
    color: #697a8d;
}

.btn-group .btn:not(.active):hover {
    background-color: #f5f5f9;
    border-color: #696cff;
    color: #696cff;
}

.project-card-inner {
    cursor: pointer;
    border-radius: 16px;
}

.project-card-inner:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    border-color: #d1d5db !important;
}

.project-card-inner:hover .position-absolute.top-2.end-2 {
    opacity: 1;
}

.position-absolute.top-2.end-2 {
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.btn-ghost-secondary {
    background: rgba(255,255,255,0.9);
    border: 1px solid rgba(0,0,0,0.08);
    backdrop-filter: blur(10px);
}

.btn-ghost-secondary:hover {
    background: rgba(255,255,255,1);
    border-color: rgba(0,0,0,0.15);
}

.project-title-container {
    transition: all 0.3s ease;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-wrap: wrap;
    width: auto !important;
    height: auto !important;
}

.project-title-text {
    display: block;
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 100%;
    text-align: center;
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
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

/* Responsive font sizing for different title lengths */
.project-title-container .project-title-text {
    transition: font-size 0.3s ease;
}

/* For very long titles, allow wrapping */
@media (max-width: 576px) {
    .project-title-text {
        white-space: normal !important;
        word-wrap: break-word;
        hyphens: auto;
        font-size: 12px !important;
    }

    .project-title-container {
        min-width: 60px !important;
        min-height: 60px !important;
        font-size: 12px !important;
        padding: 8px 12px !important;
        max-width: 120px !important;
        text-align: center;
    }
}

/* Grid view responsive sizing */
.grid-view .project-title-container {
    min-width: 80px;
    min-height: 80px;
    max-width: 200px;
    width: auto;
    padding: 12px 16px;
}

.grid-view .project-title-text {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
    text-align: center;
}

.progress-ring-container {
    width: calc(100% + 20px);
    height: calc(100% + 20px);
    max-width: 120px;
    max-height: 120px;
}

/* Grid View Styles */
.grid-view .project-card {
    opacity: 1;
    transition: opacity 0.3s ease;
}

.grid-view .project-card.hidden {
    opacity: 0;
    pointer-events: none;
    position: absolute;
    z-index: -1;
}

.grid-view .list-view-content {
    display: none !important;
}

.grid-view .grid-view-content {
    display: block !important;
}

/* List View Styles - Table Format */
.list-view {
    display: flex;
    flex-direction: column;
    gap: 0;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
}

.list-view .grid-view-content {
    display: none !important;
}

.list-view .list-view-content {
    display: contents !important;
}

/* Table Header */
.list-view::before {
    content: 'Project Description Status Date Team Actions';
    display: grid;
    grid-template-columns: 1.8fr 1.5fr 130px 140px 100px 140px;
    gap: 1.5rem;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(to right, #f8f9fa, #fafbfc);
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #4b5563;
    letter-spacing: 0.05em;
    align-items: center;
    white-space: pre;
}

.list-view .project-card {
    width: 100%;
    margin-bottom: 0;
    border-bottom: 1px solid #f0f2f4;
    background: white;
    transition: all 0.2s ease;
}

.list-view .project-card:last-child {
    border-bottom: none;
}

.list-view .project-card:hover {
    background: linear-gradient(to right, #fefbff, #f8f9fa);
    border-color: #ddd6fe;
    box-shadow: inset 0 0 0 1px rgba(105, 108, 255, 0.1);
}

.list-view .project-card .card {
    border: none;
    border-radius: 0;
    box-shadow: none;
    background: transparent;
    height: auto;
}

.list-view .project-card .card-body {
    padding: 1.25rem 1.5rem;
    display: grid;
    grid-template-columns: 1.8fr 1.5fr 130px 140px 100px 140px;
    gap: 1.5rem;
    align-items: center;
    height: auto;
    min-height: 85px;
}

/* Project Info Column */
.list-view .project-info-column {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 0;
}

.list-view .project-title-container {
    min-width: 48px !important;
    min-height: 48px !important;
    max-width: 150px !important;
    max-height: 48px !important;
    flex-shrink: 0;
    border: 2px solid rgba(0, 0, 0, 0.05);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.list-view .project-title-text {
    font-size: 12px !important;
    line-height: 1.2;
    font-weight: 700;
    white-space: normal !important;
    word-wrap: break-word;
    hyphens: auto;
    overflow-wrap: break-word;
    max-width: 100%;
    text-align: center;
}

.list-view .project-details {
    min-width: 0;
    flex: 1;
}

.list-view .card-title {
    margin-bottom: 0.35rem;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.4;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-view .card-title:hover {
    color: #696cff;
}

/* Description Column */
.list-view .description-column {
    display: flex;
    align-items: center;
    min-width: 0;
}

.list-view .description-column .text-muted {
    font-size: 0.813rem;
    line-height: 1.5;
    color: #6b7280;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 100%;
}

/* Status Column */
.list-view .status-column {
    display: flex;
    justify-content: flex-start;
}

.list-view .badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.85rem;
    border-radius: 8px;
    font-weight: 500;
    white-space: nowrap;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Date Column */
.list-view .date-column {
    font-size: 0.813rem;
    color: #374151;
    font-weight: 500;
    line-height: 1.5;
}

.list-view .date-column .text-muted {
    color: #9ca3af;
    font-size: 0.75rem;
}

.list-view .date-column .text-danger,
.list-view .date-column .text-success {
    font-size: 0.75rem;
    font-weight: 600;
}

/* Team Column */
.list-view .team-column {
    font-size: 0.813rem;
    color: #4b5563;
    text-align: center;
    font-weight: 500;
}

/* Actions Column */
.list-view .actions-column {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    align-items: center;
}

.list-view .btn-sm {
    padding: 0.5rem;
    font-size: 0.813rem;
    border-radius: 8px;
    font-weight: 500;
    min-width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.list-view .btn-primary.btn-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(105, 108, 255, 0.3);
}

.list-view .btn-outline-secondary.btn-sm:hover {
    background-color: #f3f4f6;
    border-color: #696cff;
    color: #696cff;
}

.list-view .btn-outline-danger.btn-sm:hover {
    background-color: #fee2e2;
    border-color: #ef4444;
    color: #dc2626;
}

.list-view .project-card-inner {
    border-radius: 0;
    border: none;
    height: auto;
}

.list-view .project-card-inner:hover {
    transform: none;
    box-shadow: none !important;
    border-color: transparent !important;
}

.list-view .position-absolute.top-2.end-2 {
    position: static;
    opacity: 1;
}

.list-view .position-absolute.top-2.end-2 button {
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 6px;
}

/* Hide elements not needed in table view */
.list-view .position-absolute.top-0.start-0,
.list-view .progress-ring-container,
.list-view .small.text-start,
.list-view .flex-grow-1 {
    display: none;
}

/* Responsive table */
@media (max-width: 1400px) {
    .list-view::before {
        grid-template-columns: 1.5fr 1fr 120px 120px 100px;
        content: 'Project Description Status Date Actions';
    }

    .list-view .project-card .card-body {
        grid-template-columns: 1.5fr 1fr 120px 120px 100px;
        gap: 1.25rem;
    }

    .list-view .team-column {
        display: none;
    }
}

@media (max-width: 1024px) {
    .list-view::before {
        grid-template-columns: 1.5fr 100px 110px 110px;
        content: 'Project Status Date Actions';
    }

    .list-view .project-card .card-body {
        grid-template-columns: 1.5fr 100px 110px 110px;
        gap: 1rem;
    }

    .list-view .description-column {
        display: none;
    }

    .list-view .project-details .card-title {
        font-size: 0.938rem;
    }

    .list-view .project-title-container {
        max-width: 120px !important;
    }
}

@media (max-width: 768px) {
    .list-view::before {
        grid-template-columns: 1fr 100px 100px;
        content: 'Project Status Actions';
        padding: 1rem;
        gap: 0.75rem;
    }

    .list-view .project-card .card-body {
        grid-template-columns: 1fr 100px 100px;
        padding: 1rem;
        gap: 0.75rem;
        min-height: 75px;
    }

    .list-view .date-column,
    .list-view .team-column {
        display: none;
    }

    .list-view .badge {
        font-size: 0.688rem;
        padding: 0.3rem 0.65rem;
    }

    .list-view .project-title-container {
        min-width: 40px !important;
        min-height: 40px !important;
        max-width: 100px !important;
        max-height: 40px !important;
    }

    .list-view .project-title-text {
        font-size: 10px !important;
    }

    .list-view .card-title {
        font-size: 0.875rem;
    }
}

@media (max-width: 480px) {
    .list-view::before {
        grid-template-columns: 1fr 80px;
        content: 'Project Actions';
        padding: 0.875rem;
    }

    .list-view .project-card .card-body {
        grid-template-columns: 1fr 80px;
        padding: 0.875rem;
        gap: 0.625rem;
        min-height: 70px;
    }

    .list-view .status-column,
    .list-view .date-column,
    .list-view .team-column,
    .list-view .description-column {
        display: none;
    }

    .list-view .btn-outline-secondary {
        display: none;
    }

    .list-view .project-info-column {
        gap: 0.625rem;
    }

    .list-view .project-title-container {
        min-width: 36px !important;
        min-height: 36px !important;
        max-width: 80px !important;
        max-height: 36px !important;
    }

    .list-view .project-title-text {
        font-size: 9px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const gridViewBtn = document.getElementById('grid-view');
    const listViewBtn = document.getElementById('list-view');
    const projectsGrid = document.getElementById('projects-grid');

    // Grid view handler
    gridViewBtn.addEventListener('click', function() {
        gridViewBtn.classList.add('active');
        listViewBtn.classList.remove('active');
        projectsGrid.classList.remove('list-view');
        projectsGrid.classList.add('grid-view');

        // Reset grid classes
        projectsGrid.className = 'row grid-view';

        // Update individual cards
        document.querySelectorAll('.project-card').forEach(card => {
            card.className = 'col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4 project-card';
            card.dataset.status = card.dataset.status; // Keep status data
            card.dataset.name = card.dataset.name; // Keep name data

            // Show grid content, hide list content
            const gridContent = card.querySelector('.grid-view-content');
            const listContent = card.querySelector('.list-view-content');
            const cardBody = card.querySelector('.card-body');

            if (gridContent) gridContent.style.display = 'block';
            if (listContent) listContent.style.display = 'none';
            if (cardBody) {
                cardBody.className = 'card-body text-center d-flex flex-column p-4';
            }
        });
    });

    // List view handler
    listViewBtn.addEventListener('click', function() {
        listViewBtn.classList.add('active');
        gridViewBtn.classList.remove('active');
        projectsGrid.classList.remove('grid-view');
        projectsGrid.classList.add('list-view');

        // Update container for list view
        projectsGrid.className = 'list-view';

        // Update individual cards
        document.querySelectorAll('.project-card').forEach(card => {
            card.className = 'project-card';

            // Show list content, hide grid content
            const gridContent = card.querySelector('.grid-view-content');
            const listContent = card.querySelector('.list-view-content');
            const cardBody = card.querySelector('.card-body');

            if (gridContent) gridContent.style.display = 'none';
            if (listContent) listContent.style.display = 'contents';
            if (cardBody) {
                cardBody.className = 'card-body';
            }
        });
    });

    // Search functionality
    const searchInput = document.getElementById('project-search');
    const statusFilter = document.getElementById('status-filter');
    const sortFilter = document.getElementById('sort-filter');
    const clearButton = document.getElementById('clear-filters');
    const projectCards = document.querySelectorAll('.project-card');

    function filterProjects() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        projectCards.forEach(card => {
            const name = card.dataset.name;
            const status = card.dataset.status;

            const matchesSearch = !searchTerm || name.includes(searchTerm);
            const matchesStatus = !statusValue || status === statusValue;

            if (matchesSearch && matchesStatus) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    searchInput.addEventListener('input', filterProjects);
    statusFilter.addEventListener('change', filterProjects);

    clearButton.addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = '';
        sortFilter.value = 'name';
        filterProjects();
    });

    // Prevent card click when interacting with dropdown
    document.querySelectorAll('.dropdown-toggle, .dropdown-menu').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});

// Function to confirm project deletion
function confirmDeleteProject(projectId, projectName, tasksCount, foldersCount) {
    // Update modal content
    document.getElementById('projectNameToDelete').textContent = projectName;
    document.getElementById('tasksCountToDelete').textContent = tasksCount;
    document.getElementById('foldersCountToDelete').textContent = foldersCount;

    // Update form action
    const form = document.getElementById('deleteProjectForm');
    form.action = `/projects/${projectId}`;

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteProjectModal'));
    modal.show();
}
</script>
@endsection
