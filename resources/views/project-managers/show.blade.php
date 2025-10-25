@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">{{ $project_manager->name }}</h4>
            <p class="text-muted mb-0">Project Manager Details</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('project-managers.edit', $project_manager) }}" class="btn btn-outline-secondary">
                <i class="bx bx-edit me-1"></i>Edit
            </a>
            <a href="{{ route('project-managers.index') }}" class="btn btn-outline-primary">
                <i class="bx bx-arrow-back me-1"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted d-block">Name</label>
                        <strong>{{ $project_manager->name }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted d-block">Orion ID</label>
                        <span class="badge bg-label-info fs-6">{{ $project_manager->orion_id }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted d-block">Email</label>
                        <a href="mailto:{{ $project_manager->email }}">{{ $project_manager->email }}</a>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted d-block">Mobile</label>
                        <strong>{{ $project_manager->mobile ?? 'Not provided' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="d-inline-block p-4 bg-primary bg-opacity-10 rounded">
                            <i class="bx bx-folder text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h2 class="mt-3 mb-1">{{ $project_manager->projects->count() }}</h2>
                        <p class="text-muted mb-0">Projects Managed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($project_manager->projects->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Assigned Projects</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project_manager->projects as $project)
                                <tr>
                                    <td>
                                        <strong>{{ $project->name }}</strong>
                                        @if($project->short_code)
                                            <br><small class="text-muted">{{ $project->short_code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $project->start_date ? $project->start_date->format('M d, Y') : '-' }}</td>
                                    <td>{{ $project->end_date ? $project->end_date->format('M d, Y') : '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-show"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bx bx-folder-open text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No Projects Assigned</h5>
                <p class="text-muted">This project manager doesn't have any projects assigned yet.</p>
            </div>
        </div>
    @endif
</div>
@endsection
