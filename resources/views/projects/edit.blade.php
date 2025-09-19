@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-3">Edit Project</h4>
    <div class="card p-3">
        <form method="POST" action="{{ route('projects.update', $project) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $project->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Short Code</label>
                <input type="text" name="short_code" class="form-control" value="{{ old('short_code', $project->short_code) }}" maxlength="12" placeholder="e.g., ODS-PRJ">
                <small class="text-muted">Short identifier shown on cards and in lists.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $project->description) }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['draft','active','on_hold','completed','cancelled'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $project->status)===$status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6 mb-3"></div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary">Update</button>
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


