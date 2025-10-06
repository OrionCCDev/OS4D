@extends('layouts.app')

@section('title', 'Sub-Admin Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <x-modern-breadcrumb
                title="Sub-Admin Dashboard"
                subtitle="Manage projects and tasks with limited access"
                icon="bx-shield"
                theme="admin"
                :breadcrumbs="[
                    ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx-home']
                ]"
            />
        </div>
    </div>

    <!-- Sub-Admin Notice -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bx bx-info-circle me-2"></i>
                <strong>Sub-Admin Access:</strong> You have administrative privileges for projects and tasks, but cannot delete items or access all system sections.
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bx bx-folder text-primary fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted">Total Projects</div>
                            <div class="h4 mb-0">{{ $data['totalProjects'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bx bx-task text-warning fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted">Total Tasks</div>
                            <div class="h4 mb-0">{{ $data['totalTasks'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bx bx-check-circle text-success fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted">Completed Tasks</div>
                            <div class="h4 mb-0">{{ $data['completedTasks'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bx bx-time text-danger fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted">Overdue Tasks</div>
                            <div class="h4 mb-0">{{ $data['overdueTasks'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('projects.index') }}" class="btn btn-primary w-100">
                                <i class="bx bx-folder me-2"></i>View Projects
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('tasks.index') }}" class="btn btn-warning w-100">
                                <i class="bx bx-task me-2"></i>View Tasks
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('emails.send-form') }}" class="btn btn-info w-100">
                                <i class="bx bx-envelope me-2"></i>Send Email
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('projects.create') }}" class="btn btn-success w-100">
                                <i class="bx bx-plus me-2"></i>Create Project
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Projects</h5>
                </div>
                <div class="card-body">
                    @if(isset($data['recentProjects']) && count($data['recentProjects']) > 0)
                        @foreach($data['recentProjects'] as $project)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <i class="bx bx-folder text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $project->name }}</h6>
                                <small class="text-muted">{{ $project->created_at->format('M d, Y') }}</small>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">No recent projects found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Tasks</h5>
                </div>
                <div class="card-body">
                    @if(isset($data['recentTasks']) && count($data['recentTasks']) > 0)
                        @foreach($data['recentTasks'] as $task)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge {{ $task->status_badge_class }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $task->title }}</h6>
                                <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">No recent tasks found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Access Restrictions Notice -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Access Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">✅ What You Can Do:</h6>
                            <ul class="list-unstyled">
                                <li><i class="bx bx-check text-success me-2"></i>View and manage projects</li>
                                <li><i class="bx bx-check text-success me-2"></i>View and manage tasks</li>
                                <li><i class="bx bx-check text-success me-2"></i>Send emails</li>
                                <li><i class="bx bx-check text-success me-2"></i>Create new projects and tasks</li>
                                <li><i class="bx bx-check text-success me-2"></i>Edit existing projects and tasks</li>
                                <li><i class="bx bx-check text-success me-2"></i>Change task statuses</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger">❌ What You Cannot Do:</h6>
                            <ul class="list-unstyled">
                                <li><i class="bx bx-x text-danger me-2"></i>Delete any items</li>
                                <li><i class="bx bx-x text-danger me-2"></i>Access email monitoring</li>
                                <li><i class="bx bx-x text-danger me-2"></i>Access system settings</li>
                                <li><i class="bx bx-x text-danger me-2"></i>View user management</li>
                                <li><i class="bx bx-x text-danger me-2"></i>Access advanced features</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
