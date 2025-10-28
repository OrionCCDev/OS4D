# Gmail Integration with Required Files

## Overview

The Gmail integration has been enhanced to automatically inform users about which files are required for email attachment. When users click "Send via Gmail", they now receive specific information about required files that need to be manually attached in Gmail.

## Problem Solved

**Before**: Users had to guess which files to attach when using Gmail integration
**After**: Users get a clear list of required files with names and sizes

## How It Works

### 1. Email Preparation Page
- **Required Files Section**: Green alert box shows required files
- **File Information**: Names and sizes displayed
- **Visual Indicator**: Clear indication of what needs to be attached

### 2. Gmail Button Click
- **API Call**: Fetches required files from backend
- **File List**: Shows specific files that need to be attached
- **Instructions**: Enhanced confirmation with file details

### 3. Gmail Integration
- **Pre-filled Email**: To, CC, BCC, Subject, Body
- **File Guidance**: Clear list of required files
- **User Action**: Manually attach files in Gmail
- **Completion**: Return to app and mark as sent

## Technical Implementation

### Backend Changes

#### New API Endpoint
```php
Route::get('tasks/{task}/required-files', [TaskController::class, 'getRequiredFilesForEmail'])
    ->name('tasks.required-files');
```

#### Controller Method
```php
public function getRequiredFilesForEmail(Task $task)
{
    $requiredFiles = $task->requiredAttachments()->get();
    
    $files = [];
    foreach ($requiredFiles as $file) {
        $files[] = [
            'id' => $file->id,
            'name' => $file->original_name,
            'size' => $file->size_bytes,
            'download_url' => route('tasks.attachments.download', $file)
        ];
    }
    
    return response()->json([
        'success' => true,
        'files' => $files,
        'count' => count($files)
    ]);
}
```

### Frontend Changes

#### Required Files Section
```html
<div id="requiredFilesSection" class="alert alert-success" style="display: none;">
    <i class="bx bx-paperclip"></i>
    <div>
        <strong>Required Files for Email:</strong>
        <div id="requiredFilesList" class="mt-2"></div>
        <small class="text-muted">These files will be automatically attached when using "Send via Server" or need to be manually attached when using "Send via Gmail".</small>
    </div>
</div>
```

#### JavaScript Functions
```javascript
// Load required files on page load
async function loadRequiredFiles() {
    const requiredFiles = await getRequiredFilesInfo();
    // Display files in the UI
}

// Get required files from API
async function getRequiredFilesInfo() {
    const response = await fetch(`/tasks/${taskId}/required-files`);
    const data = await response.json();
    return data.files;
}

// Enhanced Gmail button with file information
sendViaGmailBtn.addEventListener('click', async function() {
    // ... existing Gmail URL construction ...
    
    const requiredFiles = await getRequiredFilesInfo();
    let message = '✅ Gmail opened!\n\n📌 Next Steps:\n1. Attach any required files in Gmail\n2. Review and send the email\n3. Come back here and click "Mark as Sent" button';
    
    if (requiredFiles.length > 0) {
        message += '\n\n📎 Required Files to Attach:\n';
        requiredFiles.forEach(file => {
            message += `• ${file.name}\n`;
        });
        message += '\n💡 Tip: You can download these files from the task page if needed.';
    } else {
        message += '\n\n✅ No required files to attach.';
    }
    
    alert(message);
});
```

## User Experience

### Email Preparation Page
1. **See Required Files**: Green section shows required files
2. **File Details**: Names and sizes displayed
3. **Clear Guidance**: Know what needs to be attached

### Gmail Integration
1. **Click Gmail Button**: "Send via Gmail (Recommended)"
2. **Get File List**: Specific files to attach
3. **Open Gmail**: Pre-filled email opens
4. **Attach Files**: Manually attach required files
5. **Send Email**: Send from Gmail
6. **Mark Complete**: Return and click "Mark as Sent"

## Benefits

### For Users
- **Clear Guidance**: Know exactly which files to attach
- **No Guesswork**: Specific file names provided
- **File Information**: Sizes and details available
- **Download Links**: Can download files if needed

### For Managers
- **Control**: Mark files as required
- **Consistency**: Same files always attached
- **Quality**: Ensure complete documentation

### For Clients
- **Complete Information**: Receive all required files
- **Professional Service**: Consistent deliverables
- **Better Organization**: Files properly categorized

## File Information Display

### Required Files Section
- **Green Alert Box**: Clear visual indicator
- **File List**: Bulleted list with icons
- **File Names**: Bold text for clarity
- **File Sizes**: Formatted sizes (KB, MB, GB)
- **Instructions**: Clear guidance on usage

### Gmail Confirmation
- **File Count**: Number of required files
- **File Names**: Specific files to attach
- **Download Tip**: Helpful guidance
- **No Files**: Confirmation when none required

## API Response Format

```json
{
    "success": true,
    "files": [
        {
            "id": 123,
            "name": "project_design.pdf",
            "size": 1048576,
            "download_url": "/tasks/456/attachments/123/download"
        }
    ],
    "count": 1
}
```

## Error Handling

### API Errors
- **Network Issues**: Graceful fallback
- **No Files**: Empty array returned
- **Permission Errors**: Proper error messages

### UI Errors
- **Loading Failures**: Console logging
- **Display Issues**: Fallback to hidden section
- **User Feedback**: Clear error messages

## Testing

### Manual Testing
1. **Mark Files**: Manager marks files as required
2. **Email Prep**: Go to email preparation page
3. **See Section**: Verify required files section appears
4. **Gmail Button**: Click and check file list
5. **Gmail Test**: Open Gmail and verify pre-fill
6. **File Attach**: Test manual file attachment

### Automated Testing
```bash
php test_gmail_integration_required_files.php
```

## Future Enhancements

Potential improvements:
1. **Auto-Download**: Automatically download required files
2. **File Preview**: Show file previews in Gmail
3. **Bulk Attach**: Attach multiple files at once
4. **File Validation**: Check file types and sizes
5. **Template Integration**: Include files in email templates

## Troubleshooting

### Common Issues
1. **Files Not Showing**: Check API endpoint
2. **Gmail Not Opening**: Check popup blocker
3. **File List Empty**: Verify required files exist
4. **API Errors**: Check network connection

### Debug Steps
1. **Check Console**: Look for JavaScript errors
2. **Verify API**: Test endpoint directly
3. **Check Permissions**: Ensure user access
4. **Test Network**: Verify connectivity

The Gmail integration now provides complete guidance on required files, ensuring users never miss important attachments when sending emails via Gmail.
