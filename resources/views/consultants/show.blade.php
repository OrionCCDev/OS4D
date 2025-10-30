@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Consultant Details</h4>
        <div class="btn-group">
            <a href="{{ route('consultants.edit', $consultant) }}" class="btn btn-outline-primary">
                <i class="bx bx-edit me-1"></i>Edit
            </a>
            <a href="{{ route('consultants.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Name</label>
                        <p class="mb-0"><strong>{{ $consultant->name }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Email</label>
                        <p class="mb-0"><a href="mailto:{{ $consultant->email }}">{{ $consultant->email }}</a></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Mobile</label>
                        <p class="mb-0">{{ $consultant->mobile ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Position</label>
                        <p class="mb-0">{{ $consultant->position ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Status</label>
                        <p class="mb-0">
                            @if($consultant->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Role</label>
                        <p class="mb-0"><span class="badge bg-info">{{ ucfirst($consultant->role) }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Created At</label>
                        <p class="mb-0">{{ $consultant->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Last Updated</label>
                        <p class="mb-0">{{ $consultant->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
