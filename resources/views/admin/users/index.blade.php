@extends('layouts.app')

@section('content')
<div class="container container-p-y">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Users</h4>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a>
  </div>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Avatar</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Position</th>
            <th>Role</th>
            <th>Created</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
          <tr>
            <td>{{ $user->id }}</td>
            <td>
              <img src="{{ asset('uploads/users/' . ($user->img ?: 'default.png')) }}" alt="{{ $user->name }}" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
            </td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->mobile }}</td>
            <td>{{ $user->position }}</td>
            <td>
              <span class="badge bg-label-{{ $user->role === 'admin' ? 'primary' : 'secondary' }}">{{ ucfirst($user->role) }}</span>
              @if(isset($user->status))
                @if($user->status === 'inactive')
                  <span class="badge bg-warning ms-1">Inactive</span>
                @elseif($user->status === 'resigned')
                  <span class="badge bg-danger ms-1">Resigned</span>
                @endif
              @endif
            </td>
            <td>{{ $user->created_at->format('Y-m-d') }}</td>
            <td class="text-end">
              <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-edit"></i> Edit
              </a>
              <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $users->links() }}
    </div>
  </div>
</div>
@endsection


