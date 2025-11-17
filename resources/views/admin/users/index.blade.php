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
            <th>Avatar</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Position</th>
            <th>Role</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
          <tr>
            <td>
              <img src="{{ asset('uploads/users/' . ($user->img ?: 'default.png')) }}" alt="{{ $user->name }}" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
            </td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->mobile }}</td>
            <td>{{ $user->position }}</td>
            <td>
              <span class="badge bg-label-{{ $user->role === 'admin' ? 'primary' : 'secondary' }}">{{ ucfirst($user->role) }}</span>
              @if($user->status === 'inactive')
                <span class="badge bg-warning ms-1">Inactive</span>
              @elseif($user->status === 'resigned')
                <span class="badge bg-danger ms-1">Resigned</span>
              @elseif($user->status === 'active')
                <span class="badge bg-success ms-1">Active</span>
              @endif
            </td>
            <td>
              <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bx bx-edit"></i> Edit
                </a>
                @if($currentUser?->canDelete() && $user->id !== $currentUser->id)
                  @if($user->status === 'active')
                    <form id="deactivate-user-form-{{ $user->id }}" action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="button" class="btn btn-sm btn-outline-warning" onclick="confirmDeactivate({{ $user->id }}, '{{ addslashes($user->name) }}')">
                        <i class="bx bx-user-x"></i> Deactivate
                      </button>
                    </form>
                  @else
                    <form id="reactivate-user-form-{{ $user->id }}" action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="button" class="btn btn-sm btn-outline-success" onclick="confirmReactivate({{ $user->id }}, '{{ addslashes($user->name) }}')">
                        <i class="bx bx-user-check"></i> Reactivate
                      </button>
                    </form>
                  @endif
                  <form id="force-delete-user-form-{{ $user->id }}" action="{{ route('admin.users.force-delete', $user) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmForceDelete({{ $user->id }}, '{{ addslashes($user->name) }}')">
                      <i class="bx bx-trash"></i> Force Delete
                    </button>
                  </form>
                @elseif($currentUser?->isSubAdmin())
                  @include('partials.delete-request-button', [
                      'type' => 'user',
                      'id' => $user->id,
                      'label' => $user->name,
                      'text' => 'Request Deactivate'
                  ])
                @endif
              </div>
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
function confirmDeactivate(userId, userName) {
    if (confirm('Are you sure you want to deactivate user "' + userName + '"?\n\nThis will:\n- Prevent the user from logging in\n- Preserve all their historical data\n- Keep all tasks and projects intact\n- Allow reactivation later if needed\n\nClick OK to confirm deactivation.')) {
        // Get the form
        var form = document.getElementById('deactivate-user-form-' + userId);

        if (form) {
            console.log('Submitting deactivate form for user ' + userId);

            // Disable the button to prevent double-clicks
            var buttons = form.querySelectorAll('button');
            buttons.forEach(function(btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deactivating...';
            });

            // Submit the form
            form.submit();
        } else {
            console.error('Form not found for user ' + userId);
            alert('Error: Could not find deactivate form. Please refresh the page and try again.');
        }
    } else {
        console.log('User cancelled deactivation of user ' + userId);
    }
}

function confirmReactivate(userId, userName) {
    if (confirm('Are you sure you want to reactivate user "' + userName + '"?\n\nThis will:\n- Allow the user to log in again\n- Restore full access to their account\n- Maintain all their historical data\n\nClick OK to confirm reactivation.')) {
        // Get the form
        var form = document.getElementById('reactivate-user-form-' + userId);

        if (form) {
            console.log('Submitting reactivate form for user ' + userId);

            // Disable the button to prevent double-clicks
            var buttons = form.querySelectorAll('button');
            buttons.forEach(function(btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Reactivating...';
            });

            // Submit the form
            form.submit();
        } else {
            console.error('Form not found for user ' + userId);
            alert('Error: Could not find reactivate form. Please refresh the page and try again.');
        }
    } else {
        console.log('User cancelled reactivation of user ' + userId);
    }
}

function confirmForceDelete(userId, userName) {
    if (confirm('⚠️ WARNING: Force Delete User\n\nAre you absolutely sure you want to PERMANENTLY DELETE user "' + userName + '"?\n\nThis action:\n- CANNOT be undone\n- Will permanently delete the user and ALL related data\n- Bypasses all safety checks\n- May leave orphaned records in some tables\n\nType "DELETE" in the next prompt to confirm.')) {
        var confirmation = prompt('Type "DELETE" (all caps) to confirm force deletion:');

        if (confirmation === 'DELETE') {
            // Get the form
            var form = document.getElementById('force-delete-user-form-' + userId);

            if (form) {
                console.log('Submitting force delete form for user ' + userId);

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
                alert('Error: Could not find force delete form. Please refresh the page and try again.');
            }
        } else {
            console.log('User cancelled force deletion of user ' + userId + ' (confirmation text did not match)');
            alert('Force deletion cancelled. Confirmation text did not match.');
        }
    } else {
        console.log('User cancelled force deletion of user ' + userId);
    }
}
</script>
@endsection
