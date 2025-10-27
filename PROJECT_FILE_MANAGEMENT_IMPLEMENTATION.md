# Project File Management for Managers

## Overview
Added comprehensive file management functionality that allows managers to upload, view, edit, and delete files directly within project folders and subfolders.

## Features Implemented

### 1. Database Structure
- **Migration**: `2025_10_27_114628_create_project_folder_files_table.php`
- **Model**: `ProjectFolderFile` at `app/Models/ProjectFolderFile.php`

#### Database Fields:
- `project_id`: Links file to a project
- `folder_id`: Links file to a specific folder (nullable for root level files)
- `uploaded_by`: User who uploaded the file
- `original_name`: Original filename
- `display_name`: Custom display name (editable)
- `description`: Optional file description
- `mime_type`: File type
- `size_bytes`: File size in bytes
- `disk`: Storage disk (public)
- `path`: Relative file path

### 2. Controller
**File**: `app/Http/Controllers/ProjectFolderFileController.php`

#### Methods:
- `index()`: List files in a project/folder
- `store()`: Upload new files (managers only)
- `update()`: Edit file metadata (managers only)
- `destroy()`: Delete files (managers only)
- `download()`: Download files

### 3. Routes
Added to `routes/web.php` under manager middleware:
```php
Route::get('projects/{project}/files', [ProjectFolderFileController::class, 'index'])->name('projects.files.index');
Route::post('projects/{project}/files', [ProjectFolderFileController::class, 'store'])->name('projects.files.store');
Route::put('projects/{project}/files/{file}', [ProjectFolderFileController::class, 'update'])->name('projects.files.update');
Route::delete('projects/{project}/files/{file}', [ProjectFolderFileController::class, 'destroy'])->name('projects.files.destroy');
Route::get('projects/{project}/files/{file}/download', [ProjectFolderFileController::class, 'download'])->name('projects.files.download');
```

### 4. User Interface
**File**: `resources/views/projects/show.blade.php`

#### New Sections:
1. **Files Section**: Displays files in the current folder/project
2. **Upload Modal**: Form for uploading files with display name and description
3. **Edit Modal**: Form for editing file metadata
4. **File Cards**: Responsive grid showing file details

#### Features:
- File type icons (PDF, images, documents, etc.)
- Human-readable file sizes
- Uploader information
- Download, Edit, Delete buttons (managers only)
- Responsive design

### 5. JavaScript Functionality
- AJAX file loading
- File upload with progress
- File editing
- File deletion with confirmation
- File type icon detection
- Responsive file grid display

## Manager Capabilities

Managers (roles: `admin`, `manager`, `sub-admin`) can:
1. **Upload Files**: Add files to any project folder
2. **View Files**: Browse files in folders and subfolders
3. **Edit Metadata**: Change display name and description
4. **Delete Files**: Remove files from projects
5. **Download Files**: Download files from the interface

## File Storage Structure

Files are stored in: `public/projectsofus/{project-id}-{slug}/{folder-path}/`

Example:
- Project "Orion Design" (ID: 1) → `projectsofus/1-orion-design/`
- Folder "Documents" → `projectsofus/1-orion-design/documents/`
- Subfolder "Drafts" → `projectsofus/1-orion-design/documents/drafts/`

## Security

1. **Access Control**: Only managers can upload, edit, or delete files
2. **File Validation**: Maximum file size: 100MB
3. **File Sanitization**: Unique filename generation to prevent conflicts
4. **Project Verification**: Files are verified to belong to the correct project
5. **CSRF Protection**: All forms include CSRF tokens

## Usage

### For Managers

1. **Upload a File**:
   - Navigate to any project
   - Click "Upload File" button
   - Select file
   - (Optional) Enter display name and description
   - Click "Upload"

2. **Edit File**:
   - Click the edit icon on any file
   - Modify display name and/or description
   - Click "Save Changes"

3. **Delete File**:
   - Click the delete icon
   - Confirm deletion

4. **Download File**:
   - Click the download icon or filename

### File Organization

- Files are organized within project folder structure
- Each folder displays only files in that specific folder
- Root level displays files not in any subfolder
- Files automatically follow the folder hierarchy

## Technical Details

### Model Relationships
```php
// ProjectFolderFile has relationships with:
- Project (belongsTo)
- ProjectFolder (belongsTo)
- User/uploader (belongsTo)

// Project and ProjectFolder have:
- files() relationship to ProjectFolderFile
```

### API Endpoints

All endpoints return JSON:
- `GET /projects/{project}/files?folder={folder_id}`: List files
- `POST /projects/{project}/files`: Upload file
- `PUT /projects/{project}/files/{file}`: Update file metadata
- `DELETE /projects/{project}/files/{file}`: Delete file
- `GET /projects/{project}/files/{file}/download`: Download file

### File Upload Process

1. File validation (size, type)
2. Manager permission check
3. Folder path construction
4. Directory creation if needed
5. Unique filename generation
6. File storage
7. Database record creation
8. Response with file details

## Migration

Run migration:
```bash
php artisan migrate
```

This creates the `project_folder_files` table with proper indexes and foreign keys.

## Future Enhancements

Potential improvements:
- File versioning
- File search functionality
- Bulk file operations
- File comments/notes
- File access logs/audit trail
- File preview in browser
- File permission levels per user
- Integration with cloud storage (S3, etc.)

