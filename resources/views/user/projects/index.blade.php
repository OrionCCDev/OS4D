@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-12">
                        <div class="card-body">
                            <h5 class="card-title text-primary fw-bold">Projects</h5>
                            <p class="mb-4 text-muted">View all projects and access your assigned tasks and files</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="row">
        @forelse($projects as $project)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card h-100 project-card" style="transition: all 0.3s ease; cursor: pointer;" onclick="window.location.href='{{ route('user.projects.show', $project->id) }}'">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>

                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="folder-icon me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="bx bx-folder text-white" style="font-size: 24px;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-semibold">{{ Str::limit($project->name, 30) }}</h6>
                                @if($project->short_code)
                                    <small class="text-muted">{{ $project->short_code }}</small>
                                @endif
                            </div>
                        </div>

                        @if($project->description)
                            <p class="text-muted small mb-3">{{ Str::limit($project->description, 100) }}</p>
                        @endif

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-center p-2 rounded" style="background: #f8f9fa;">
                                    <div class="fw-bold text-primary">{{ $project->tasks()->where('assigned_to', auth()->id())->count() }}</div>
                                    <small class="text-muted">My Tasks</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 rounded" style="background: #fff3cd;">
                                    <div class="fw-bold text-warning">{{ $project->folders()->count() }}</div>
                                    <small class="text-muted">Folders</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : ($project->status === 'on_hold' ? 'warning' : 'secondary')) }}">
                                {{ ucfirst(str_replace('_', ' ', $project->status ?? 'draft')) }}
                            </span>
                            <a href="{{ route('user.projects.show', $project->id) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation();">
                                <i class="bx bx-right-arrow-alt me-1"></i>View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-folder-plus" style="font-size: 4rem; color: #d1d5db;"></i>
                        <h5 class="text-muted mb-2">No projects available</h5>
                        <p class="text-muted mb-0">Contact your administrator to get assigned to projects</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
.project-card {
    border: 1px solid #e5e7eb;
}

.project-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    border-color: #d1d5db !important;
}

.project-card .folder-icon {
    transition: transform 0.3s ease;
}

.project-card:hover .folder-icon {
    transform: scale(1.1);
}
</style>
@endsection

