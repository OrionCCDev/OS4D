@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-3">Create Task</h4>
    <div class="card p-3">
        <form method="POST" action="{{ route('tasks.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select" required id="project_id">
                        <option value="">Select project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected((old('project_id') ?? ($selectedProjectId ?? null))==$project->id)>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Folder</label>
                    <select name="folder_id" class="form-select" id="folder_id">
                        <option value="">None</option>
                        @foreach($folders as $f)
                            <option value="{{ $f->id }}" data-project="{{ $f->project_id }}" @selected((old('folder_id') ?? ($selectedFolderId ?? null))==$f->id)>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Assign To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Not assigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('assigned_to')==$user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Due Date</label>
                    <div class="d-flex gap-2">
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', $defaultDueDate ?? '') }}">
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-days="2">+ 2 Days</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-days="7">+ 1 Week</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-days="14">+ 2 Weeks</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-months="1">+ 1 Month</button>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        @php($priorities = ['low'=>'Low','normal'=>'Normal','medium'=>'Medium','high'=>'High','urgent'=>'Urgent','critical'=>'Critical'])
                        @foreach($priorities as $value=>$label)
                            <option value="{{ $value }}" @selected(old('priority','normal')==$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Attachments</label>
                <input type="file" name="attachments[]" class="form-control" multiple>
                <small class="text-muted">You can select multiple files. Max size 20MB per file.</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                    <i class="bx bx-x me-1"></i>Cancel
                </a>
                <button class="btn btn-primary">
                    <i class="bx bx-check me-1"></i>Save
                </button>
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

<script>
document.addEventListener('DOMContentLoaded', function(){
    const input = document.getElementById('due_date');
    function formatDate(d){
        const y = d.getFullYear();
        const m = String(d.getMonth()+1).padStart(2,'0');
        const day = String(d.getDate()).padStart(2,'0');
        return `${y}-${m}-${day}`;
    }
    document.querySelectorAll('[data-add-days]').forEach(btn => {
        btn.addEventListener('click', function(){
            const days = parseInt(this.getAttribute('data-add-days'), 10);
            const base = input.value ? new Date(input.value) : new Date();
            base.setDate(base.getDate() + days);
            input.value = formatDate(base);
        });
    });
    document.querySelectorAll('[data-add-months]').forEach(btn => {
        btn.addEventListener('click', function(){
            const months = parseInt(this.getAttribute('data-add-months'), 10);
            const base = input.value ? new Date(input.value) : new Date();
            const d = new Date(base);
            d.setMonth(d.getMonth() + months);
            input.value = formatDate(d);
        });
    });
});
</script>

<script>
// Filter folders by selected project on the client side as a convenience
document.addEventListener('DOMContentLoaded', function(){
  const projectSel = document.getElementById('project_id');
  const folderSel = document.getElementById('folder_id');
  if(!projectSel || !folderSel) return;
  function filterFolders(){
    const pid = projectSel.value;
    let firstVisible = null;
    Array.from(folderSel.options).forEach(opt => {
      if (!opt.value) return; // keep None
      const show = !pid || opt.getAttribute('data-project') === pid;
      opt.hidden = !show;
      if (show && !firstVisible) firstVisible = opt;
    });
    // If currently selected is hidden, switch to first visible
    const sel = folderSel.options[folderSel.selectedIndex];
    if (sel && sel.hidden) {
      folderSel.value = firstVisible ? firstVisible.value : '';
    }
  }
  projectSel.addEventListener('change', filterFolders);
  filterFolders();
});
</script>
