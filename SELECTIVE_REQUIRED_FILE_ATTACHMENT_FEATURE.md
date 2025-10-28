# Selective Required File Attachment Feature

## Overview

This feature allows managers to selectively mark specific task files as "required for email" and only those marked files are automatically attached to confirmation emails. Users can still add additional files during email preparation.

## How It Works

### 1. Manager Control

Managers can now:
- **Mark Individual Files**: Select specific files to be required for email
- **Bulk Mark Files**: Mark multiple files as required at once
- **Add Notes**: Include notes explaining why files are required
- **Unmark Files**: Remove files from required status

### 2. Selective Attachment Process

When a confirmation email is sent:

1. **Email Preparation Attachments**: Files manually uploaded during email preparation are attached first
2. **Required Task Attachments**: Only files marked as `required_for_email = true` are automatically attached
3. **File Validation**: Each file is validated for existence and size limits (100MB max)
4. **Proper Naming**: Task attachments retain their original filenames

### 3. Database Changes

#### New Fields Added to `task_attachments` Table:
- `required_for_email` (boolean, default: false) - Marks if file is required for email
- `required_notes` (text, nullable) - Manager notes explaining why file is required

#### New Relationship:
- `Task->requiredAttachments()` - Returns only attachments marked as required

## Implementation Details

### Files Modified:
- `database/migrations/2025_10_28_154647_add_required_for_email_to_task_attachments_table.php` - New migration
- `app/Models/TaskAttachment.php` - Added new fields and casting
- `app/Models/Task.php` - Added `requiredAttachments()` relationship
- `app/Http/Controllers/TaskController.php` - Added manager methods
- `app/Mail/TaskConfirmationMail.php` - Updated to use required attachments only
- `app/Jobs/SendTaskConfirmationEmailJob.php` - Updated Gmail OAuth service
- `app/Services/RequiredFileAttachmentService.php` - Updated for selective attachment
- `routes/web.php` - Added new routes

### New Controller Methods:

#### `markAttachmentAsRequired(Request $request, Task $task, TaskAttachment $attachment)`
- Marks a single file as required/unrequired
- Only managers can use this method
- Creates history record
- Returns JSON response

#### `bulkMarkAttachmentsAsRequired(Request $request, Task $task)`
- Marks multiple files as required/unrequired
- Only managers can use this method
- Creates history record
- Returns JSON response

### New Routes:
```php
PUT /tasks/{task}/attachments/{attachment}/mark-required
PUT /tasks/{task}/attachments/bulk-mark-required
```

## Usage

### For Managers

#### Mark Individual File as Required:
```javascript
// AJAX request to mark file as required
fetch(`/tasks/${taskId}/attachments/${attachmentId}/mark-required`, {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        required_for_email: true,
        required_notes: 'This file contains important project specifications'
    })
});
```

#### Bulk Mark Files as Required:
```javascript
// AJAX request to bulk mark files
fetch(`/tasks/${taskId}/attachments/bulk-mark-required`, {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        attachment_ids: [1, 2, 3],
        required_for_email: true,
        required_notes: 'These files are essential for client review'
    })
});
```

### For Users

1. **Upload Files to Task**: Upload any files to the task during work
2. **Manager Marks Required**: Manager selects which files are required for email
3. **Send Confirmation Email**: When ready, send the confirmation email
4. **Automatic Inclusion**: Only marked files are automatically attached
5. **Manual Attachments**: Users can still add additional files during email preparation

## API Examples

### Mark File as Required
```bash
curl -X PUT "http://your-app.com/tasks/1/attachments/5/mark-required" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "required_for_email": true,
    "required_notes": "This file contains the final design specifications"
  }'
```

### Bulk Mark Files
```bash
curl -X PUT "http://your-app.com/tasks/1/attachments/bulk-mark-required" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "attachment_ids": [1, 2, 3],
    "required_for_email": true,
    "required_notes": "These files are required for client approval"
  }'
```

## Benefits

### For Managers
- **Selective Control**: Choose exactly which files are required
- **Quality Assurance**: Ensure only relevant files are sent
- **Documentation**: Add notes explaining file requirements
- **Audit Trail**: Full history of requirement changes

### For Users
- **Clarity**: Know exactly which files will be attached
- **Flexibility**: Can still add additional files if needed
- **Consistency**: Same files always attached for similar tasks
- **Professional Service**: Clients receive only relevant files

### For Clients
- **Relevant Content**: Only receive files that are actually needed
- **Complete Information**: All required files are included
- **Better Organization**: Files are properly categorized
- **Professional Service**: Consistent and complete deliverables

## Testing

Run the test script to verify functionality:

```bash
php test_selective_required_attachments.php
```

The test script will:
1. Check database structure
2. Test service methods
3. Validate file handling
4. Show system status

## Logging

The system logs detailed information about selective attachment processing:

```
TaskConfirmationMail: Processing required task attachments - Count: 3
Job: Processing required task attachments - Count: 3
RequiredFileAttachmentService: Found 3 required task attachments for task 1
RequiredFileAttachmentService: - design.pdf (Path: tasks/1/design.pdf, Exists: Yes, Size: 1024000 bytes, Notes: Final design specifications)
```

## Error Handling

### Common Issues and Solutions

1. **No Required Files Marked**
   - Result: No task files are automatically attached
   - Solution: Manager needs to mark files as required

2. **Required File Not Found**
   - Error: `Required task attachment file not found`
   - Solution: Check file path and ensure file exists

3. **File Too Large**
   - Error: `Required task attachment file too large`
   - Solution: Compress file or split into smaller parts

4. **Permission Denied**
   - Error: `Access denied. Only managers can mark files as required`
   - Solution: Ensure user has manager role

## Security Considerations

1. **Access Control**: Only managers can mark files as required
2. **File Validation**: All files are validated before attachment
3. **Path Security**: File paths are sanitized and validated
4. **CSRF Protection**: All forms include CSRF tokens
5. **History Tracking**: All changes are logged with user information

## Performance Impact

- **Reduced Email Size**: Only required files are attached
- **Faster Processing**: Less files to process and validate
- **Better Memory Usage**: Only necessary files loaded into memory
- **Improved User Experience**: Faster email sending

## Future Enhancements

Potential improvements:
1. **File Categories**: Organize files by type (documents, images, etc.)
2. **Template Requirements**: Predefined sets of required files for task types
3. **Auto-Mark Rules**: Automatically mark files based on file type or name
4. **Client Preferences**: Remember which files clients typically need
5. **File Compression**: Automatic compression for large files

## Migration Notes

### Existing Data
- All existing attachments have `required_for_email = false` by default
- Managers need to manually mark existing files as required
- No data loss during migration

### Backward Compatibility
- Existing email functionality continues to work
- Old code will work with new selective system
- Gradual migration possible

## Troubleshooting

### Debug Mode
Enable detailed logging by checking Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

### Common Commands
```bash
# Test the feature
php test_selective_required_attachments.php

# Check required files
php artisan tinker
>>> Task::find(1)->requiredAttachments->count()

# Check file status
php artisan tinker
>>> TaskAttachment::where('required_for_email', true)->get()
```

The selective required file attachment feature provides managers with precise control over which files are automatically included in confirmation emails, ensuring clients receive only the most relevant and necessary documents.
