# Default Profile Image Setup

## Overview
The system now uses a centralized default profile image located at `public/uploads/users/default.png` that is shared across all users who haven't uploaded a custom image.

## Protected Default Images
The following images are protected from deletion:
- `default.png` (primary default image)
- `default.jpg` (alternative default)
- `1.png` (legacy default)
- `default_user.jpg` (legacy default)
- `default-user.jpg` (legacy default)

## How It Works

### 1. Image Display
All user images are now displayed using:
```php
asset('uploads/users/' . ($user->img ?: 'default.png'))
```

This means:
- If user has a custom image → shows their custom image
- If user has no image (null) → shows default.png
- If user has default.png → shows default.png

### 2. Image Upload
When a user uploads a new image:
- The old custom image is deleted (if it exists and is not a default)
- The new image is saved with a unique filename: `user_{id}_{timestamp}.{ext}`
- The user's `img` field is updated with the new filename
- **default.png is never deleted**

### 3. Image Removal
When a user removes their profile image:
- Their custom image is deleted (if it exists and is not a default)
- Their `img` field is set to `default.png`
- **default.png is never deleted**

### 4. Email Signatures
- Custom images appear in email signatures
- Default images (default.png, 1.png, etc.) do NOT appear in signatures
- This keeps signatures clean for users without custom photos

## Implementation

### Files Updated:
1. **ProfileController** (`app/Http/Controllers/ProfileController.php`)
   - `updateImage()` - Protected default.png from deletion
   - `removeImage()` - Sets to default.png instead of 1.png

2. **Admin UsersController** (`app/Http/Controllers/Admin/UsersController.php`)
   - `update()` - Protected all default images
   - `destroy()` - Protected all default images

3. **Views Updated:**
   - `resources/views/layouts/header.blade.php` - Navigation avatars
   - `resources/views/profile/partials/profile-image-form.blade.php` - Profile page
   - `resources/views/admin/users/index.blade.php` - User list
   - `resources/views/admin/users/_form.blade.php` - User form

4. **EmailSignatureService** (`app/Services/EmailSignatureService.php`)
   - `getUserImage()` - Excludes default.png from signatures

## Setup Instructions

### Initial Setup:
1. Upload your default profile image to: `public/uploads/users/default.png`
2. Ensure the file has proper permissions (644 or 755)
3. Image should be square (recommended: 400x400px or larger)
4. Use a professional generic avatar or company logo

### For Existing Users:
Run this SQL to set all users without images to use default.png:
```sql
UPDATE users SET img = 'default.png' WHERE img IS NULL OR img = '';
```

### For New Users:
The system will automatically use default.png for new users if no image is uploaded during creation.

## Benefits

1. **Centralized Management**: One default image for all users
2. **No Broken Images**: All users always have a valid image
3. **Easy Updates**: Update default.png to change all default avatars
4. **Storage Efficiency**: Only one default file instead of copies
5. **Clean Signatures**: Default images don't clutter email signatures
6. **Safe Deletion**: Default image is protected from accidental deletion

## File Location
```
public/uploads/users/default.png
```

This file must:
- Exist at all times
- Be readable by the web server
- Be a valid image file (PNG recommended)
- Be square aspect ratio (1:1) for best results
- Be at least 400x400px for quality

## Testing

1. **Test Default Display:**
   - Create a new user without uploading an image
   - Verify default.png is shown

2. **Test Custom Upload:**
   - Upload a custom image
   - Verify it displays correctly
   - Verify default.png still exists

3. **Test Image Removal:**
   - Remove a custom image
   - Verify it resets to default.png
   - Verify default.png still exists

4. **Test Navigation:**
   - Check navigation bar avatar
   - Check dropdown menu avatar
   - Verify all show correct images

5. **Test Email Signatures:**
   - Send an email with default image
   - Verify signature doesn't include image
   - Upload custom image and send again
   - Verify signature now includes custom image

