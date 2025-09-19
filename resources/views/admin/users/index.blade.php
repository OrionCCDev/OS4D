@extends('layouts.app')

@section('content')
<div class="container-xxl container-p-y">
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
              <img src="{{ asset('uploads/users/' . $user->img) }}" alt="" class="rounded-circle" width="36" height="36">
            </td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><span class="badge bg-label-{{ $user->role === 'admin' ? 'primary' : 'secondary' }}">{{ ucfirst($user->role) }}</span></td>
            <td>{{ $user->created_at->format('Y-m-d') }}</td>
            <td class="text-end">
              <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
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


