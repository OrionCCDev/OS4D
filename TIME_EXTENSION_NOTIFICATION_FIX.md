# Time Extension Notification Fix

## Problem
Managers were not receiving notifications when users submitted time extension requests.

## Root Cause
The `notifyManagers` method in `app/Models/Task.php` was only querying for users with `admin` or `manager` roles, but NOT including `sub-admin` role. However, the `isManager()` method includes all three roles.

## Fixes Applied

### 1. Updated Manager Query
**File**: `app/Models/Task.php` (line 493)
**Before**:
```php
$managers = User::where('role', 'admin')->orWhere('role', 'manager')->get();
```

**After**:
```php
$managers = User::whereIn('role', ['admin', 'manager', 'sub-admin'])->get();
```

### 2. Added Time Extension to High Priority Notifications
**File**: `app/Models/Task.php` (line 469)
Added `time_extension_requested` and `time_extension_reviewed` to the list of high-priority notification types.

### 3. Added Logging for Debugging
**File**: `app/Http/Controllers/TaskController.php` (line 2506)
Added logging to track notification sending process.

## Testing

After these changes, managers should receive notifications when:
- A user requests a time extension
- The notification will appear in their notification list
- It will be marked as high priority
- All managers (admin, manager, and sub-admin) will be notified

## How to Verify

1. Submit a time extension request as a user
2. Check manager accounts (admin, manager, or sub-admin)
3. Look for notification with title: "Time Extension Requested"
4. The message should show: "Task '[task name]' has a time extension request: X days"

## What's Fixed

✅ All manager roles (admin, manager, sub-admin) now receive notifications
✅ Time extension notifications are marked as high priority
✅ Better error logging for debugging
✅ Notification system properly integrated
