@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Consultants</h4>
            <p class="text-muted mb-0">Manage consultant users and their access</p>
        </div>
        <a href="{{ route('consultants.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>New Consultant
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
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultants as $consultant)
                        <tr>
                            <td>
                                <strong>{{ $consultant->name }}</strong>
                            </td>
                            <td>
                                <a href="mailto:{{ $consultant->email }}">{{ $consultant->email }}</a>
                            </td>
                            <td>{{ $consultant->mobile ?? '-' }}</td>
                            <td>{{ $consultant->position ?? '-' }}</td>
                            <td>
                                @if($consultant->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('consultants.show', $consultant) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ route('consultants.edit', $consultant) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <form action="{{ route('consultants.destroy', $consultant) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this consultant?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bx bx-user fs-1 d-block mb-2"></i>
                                No consultants found. <a href="{{ route('consultants.create') }}">Create your first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $consultants->links() }}</div>
    </div>
</div>
@endsection
