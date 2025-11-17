@extends('layouts.app')

@section('content')
<div class="container container-p-y">
  @php($currentUser = auth()->user())
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Users</h4>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a>
  </div>

  @if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('status') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
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
              @if($currentUser?->canDelete())
                <form id="delete-user-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')">
                    <i class="bx bx-trash"></i> Delete
                  </button>
                </form>
              @elseif($currentUser?->isSubAdmin())
                @include('partials.delete-request-button', [
                    'type' => 'user',
                    'id' => $user->id,
                    'label' => $user->name,
                    'text' => 'Request Delete'
                ])
              @endif
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

<script>
function confirmDelete(userId, userName) {
    if (confirm('Are you sure you want to delete user "' + userName + '"?\n\nThis action cannot be undone and will:\n- Delete all user data\n- Reassign their tasks and projects\n- Remove all associated records\n\nClick OK to confirm deletion.')) {
        // Get the form
        var form = document.getElementById('delete-user-form-' + userId);

        if (form) {
            console.log('Submitting delete form for user ' + userId);

            // Disable the button to prevent double-clicks
            var buttons = form.querySelectorAll('button');
            buttons.forEach(function(btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
            });

            // Submit the form
            form.submit();
        } else {
            console.error('Form not found for user ' + userId);
            alert('Error: Could not find delete form. Please refresh the page and try again.');
        }
    } else {
        console.log('User cancelled deletion of user ' + userId);
    }
}
</script>
@endsection
