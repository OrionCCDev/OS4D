@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <h4 class="mb-3">Create Project</h4>
    <div class="card p-3">
        <form method="POST" action="{{ route('projects.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Short Code</label>
                <input type="text" name="short_code" class="form-control" value="{{ old('short_code') }}" maxlength="12" placeholder="e.g., ODS-PRJ">
                <small class="text-muted">Short identifier shown on cards and in lists.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.btn-outline-secondary {
    color: #6c757d !important;
    border-color: #6c757d !important;
    background-color: transparent !important;
}

.btn-outline-secondary:hover {
    color: #fff !important;
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

.btn-outline-secondary:focus {
    color: #6c757d !important;
    border-color: #6c757d !important;
    background-color: transparent !important;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25) !important;
}
</style>
@endpush


