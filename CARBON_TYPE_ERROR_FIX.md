# Carbon Type Error Fix - Time Extension Approval

## Error
When a manager approves a time extension request, this error appears:
```
Error: Carbon\Carbon::rawAddUnit(): Argument #3 ($value) must be of type int|float, string given
```

## Root Cause
The `approved_days` value from the form input is received as a string (e.g., "1") but the Carbon `addDays()` method requires an integer or float.

## Fix Applied

### File: `app/Http/Controllers/TaskController.php`

**Changes:**
1. Cast `approved_days` to integer when approving
2. Refresh task data to get current due date from database
3. Cast again when calling `addDays()` to ensure integer type

### Code Changes:

**Before:**
```php
$approvedDays = $approveAction ? ($validated['approved_days'] ?? $extensionRequest->requested_days) : null;
// ...
$newDueDate = Carbon::parse($task->due_date)->addDays($approvedDays);
```

**After:**
```php
// Ensure approved_days is an integer, not a string
$approvedDays = $approveAction ? (int)($validated['approved_days'] ?? $extensionRequest->requested_days) : null;
// ...
// Ensure we have the current due date from database
$currentDueDate = $task->fresh()->due_date;
$newDueDate = Carbon::parse($currentDueDate)->addDays((int)$approvedDays);
```

## What This Fixes

✅ `approved_days` is now properly converted to integer
✅ Prevents Carbon type error when adding days to date
✅ Gets fresh due date from database before calculation
✅ Ensures date calculation is accurate

## Testing

1. As a manager, view a task with a pending time extension
2. Approve the request
3. It should work without errors
4. The due date should be updated correctly
5. Task history should show the old and new due dates

## Additional Benefits

- More robust type handling
- Database refresh ensures accurate date calculations
- Better error prevention
