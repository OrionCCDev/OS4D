@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Folders</h4>
        <a href="{{ route('folders.create') }}" class="btn btn-primary">New Folder</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead><tr>
                    <th>Name</th>
                    <th>Project</th>
                    <th>Parent</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @foreach($folders as $folder)
                    <tr>
                        <td>{{ $folder->name }}</td>
                        <td>{{ $folder->project?->name }}</td>
                        <td>{{ $folder->parent?->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('folders.edit', $folder) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('folders.destroy', $folder) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this folder?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $folders->links() }}</div>
    </div>
</div>
@endsection


