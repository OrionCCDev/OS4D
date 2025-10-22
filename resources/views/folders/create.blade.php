@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <h4 class="mb-3">Create Folder</h4>
    <div class="card p-3">
        <form method="POST" action="{{ route('folders.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Project</label>
                <select name="project_id" id="project_id" class="form-select" required>
                    <option value="">Select project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id', $selectedProjectId ?? null)==$project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Parent Folder</label>
                <select name="parent_id" id="parent_id" class="form-select">
                    <option value="">None</option>
                    @foreach($folders as $f)
                        <option value="{{ $f->id }}" data-project="{{ $f->project_id }}" @selected(old('parent_id', $selectedParentId ?? null)==$f->id)>{{ $f->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Parent options filter by selected project.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('folders.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
</style>
@endpush

@push('scripts')
<script>
  (function(){
    const projectSelect = document.getElementById('project_id');
    const parentSelect = document.getElementById('parent_id');
    const filterParents = () => {
      const pid = projectSelect.value;
      Array.from(parentSelect.options).forEach(opt => {
        if (!opt.value) return; // keep None
        opt.hidden = pid && opt.dataset.project !== pid;
      });
      // If current selected parent doesn't match project, reset
      if (parentSelect.selectedOptions.length && parentSelect.selectedOptions[0].hidden) {
        parentSelect.value = '';
      }
    };
    if (projectSelect && parentSelect) {
      projectSelect.addEventListener('change', filterParents);
      filterParents();
    }
  })();
 </script>
@endpush


