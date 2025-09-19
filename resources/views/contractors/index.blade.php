@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Contractors</h4>
        <a href="{{ route('contractors.create') }}" class="btn btn-primary">New Contractor</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead><tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Phone</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @foreach($contractors as $contractor)
                    <tr>
                        <td>{{ $contractor->name }}</td>
                        <td>{{ $contractor->email }}</td>
                        <td>{{ $contractor->company }}</td>
                        <td>{{ $contractor->phone }}</td>
                        <td class="text-end">
                            <a href="{{ route('contractors.edit', $contractor) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('contractors.destroy', $contractor) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this contractor?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $contractors->links() }}</div>
    </div>
</div>
@endsection


