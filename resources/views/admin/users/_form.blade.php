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
      <option value="sup-admin" {{ old('role', $user->role ?? 'user') === 'sup-admin' ? 'selected' : '' }}>Sup Admin</option>
      <option value="manager" {{ old('role', $user->role ?? 'user') === 'manager' ? 'selected' : '' }}>Manager</option>
      <option value="sub-admin" {{ old('role', $user->role ?? 'user') === 'sub-admin' ? 'selected' : '' }}>Sub Admin</option>
    </select>
  </div>
  <div class="col-md-6">
    <label for="img" class="form-label">
      <i class="bx bx-image me-1"></i>Avatar Image
    </label>
    <input type="file" 
           id="img" 
           name="img" 
           class="form-control @error('img') is-invalid @enderror" 
           accept="image/jpeg,image/png,image/jpg,image/webp"
           onchange="previewUserImage(this)">
    @error('img')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted d-block mt-1">
      <i class="bx bx-info-circle me-1"></i>JPG, PNG, or WEBP. Max 2MB. Recommended: 400x400px
    </small>
    @if($editing)
      <div class="mt-3">
        <label class="form-label small text-muted">Current Image:</label>
        <div class="d-flex align-items-center gap-2">
          <img id="current-avatar" 
               src="{{ asset('uploads/users/' . ($user->img ?: 'default.png')) }}" 
               alt="{{ $user->name }}" 
               class="rounded-circle border" 
               width="80" 
               height="80" 
               style="object-fit: cover;">
          <div id="image-preview-container" style="display: none;">
            <label class="form-label small text-success">New Image Preview:</label>
            <img id="image-preview" 
                 src="" 
                 alt="Preview" 
                 class="rounded-circle border border-success" 
                 width="80" 
                 height="80" 
                 style="object-fit: cover;">
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

<script>
function previewUserImage(input) {
    const previewContainer = document.getElementById('image-preview-container');
    const preview = document.getElementById('image-preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
        
        // Show file info
        const file = input.files[0];
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
        console.log('Image selected:', file.name, '(' + fileSize + ' MB)');
        
        // Validate file size
        if (file.size > 2048 * 1024) {
            alert('File size exceeds 2MB. Please choose a smaller image.');
            input.value = '';
            previewContainer.style.display = 'none';
        }
    } else {
        previewContainer.style.display = 'none';
    }
}
</script>

