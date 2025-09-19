@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
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
                                    <button class="btn btn-primary flex-fill position-relative" style="z-index: 2;">
                                        <i class="bx bx-right-arrow-alt me-1"></i>Open
                                    </button>
                                    <a href="{{ route('projects.edit', $project) }}"
                                       class="btn btn-outline-secondary position-relative"
                                       style="z-index: 2;"
                                       onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('projects.edit', $project) }}';">
                                        <i class="bx bx-edit"></i>
                                    </a>
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
                                    <p class="text-muted small">{{ Str::limit($project->description ?? 'No description available', 50) }}</p>
                                </div>
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
                                {{ optional($project->start_date)->format('M d, Y') ?: '—' }}
                            </div>

                            <!-- Team Column -->
                            <div class="team-column">
                                {{ isset($project->team_members_count) ? $project->team_members_count . ' members' : '—' }}
                            </div>

                            <!-- Actions Column -->
                            <div class="actions-column">
                                <button class="btn btn-primary btn-sm position-relative" style="z-index: 2;">
                                    <i class="bx bx-right-arrow-alt"></i>
                                </button>
                                <a href="{{ route('projects.edit', $project) }}"
                                   class="btn btn-outline-secondary btn-sm position-relative"
                                   style="z-index: 2;"
                                   onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('projects.edit', $project) }}';">
                                    <i class="bx bx-edit"></i>
                                </a>

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
        {{ $projects->links() }}
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
    white-space: nowrap;
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
    }

    .project-title-container {
        min-width: 60px !important;
        min-height: 60px !important;
        font-size: 12px !important;
        padding: 8px 12px !important;
    }
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
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.list-view .grid-view-content {
    display: none !important;
}

.list-view .list-view-content {
    display: contents !important;
}

/* Table Header */
.list-view::before {
    content: 'Project Status Start Date Team Actions';
    display: grid;
    grid-template-columns: 2fr 120px 120px 80px 120px;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #6b7280;
    letter-spacing: 0.05em;
    align-items: center;
    white-space: pre;
}

.list-view .project-card {
    width: 100%;
    margin-bottom: 0;
    border-bottom: 1px solid #f3f4f6;
    background: white;
    transition: background-color 0.2s ease;
}

.list-view .project-card:last-child {
    border-bottom: none;
}

.list-view .project-card:hover {
    background: #f8f9fb;
}

.list-view .project-card .card {
    border: none;
    border-radius: 0;
    box-shadow: none;
    background: transparent;
    height: auto;
}

.list-view .project-card .card-body {
    padding: 1rem;
    display: grid;
    grid-template-columns: 2fr 120px 120px 80px 120px;
    gap: 1rem;
    align-items: center;
    height: auto;
    min-height: 70px;
}

/* Project Info Column */
.list-view .project-info-column {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.list-view .project-title-container {
    min-width: 40px !important;
    min-height: 40px !important;
    max-width: 40px !important;
    max-height: 40px !important;
    flex-shrink: 0;
}

.list-view .project-title-text {
    font-size: 12px !important;
    line-height: 1.2;
}

.list-view .project-details {
    min-width: 0;
    flex: 1;
}

.list-view .card-title {
    margin-bottom: 0.25rem;
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.3;
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-view .text-muted.small {
    font-size: 0.75rem;
    line-height: 1.3;
    color: #6b7280;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Status Column */
.list-view .status-column {
    display: flex;
    justify-content: flex-start;
}

.list-view .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-weight: 500;
    white-space: nowrap;
}

/* Date Column */
.list-view .date-column {
    font-size: 0.8rem;
    color: #374151;
    font-weight: 500;
}

/* Team Column */
.list-view .team-column {
    font-size: 0.8rem;
    color: #6b7280;
    text-align: center;
}

/* Actions Column */
.list-view .actions-column {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    align-items: center;
}

.list-view .btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 6px;
    font-weight: 500;
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
    width: 28px;
    height: 28px;
    padding: 0;
    border-radius: 4px;
}

/* Hide elements not needed in table view */
.list-view .position-absolute.top-0.start-0,
.list-view .progress-ring-container,
.list-view .small.text-start,
.list-view .flex-grow-1 {
    display: none;
}

/* Responsive table */
@media (max-width: 1024px) {
    .list-view::before {
        grid-template-columns: 2fr 100px 100px 100px;
        content: 'Project Status Date Actions';
    }

    .list-view .project-card .card-body {
        grid-template-columns: 2fr 100px 100px 100px;
    }

    .list-view .team-column {
        display: none;
    }
}

@media (max-width: 768px) {
    .list-view::before {
        grid-template-columns: 1fr 80px 80px;
        content: 'Project Status Actions';
    }

    .list-view .project-card .card-body {
        grid-template-columns: 1fr 80px 80px;
        padding: 0.75rem;
        gap: 0.75rem;
    }

    .list-view .date-column,
    .list-view .team-column {
        display: none;
    }

    .list-view .badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
    }

    .list-view .project-title-container {
        min-width: 32px !important;
        min-height: 32px !important;
        max-width: 32px !important;
        max-height: 32px !important;
    }

    .list-view .project-title-text {
        font-size: 10px !important;
    }
}

@media (max-width: 480px) {
    .list-view::before {
        grid-template-columns: 1fr 60px;
        content: 'Project Actions';
    }

    .list-view .project-card .card-body {
        grid-template-columns: 1fr 60px;
        padding: 0.75rem;
        gap: 0.5rem;
        min-height: 60px;
    }

    .list-view .status-column,
    .list-view .date-column,
    .list-view .team-column {
        display: none;
    }

    .list-view .actions-column .btn-outline-secondary {
        display: none;
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
</script>
@endsection
