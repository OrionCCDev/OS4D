@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-3">Edit Folder</h4>
    <div class="card p-3">
        <form method="POST" action="{{ route('folders.update', $folder) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Project</label>
                <select name="project_id" class="form-select" required>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id', $folder->project_id)==$project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Parent Folder</label>
                <select name="parent_id" class="form-select">
                    <option value="">None</option>
                    @foreach($folders as $f)
                        <option value="{{ $f->id }}" @selected(old('parent_id', $folder->parent_id)==$f->id)>{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $folder->name) }}" required>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('folders.index') }}" class="btn btn-secondary">Cancel</a>
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


