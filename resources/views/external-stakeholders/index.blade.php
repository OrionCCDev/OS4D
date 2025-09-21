@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">External Stakeholders</h4>
        <a href="{{ route('external-stakeholders.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>Add Stakeholder
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stakeholders as $stakeholder)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                {{ substr($stakeholder->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $stakeholder->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $stakeholder->email }}</td>
                                <td>{{ $stakeholder->company ?? 'N/A' }}</td>
                                <td>{{ $stakeholder->role ?? 'N/A' }}</td>
                                <td>{{ $stakeholder->phone ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $stakeholder->is_active ? 'success' : 'secondary' }}">
                                        {{ $stakeholder->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('external-stakeholders.edit', $stakeholder) }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                            <form action="{{ route('external-stakeholders.destroy', $stakeholder) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this stakeholder?')">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No external stakeholders found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        {{ $stakeholders->links() }}
    </div>
</div>
@endsection
