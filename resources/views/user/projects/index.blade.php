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
                <div class="card h-100 project-card" style="transition: all 0.3s ease; cursor: pointer; border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08);" onclick="window.location.href='{{ route('user.projects.show', $project->id) }}'">
                    <!-- Gradient background -->
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); opacity: 0.95;"></div>

                    <div class="card-body p-4 position-relative" style="z-index: 1;">
                        <div class="d-flex align-items-center mb-3">
                            <div class="folder-icon me-3" style="width: 52px; height: 52px; background: rgba(255,255,255,0.25); backdrop-filter: blur(10px); border-radius: 16px; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.3);">
                                <i class="bx bx-folder text-white" style="font-size: 28px;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold text-white">{{ Str::limit($project->name, 30) }}</h6>
                                @if($project->short_code)
                                    <small class="text-white-50">{{ $project->short_code }}</small>
                                @endif
                            </div>
                        </div>

                        @if($project->description)
                            <p class="text-white-50 small mb-3" style="opacity: 0.9;">{{ Str::limit($project->description, 80) }}</p>
                        @endif

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-center p-2 rounded" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">
                                    <div class="fw-bold text-white">{{ $project->tasks()->where('assigned_to', auth()->id())->count() }}</div>
                                    <small class="text-white-50">My Tasks</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 rounded" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">
                                    <div class="fw-bold text-white">{{ $project->folders()->count() }}</div>
                                    <small class="text-white-50">Folders</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge" style="background: rgba(255,255,255,0.25); color: white; border: 1px solid rgba(255,255,255,0.3);">
                                {{ ucfirst(str_replace('_', ' ', $project->status ?? 'draft')) }}
                            </span>
                            <a href="{{ route('user.projects.show', $project->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.25); color: white; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px);" onclick="event.stopPropagation();">
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
    position: relative;
}

.project-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3) !important;
}

.project-card .folder-icon {
    transition: transform 0.3s ease;
}

.project-card:hover .folder-icon {
    transform: scale(1.15) rotate(5deg);
}

/* Shine effect on hover */
.project-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
    z-index: 2;
    pointer-events: none;
}

.project-card:hover::before {
    left: 100%;
}

.project-card .badge {
    transition: all 0.3s ease;
}

.project-card:hover .badge {
    background: rgba(255,255,255,0.35) !important;
}

.project-card .btn {
    transition: all 0.3s ease;
}

.project-card:hover .btn {
    background: rgba(255,255,255,0.35) !important;
    transform: translateX(5px);
}
</style>
@endsection

