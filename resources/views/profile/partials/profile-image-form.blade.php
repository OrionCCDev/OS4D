@php
    $defaultAvatar = 'DAssets/img/avatars/1.png';
    $currentImage = $user->img ? asset('uploads/users/' . $user->img) : asset($defaultAvatar);
    $hasCustomImage = $user->img && !in_array($user->img, ['default.png', 'default.jpg', '1.png']);
@endphp

<div class="text-center mb-4">
    <div class="position-relative d-inline-block">
        <img id="profile-image-preview"
             src="{{ $currentImage }}"
             alt="Profile Image"
             class="rounded-circle border"
             style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e0e0e0 !important;">

        @if($hasCustomImage)
            <button type="button"
                    class="btn btn-sm btn-danger position-absolute"
                    style="top: -5px; right: -5px; border-radius: 50%; width: 30px; height: 30px; padding: 0;"
                    onclick="removeProfileImage()"
                    title="Remove current image">
                <i class="bx bx-x" style="font-size: 16px;"></i>
            </button>
        @endif
    </div>

    <div class="mt-3">
        <h6 class="mb-1">{{ $user->name }}</h6>
        <small class="text-muted">{{ ucfirst($user->role) }}</small>
    </div>
</div>

<form id="profile-image-form" method="post" action="{{ route('profile.image.update') }}" enctype="multipart/form-data">
    @csrf
    @method('patch')

    <div class="mb-3">
        <label for="profile_image" class="form-label">{{ __('Upload New Image') }}</label>
        <input type="file"
               class="form-control @error('profile_image') is-invalid @enderror"
               id="profile_image"
               name="profile_image"
               accept="image/*"
               onchange="previewImage(this)">
        @error('profile_image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            {{ __('Upload a JPG, PNG, or WEBP image. Maximum size: 2MB. Recommended: 400x400px or larger.') }}
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" id="upload-btn" disabled>
            <i class="bx bx-upload me-1"></i>{{ __('Upload Image') }}
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="resetImageForm()">
            <i class="bx bx-refresh me-1"></i>{{ __('Reset') }}
        </button>
    </div>
</form>

<form id="remove-image-form" method="post" action="{{ route('profile.image.remove') }}" style="display: none;">
    @csrf
    @method('delete')
</form>

<div class="mt-4">
    <div class="alert alert-info">
        <div class="d-flex align-items-start">
            <i class="bx bx-info-circle me-2 mt-1"></i>
            <div>
                <h6 class="alert-heading mb-1">{{ __('Profile Image Tips') }}</h6>
                <ul class="mb-0 small">
                    <li>Your profile image will appear in email signatures</li>
                    <li>Use a professional headshot for best results</li>
                    <li>Square images work best (1:1 aspect ratio)</li>
                    <li>Make sure your face is clearly visible</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
#profile-image-preview {
    transition: all 0.3s ease;
}

#profile-image-preview:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: #696cff;
    background-color: #f8f9ff;
}

.upload-area.dragover {
    border-color: #696cff;
    background-color: #f0f0ff;
}
</style>

<script>
function previewImage(input) {
    const file = input.files[0];
    const preview = document.getElementById('profile-image-preview');
    const uploadBtn = document.getElementById('upload-btn');

    if (file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file.');
            input.value = '';
            return;
        }

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            uploadBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    } else {
        uploadBtn.disabled = true;
    }
}

function resetImageForm() {
    document.getElementById('profile_image').value = '';
    document.getElementById('upload-btn').disabled = true;

    // Reset to current image
    @if($hasCustomImage)
        document.getElementById('profile-image-preview').src = '{{ $currentImage }}';
    @else
        document.getElementById('profile-image-preview').src = '{{ asset($defaultAvatar) }}';
    @endif
}

function removeProfileImage() {
    if (confirm('Are you sure you want to remove your profile image? This will reset it to the default avatar.')) {
        document.getElementById('remove-image-form').submit();
    }
}

// Drag and drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.querySelector('.form-control[type="file"]');
    const fileInput = document.getElementById('profile_image');

    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            previewImage(fileInput);
        }
    });

    // Form submission with loading state
    document.getElementById('profile-image-form').addEventListener('submit', function() {
        const uploadBtn = document.getElementById('upload-btn');
        uploadBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Uploading...';
        uploadBtn.disabled = true;
    });
});
</script>
