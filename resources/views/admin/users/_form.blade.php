@php($editing = isset($user))

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="row g-3">
  <div class="col-md-6">
    <label for="name" class="form-label">Name</label>
    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
  </div>
  <div class="col-md-6">
    <label for="email" class="form-label">Email</label>
    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
  </div>
  <div class="col-md-6">
    <label for="password" class="form-label">{{ $editing ? 'New Password' : 'Password' }}</label>
    <input type="password" id="password" name="password" class="form-control" {{ $editing ? '' : 'required' }}>
  </div>
  <div class="col-md-6">
    <label for="mobile" class="form-label">Mobile</label>
    <input type="text" id="mobile" name="mobile" class="form-control" value="{{ old('mobile', $user->mobile ?? '') }}">
  </div>
  <div class="col-md-6">
    <label for="position" class="form-label">Position</label>
    <input type="text" id="position" name="position" class="form-control" value="{{ old('position', $user->position ?? '') }}">
  </div>
  <div class="col-md-6">
    <label for="role" class="form-label">Role</label>
    <select id="role" name="role" class="form-select" required>
      <option value="user" {{ old('role', $user->role ?? 'user') === 'user' ? 'selected' : '' }}>User</option>
      <option value="admin" {{ old('role', $user->role ?? 'user') === 'admin' ? 'selected' : '' }}>Admin</option>
    </select>
  </div>
  <div class="col-md-6">
    <label for="img" class="form-label">Avatar</label>
    <input type="file" id="img" name="img" class="form-control">
    @if($editing)
      <div class="mt-2">
        <img src="{{ asset('uploads/users/' . ($user->img ?: 'default.png')) }}" alt="{{ $user->name }}" class="rounded" width="64" height="64" style="object-fit: cover;">
      </div>
    @endif
  </div>
</div>


