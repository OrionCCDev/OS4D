@php
    $isActive = isset($selectedFolder) && $selectedFolder && $selectedFolder->id === $folder->id;
    $hasChildren = $folder->children && $folder->children->count();
    $isExpanded = isset($expandedFolderIds) && in_array($folder->id, (array) $expandedFolderIds);
@endphp
<li class="mb-1" data-tree-item>
    <div class="d-flex align-items-center gap-2">
        @if($hasChildren)
            <button type="button" class="btn btn-sm p-0 px-1 border-0 bg-transparent text-secondary" data-tree-toggle aria-expanded="{{ $isExpanded ? 'true' : 'false' }}">
                <span class="toggle-icon">{{ $isExpanded ? '▾' : '▸' }}</span>
            </button>
        @else
            <span class="text-secondary" style="width: 14px; display:inline-block;"></span>
        @endif
        <a href="{{ route('projects.show', ['project' => $project->id, 'folder' => $folder->id]) }}" class="{{ $isActive ? 'fw-bold text-primary' : '' }}">
            {{ $folder->name }}
        </a>
        <a class="badge bg-label-secondary text-decoration-none" href="{{ route('folders.create', ['project_id' => $project->id, 'parent_id' => $folder->id]) }}">+ SUB</a>
    </div>
    @if($hasChildren)
        <ul class="list-unstyled ms-3 mt-1" data-tree-children style="display: {{ $isExpanded ? 'block' : 'none' }};">
            @foreach($folder->children as $child)
                @include('projects.tree', ['folder' => $child, 'project' => $project, 'selectedFolder' => $selectedFolder, 'expandedFolderIds' => $expandedFolderIds])
            @endforeach
        </ul>
    @endif
</li>


