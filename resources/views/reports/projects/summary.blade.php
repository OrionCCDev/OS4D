@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Project Summary Report</h4>
            <p class="text-muted">{{ $projectData['project']->name }} - {{ $projectData['project']->short_code }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.projects.summary.pdf', $projectData['project']->id) }}"
               class="btn btn-primary">
                <i class="bx bx-download me-1"></i>Export PDF
            </a>
            <a href="{{ route('reports.projects') }}"
               class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to Projects
            </a>
        </div>
    </div>

    <!-- Project Overview Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Project Overview</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Project Name:</strong></td>
                            <td>{{ $projectData['project']->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Project Code:</strong></td>
                            <td>{{ $projectData['project']->short_code ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $projectData['project']->status === 'active' ? 'success' : ($projectData['project']->status === 'completed' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($projectData['project']->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Owner:</strong></td>
                            <td>{{ $projectData['project']->owner->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Created Date:</strong></td>
                            <td>{{ $projectData['projectTimeline']['created_at']->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Due Date:</strong></td>
                            <td>
                                @if($projectData['projectTimeline']['due_date'])
                                    {{ \Carbon\Carbon::parse($projectData['projectTimeline']['due_date'])->format('M d, Y') }}
                                @else
                                    <span class="text-muted">No due date</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Project Duration:</strong></td>
                            <td>
                                @if($projectData['managerPlannedDuration'])
                                    {{ $projectData['managerPlannedDuration'] }} days
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Completion Rate:</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 100px; height: 8px;">
                                        <div class="progress-bar" style="width: {{ $projectData['projectStats']['completion_rate'] }}%"></div>
                                    </div>
                                    <span class="small">{{ $projectData['projectStats']['completion_rate'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $projectData['projectStats']['total_tasks'] }}</h3>
                    <p class="text-muted mb-0">Total Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $projectData['projectStats']['completed_tasks'] }}</h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">{{ $projectData['projectStats']['in_progress_tasks'] }}</h3>
                    <p class="text-muted mb-0">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">{{ $projectData['projectStats']['overdue_tasks'] }}</h3>
                    <p class="text-muted mb-0">Overdue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Performance -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Team Members Performance</h5>
        </div>
        <div class="card-body">
            @if(count($projectData['teamPerformance']) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Team Member</th>
                                <th>Total Tasks</th>
                                <th>Completed</th>
                                <th>In Progress</th>
                                <th>Completion Rate</th>
                                <th>Avg. Completion Time</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projectData['teamPerformance'] as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-primary">
                                                    {{ substr($member['user']->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $member['user']->name }}</h6>
                                                <small class="text-muted">{{ $member['user']->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $member['total_tasks'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $member['completed_tasks'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $member['in_progress_tasks'] }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 80px; height: 8px;">
                                                <div class="progress-bar" style="width: {{ $member['completion_rate'] }}%"></div>
                                            </div>
                                            <span class="small">{{ $member['completion_rate'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($member['avg_completion_time'] > 0)
                                            {{ $member['avg_completion_time'] }} days
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member['last_activity'])
                                            {{ $member['last_activity']->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bx bx-user fs-1 text-muted"></i>
                    <h5 class="mt-3">No Team Members</h5>
                    <p class="text-muted">No team members have been assigned to tasks in this project.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Folder Structure and Tasks -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Project Structure & Tasks</h5>
        </div>
        <div class="card-body">
            @if(count($projectData['folderStructure']) > 0)
                @foreach($projectData['folderStructure'] as $folderData)
                    <div class="folder-section mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="bx bx-folder me-2"></i>{{ $folderData['folder']->name }}
                        </h6>

                        @if(count($folderData['tasks']) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Task Title</th>
                                            <th>Assignee</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Created</th>
                                            <th>Due Date</th>
                                            <th>Completed</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($folderData['tasks'] as $task)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $task['title'] }}</div>
                                                    <small class="text-muted">
                                                        <i class="bx bx-folder me-1"></i>
                                                        {{ implode(' → ', $task['folder_path']) }}
                                                    </small>
                                                </td>
                                                <td>{{ $task['assignee'] }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $task['status'] === 'completed' ? 'success' : ($task['status'] === 'in_progress' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'info') }}">
                                                        {{ ucfirst($task['priority']) }}
                                                    </span>
                                                </td>
                                                <td>{{ $task['created_at']->format('M d, Y') }}</td>
                                                <td>
                                                    @if($task['due_date'])
                                                        {{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}
                                                    @else
                                                        <span class="text-muted">No due date</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task['completed_at'])
                                                        {{ \Carbon\Carbon::parse($task['completed_at'])->format('M d, Y') }}
                                                    @else
                                                        <span class="text-muted">Not completed</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task['duration_days'])
                                                        {{ $task['duration_days'] }} days
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No tasks in this folder.</p>
                        @endif

                        <!-- Sub-folders -->
                        @if(count($folderData['sub_folders']) > 0)
                            <div class="sub-folders mt-3">
                                @foreach($folderData['sub_folders'] as $subFolderData)
                                    <div class="sub-folder-section ms-4 mb-3">
                                        <h6 class="text-secondary mb-2">
                                            <i class="bx bx-folder me-2"></i>{{ $subFolderData['folder']->name }}
                                        </h6>

                                        @if(count($subFolderData['tasks']) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Task Title</th>
                                                            <th>Assignee</th>
                                                            <th>Status</th>
                                                            <th>Priority</th>
                                                            <th>Created</th>
                                                            <th>Due Date</th>
                                                            <th>Completed</th>
                                                            <th>Duration</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($subFolderData['tasks'] as $task)
                                                            <tr>
                                                                <td>
                                                                    <div class="fw-semibold">{{ $task['title'] }}</div>
                                                                    <small class="text-muted">
                                                                        <i class="bx bx-folder me-1"></i>
                                                                        {{ implode(' → ', $task['folder_path']) }}
                                                                    </small>
                                                                </td>
                                                                <td>{{ $task['assignee'] }}</td>
                                                                <td>
                                                                    <span class="badge bg-{{ $task['status'] === 'completed' ? 'success' : ($task['status'] === 'in_progress' ? 'warning' : 'secondary') }}">
                                                                        {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-{{ $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'info') }}">
                                                                        {{ ucfirst($task['priority']) }}
                                                                    </span>
                                                                </td>
                                                                <td>{{ $task['created_at']->format('M d, Y') }}</td>
                                                                <td>
                                                                    @if($task['due_date'])
                                                                        {{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}
                                                                    @else
                                                                        <span class="text-muted">No due date</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($task['completed_at'])
                                                                        {{ \Carbon\Carbon::parse($task['completed_at'])->format('M d, Y') }}
                                                                    @else
                                                                        <span class="text-muted">Not completed</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($task['duration_days'])
                                                                        {{ $task['duration_days'] }} days
                                                                    @else
                                                                        <span class="text-muted">N/A</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted">No tasks in this sub-folder.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <i class="bx bx-folder-open fs-1 text-muted"></i>
                    <h5 class="mt-3">No Folders</h5>
                    <p class="text-muted">This project doesn't have any folders yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Project Summary -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Project Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Project Timeline</h6>
                    <ul class="list-unstyled">
                        <li><strong>Created:</strong> {{ $projectData['projectTimeline']['created_at']->format('M d, Y H:i') }}</li>
                        @if($projectData['projectTimeline']['start_date'])
                            <li><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($projectData['projectTimeline']['start_date'])->format('M d, Y') }}</li>
                        @endif
                        @if($projectData['projectTimeline']['due_date'])
                            <li><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($projectData['projectTimeline']['due_date'])->format('M d, Y') }}</li>
                        @endif
                        @if($projectData['projectTimeline']['end_date'])
                            <li><strong>End Date:</strong> {{ \Carbon\Carbon::parse($projectData['projectTimeline']['end_date'])->format('M d, Y') }}</li>
                        @endif
                        <li><strong>Last Updated:</strong> {{ $projectData['projectTimeline']['updated_at']->format('M d, Y H:i') }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Key Metrics</h6>
                    <ul class="list-unstyled">
                        <li><strong>Total Tasks:</strong> {{ $projectData['projectStats']['total_tasks'] }}</li>
                        <li><strong>Completed Tasks:</strong> {{ $projectData['projectStats']['completed_tasks'] }}</li>
                        <li><strong>Completion Rate:</strong> {{ $projectData['projectStats']['completion_rate'] }}%</li>
                        <li><strong>Overdue Tasks:</strong> {{ $projectData['projectStats']['overdue_tasks'] }}</li>
                        @if($projectData['managerPlannedDuration'])
                            <li><strong>Planned Duration:</strong> {{ $projectData['managerPlannedDuration'] }} days</li>
                        @endif
                        <li><strong>Team Members:</strong> {{ count($projectData['teamPerformance']) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->

<style>
.folder-section {
    border-left: 3px solid #696cff;
    padding-left: 15px;
    margin-bottom: 20px;
}

.sub-folder-section {
    border-left: 2px solid #e1e4e8;
    padding-left: 15px;
    margin-bottom: 15px;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75rem;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

.avatar {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
    color: white;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .table th,
    .table td {
        padding: 0.5rem;
    }
}
</style>
@endsection
