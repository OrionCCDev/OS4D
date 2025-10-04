@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Email Templates</h4>
        <a href="{{ route('email-templates.create') }}" class="btn btn-primary">New Template</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead><tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Active</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @foreach($templates as $template)
                    <tr>
                        <td>{{ $template->name }}</td>
                        <td>{{ $template->type }}</td>
                        <td>{{ $template->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-end">
                            <a href="{{ route('email-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('email-templates.destroy', $template) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this template?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $templates->links() }}</div>
    </div>
</div>
@endsection


