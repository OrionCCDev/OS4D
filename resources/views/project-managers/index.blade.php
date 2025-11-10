@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Project Managers</h4>
            <p class="text-muted mb-0">Manage project managers and assign them to projects</p>
        </div>
        <a href="{{ route('project-managers.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>New Project Manager
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Orion ID</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Projects</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectManagers as $manager)
                        <tr>
                            <td>
                                <strong>{{ $manager->name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-label-info">{{ $manager->orion_id }}</span>
                            </td>
                            <td>
                                <a href="mailto:{{ $manager->email }}">{{ $manager->email }}</a>
                            </td>
                            <td>{{ $manager->mobile ?? '-' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $manager->projects_count ?? 0 }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('project-managers.show', $manager) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ route('project-managers.edit', $manager) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    @if(auth()->user()->canDelete())
                                        <form action="{{ route('project-managers.destroy', $manager) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this project manager?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-danger disabled" aria-disabled="true" title="You do not have permission to delete project managers.">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bx bx-user fs-1 d-block mb-2"></i>
                                No project managers found. <a href="{{ route('project-managers.create') }}">Create your first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $projectManagers->links() }}</div>
    </div>
</div>
@endsection
