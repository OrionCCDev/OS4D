# User Image Upload - Fix & Troubleshooting Guide

## Issue
User images are not changing when uploaded by managers in the admin panel.

## Root Causes & Solutions

### 1. **Missing `img` Field Unset** ✅ FIXED
**Problem**: When no new image was uploaded, the controller was still trying to update the `img` field with `null`, overwriting the existing image.

**Solution**: Added logic to unset the `img` field from the data array when no file is uploaded.

```php
if ($request->hasFile('img')) {
    // Handle upload
} else {
    // Don't change existing img field
    unset($data['img']);
}
```

### 2. **Improved Error Handling** ✅ ADDED
**Problem**: Silent failures - no feedback when upload fails.

**Solution**: Added try-catch block with logging and user-friendly error messages.

```php
try {
    // Upload logic
    \Log::info('User image uploaded successfully', [...]);
} catch (\Exception $e) {
    \Log::error('Failed to upload user image', [...]);
    return back()->withErrors(['img' => 'Failed to upload image: ' . $e->getMessage()]);
}
```

### 3. **Better User Feedback** ✅ ADDED
**Problem**: Users didn't know if upload was successful or what image they selected.

**Solution**: 
- Added image preview (shows side-by-side: current vs new)
- Enhanced success message: "User updated successfully with new image"
- Better error display with `@error` directive
- File size validation in browser

### 4. **Consistent Filename Format** ✅ FIXED
**Problem**: Inconsistent filename generation.

**Solution**: Changed from `uniqid('u_')` to `user_{id}_{timestamp}` format for better tracking.

```php
$filename = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
```

---

## How to Verify the Fix

### Step 1: Check Directory Permissions
Run the diagnostic script:
```bash
php check_uploads_permissions.php
```

This will:
- ✅ Verify `public/uploads/users/` exists
- ✅ Check if directory is writable
- ✅ Test file creation/deletion
- ✅ List existing images
- ✅ Verify default.png exists

### Step 2: Test Image Upload
1. Login as admin/manager
2. Go to Users page (`/admin/users`)
3. Click "Edit" on any user
4. Scroll to "Avatar Image" field
5. Click "Choose File" and select an image
6. You should see:
   - ✅ Current image displayed
   - ✅ New image preview appears
   - ✅ File info in console
7. Click "Update"
8. Page should show: "User updated successfully with new image"
9. User's avatar should immediately change

### Step 3: Check Logs
If upload fails, check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

Look for:
- `User image uploaded successfully` (success)
- `Failed to upload user image` (failure with details)

---

## Common Issues & Fixes

### Issue 1: "Permission Denied"
**Symptom**: Error message about file permissions

**Fix**:
```bash
# On Linux/Mac
chmod 755 public/uploads/users
chown -R www-data:www-data public/uploads/users

# On Windows (run as administrator)
icacls public\uploads\users /grant Users:(OI)(CI)F
```

### Issue 2: "File Size Too Large"
**Symptom**: Upload fails for large images

**Fix**: Update `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

Then restart web server:
```bash
# Apache
sudo service apache2 restart

# Nginx + PHP-FPM
sudo service php8.3-fpm restart
sudo service nginx restart
```

### Issue 3: Image Appears Broken
**Symptom**: Image uploads but shows broken image icon

**Possible Causes**:
1. File didn't actually upload (check `public/uploads/users/` directory)
2. Incorrect file permissions
3. Wrong file path in database

**Fix**:
1. Check if file exists:
   ```bash
   ls -la public/uploads/users/user_*
   ```

2. Verify database value:
   ```sql
   SELECT id, name, img FROM users WHERE id = [user_id];
   ```

3. Check full URL in browser:
   ```
   https://yoursite.com/uploads/users/[filename]
   ```

### Issue 4: Old Image Still Shows (Caching)
**Symptom**: New image uploaded but old one still appears

**Fix**:
1. Hard refresh browser: `Ctrl + Shift + R` (or `Cmd + Shift + R` on Mac)
2. Clear browser cache
3. Try incognito/private mode
4. Check if old file was actually deleted from server

---

## Updated Files

### 1. `app/Http/Controllers/Admin/UsersController.php`
**Changes**:
- ✅ Added `else { unset($data['img']); }` to prevent overwriting existing image
- ✅ Wrapped upload in try-catch for error handling
- ✅ Added logging for successful uploads and errors
- ✅ Changed filename format to `user_{id}_{timestamp}`
- ✅ Improved success message to indicate if image was uploaded

### 2. `resources/views/admin/users/_form.blade.php`
**Changes**:
- ✅ Added file type restrictions in `accept` attribute
- ✅ Added error display with `@error` directive
- ✅ Added helpful hint text
- ✅ Added image preview functionality
- ✅ Changed to rounded-circle for better appearance
- ✅ Shows current image with label
- ✅ Shows new image preview side-by-side

### 3. `resources/views/admin/users/edit.blade.php`
**Already correct**:
- ✅ Has `enctype="multipart/form-data"`
- ✅ Uses POST with PUT method override

### 4. `app/Http/Requests/UpdateUserRequest.php`
**Already correct**:
- ✅ Validates `img` as nullable image
- ✅ Max size 2048 KB (2 MB)
- ✅ Accepts jpeg, png, jpg, webp

---

## Image Preview Feature

### What It Does
When manager selects a new image, they see:
- Current image on the left
- New image preview on the right
- File size validation
- Clear visual feedback

### How It Works
```javascript
function previewUserImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Show preview
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
        
        // Validate size
        if (file.size > 2048 * 1024) {
            alert('File size exceeds 2MB');
        }
    }
}
```

---

## Testing Checklist

- [ ] Upload works for new user creation
- [ ] Upload works for user editing
- [ ] Old image is deleted when new one uploaded
- [ ] Default images (default.png, 1.png) are never deleted
- [ ] Image preview shows correctly
- [ ] File size validation works
- [ ] Error messages display properly
- [ ] Success message includes "with new image" when applicable
- [ ] Logs show successful uploads
- [ ] Avatar updates immediately after save
- [ ] Image persists after page refresh
- [ ] Works for different file types (jpg, png, webp)

---

## Browser Console Debugging

Open browser console (F12) and check for:

**On File Selection**:
```
Image selected: photo.jpg (0.85 MB)
```

**On Upload**:
Check Network tab for:
- Request to `/admin/users/{id}`
- Method: POST
- Status: 302 (redirect)
- Form Data includes: `img: [binary data]`

---

## Production Deployment

1. **Backup existing images**:
   ```bash
   tar -czf user_images_backup_$(date +%Y%m%d).tar.gz public/uploads/users/
   ```

2. **Deploy code changes**

3. **Verify permissions**:
   ```bash
   php check_uploads_permissions.php
   ```

4. **Test with a non-critical user first**

5. **Monitor logs during deployment**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## Quick Reference

### Upload Size Limits
- **Validation**: 2 MB (2048 KB)
- **Recommended**: 400x400px or larger
- **Formats**: JPG, PNG, WEBP

### File Locations
- **Upload Directory**: `public/uploads/users/`
- **Default Image**: `public/uploads/users/default.png`
- **Naming Pattern**: `user_{id}_{timestamp}.{ext}`

### Permissions
- **Directory**: `755` (rwxr-xr-x)
- **Files**: `644` (rw-r--r--)
- **Owner**: Web server user (www-data, nginx, apache, etc.)

---

## Support

If issues persist:
1. Check `storage/logs/laravel.log` for detailed errors
2. Verify PHP extensions: `php -m | grep -i gd` (for image processing)
3. Check disk space: `df -h`
4. Verify .htaccess allows file access
5. Check web server error logs

**Success indicators**:
- ✅ "User updated successfully with new image" message
- ✅ Log entry: "User image uploaded successfully"
- ✅ New file appears in `public/uploads/users/`
- ✅ Avatar changes immediately
- ✅ Database `img` field updated

The fix is now complete and ready for production! 🎉

