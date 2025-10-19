# Task Reassignment & Employee Management System

## Overview
This comprehensive system allows managers to handle employee transitions (resignations, leaves, role changes) by reassigning tasks to other team members while maintaining complete task history and accountability.

## Features

### 1. User Status Management
- **Active**: User can log in and work on tasks (default)
- **Inactive**: User is temporarily unavailable (on leave, etc.)
- **Resigned**: User has left the organization

### 2. Individual Task Reassignment
Reassign any single task from one user to another with full tracking.

### 3. Bulk Task Reassignment
Transfer all active tasks from one user to another in a single operation - perfect for handling employee departures.

### 4. Task History Tracking
Every reassignment is automatically logged in the task history with:
- Who initiated the reassignment
- Previous assignee
- New assignee
- Reason for reassignment
- Timestamp

---

## How to Use

### For Employee Resignation/Departure

#### Step 1: Access User Management
1. Navigate to **Users** page (`/admin/users`)
2. Find the employee who is resigning/leaving
3. Click the **"Reassign"** button next to their name

#### Step 2: Review Active Tasks
You'll see a summary of:
- Total active tasks
- Tasks in progress
- Overdue tasks

#### Step 3: Select New Assignee
1. Choose a user from the **"New Assignee"** dropdown
2. All active users (except the departing employee) will be available
3. Enter a **reason** (e.g., "Employee resigned - transitioning tasks")

#### Step 4: Select Tasks to Reassign
- By default, ALL active tasks are selected
- You can:
  - **Select All**: Reassign all tasks
  - **Deselect All**: Start fresh and pick specific tasks
  - **Individual Selection**: Check/uncheck specific tasks

#### Step 5: Optionally Deactivate User
- Check **"Mark user as Inactive"** to prevent login access
- This keeps their account and history intact
- User can be reactivated later if needed

#### Step 6: Confirm Reassignment
1. Click **"Reassign Selected Tasks"**
2. Confirm the action
3. Wait for success message
4. System will automatically:
   - Update all selected tasks
   - Create history entries
   - Notify new assignees
   - Update user status (if requested)

---

### For Individual Task Reassignment

#### Method 1: From Task Details Page
1. Open any task details page
2. Find the **"Assigned To"** field
3. Click the **"Reassign"** button (managers/admins only)
4. Select new assignee
5. Optionally add a reason
6. Click **"Reassign Task"**

The new assignee will be notified immediately.

#### Method 2: From Task Edit Page
1. Open any task details page
2. Click **"Edit"** button (managers only)
3. Change the **"Assigned To"** dropdown
4. Click **"Update"** button
5. Confirm the reassignment when prompted
6. Task is updated and reassigned

**Benefits of Edit Method**:
- Change multiple task properties at once
- See full task context while reassigning
- Visual confirmation before submitting
- Button changes to "Update & Reassign" when changing assignee

---

## Database Changes

### Migration File
```
database/migrations/2025_10_19_194653_add_status_to_users_table.php
```

### New Fields in `users` Table
- `status` (enum): 'active', 'inactive', 'resigned'
- `deactivated_at` (timestamp): When user was deactivated
- `deactivation_reason` (text): Why user was deactivated

### Running the Migration
```bash
php artisan migrate
```

---

## API Endpoints

### Bulk Reassignment
**GET** `/users/{user}/bulk-reassignment`
- Shows bulk reassignment page for a specific user
- **Access**: Managers and Admins only

**POST** `/tasks/bulk-reassign`
- Performs bulk reassignment
- **Parameters**:
  - `from_user_id` (required): User to reassign from
  - `to_user_id` (required): User to reassign to
  - `task_ids[]` (required): Array of task IDs to reassign
  - `reassignment_reason` (optional): Explanation
  - `deactivate_user` (optional): Set to 1 to deactivate user

### Individual Task Reassignment
**POST** `/tasks/{task}/reassign`
- Reassigns a single task
- **Parameters**:
  - `new_assignee_id` (required): New user ID
  - `reassignment_reason` (optional): Explanation

### User Status Management
**POST** `/users/{user}/status`
- Updates user status
- **Parameters**:
  - `status` (required): 'active', 'inactive', or 'resigned'
  - `reason` (optional): Explanation

### Get User's Active Tasks
**GET** `/users/{user}/active-tasks`
- Returns count of user's active and completed tasks
- **Response**:
```json
{
  "active_tasks": 15,
  "completed_tasks": 42,
  "total_tasks": 57
}
```

---

## User Interface Components

### 1. Users List Page
**Location**: `resources/views/admin/users/index.blade.php`

**Features**:
- Status badges (Active, Inactive, Resigned)
- **"Reassign"** button for each user
- Quick access to bulk reassignment

### 2. Bulk Reassignment Page
**Location**: `resources/views/tasks/bulk-reassignment.blade.php`

**Features**:
- Summary cards showing task counts
- Filterable task list with full details
- Select all/deselect all functionality
- User deactivation option
- Real-time validation
- Success/error notifications

### 3. Task Details Page
**Location**: `resources/views/tasks/show.blade.php`

**Features**:
- **"Reassign"** button next to assignee name
- Modal for quick reassignment
- Available users dropdown
- Reason input field

### 4. Task Edit Page
**Location**: `resources/views/tasks/edit.blade.php`

**Features**:
- **"Assigned To"** dropdown in edit form
- Shows current assignee with "- Current" label
- Live button text change when reassigning
- Confirmation prompt before saving
- Only active users are shown in dropdown
- Visual indicator: button changes to warning color when reassigning

---

## Controller: TaskReassignmentController

### Methods

#### `showBulkReassignment(User $user)`
Displays the bulk reassignment interface for a specific user.

#### `reassignTask(Request $request, Task $task)`
Handles individual task reassignment with validation and notifications.

#### `bulkReassign(Request $request)`
Processes bulk task reassignment with:
- Transaction safety
- History logging
- Notifications
- Optional user deactivation

#### `updateUserStatus(Request $request, User $user)`
Updates user status (active/inactive/resigned) with reason tracking.

#### `getUserActiveTasks(User $user)`
Returns statistics about a user's task workload.

---

## Best Practices

### When an Employee Resigns
1. **Immediate Action**: As soon as resignation is confirmed
2. **Review Tasks**: Check task status and priorities
3. **Strategic Assignment**: Consider:
   - New assignee's workload
   - Task complexity and familiarity
   - Project continuity
4. **Document Reason**: Always include why tasks are being reassigned
5. **Deactivate Account**: Mark user as "resigned" to maintain audit trail
6. **DON'T DELETE**: Never delete user accounts - use status instead

### When an Employee is Temporarily Unavailable
1. **Mark as Inactive**: Use "inactive" status instead of "resigned"
2. **Selective Reassignment**: Only reassign urgent/critical tasks
3. **Document Duration**: Include expected return date in reason
4. **Reactivate on Return**: Change status back to "active"

### For Task Continuity
1. **Knowledge Transfer**: Ensure new assignee has context
2. **Update Task Notes**: Add transition information
3. **Priority Review**: Adjust priorities if needed
4. **Monitor Progress**: Check in with new assignee

---

## Security & Permissions

### Access Control
- **Bulk Reassignment**: Managers and Admins only
- **Individual Reassignment**: Managers and Admins only
- **View History**: Managers and assigned users

### Middleware
- `manager.or.admin`: Restricts bulk operations
- `task.access`: Controls task-level permissions

---

## Task History Integration

Every reassignment creates a history entry:

```php
TaskHistory::create([
    'task_id' => $task->id,
    'user_id' => auth()->id(), // Who made the change
    'action' => 'reassigned',
    'old_value' => 'John Doe',
    'new_value' => 'Jane Smith',
    'description' => 'Employee resigned - transitioning tasks'
]);
```

This ensures complete audit trail and accountability.

---

## Notifications

### New Assignee Notification
When a task is reassigned, the new assignee receives:
- **Type**: `task_assigned`
- **Title**: "New Task Assigned"
- **Message**: "You have been assigned a task: [Task Title]"
- **Priority**: Normal
- **Actionable**: Yes (links to task details)

---

## Example Scenarios

### Scenario 1: Employee Resigns
**Situation**: Sarah is leaving the company with 12 active tasks

**Steps**:
1. Manager goes to Users page
2. Clicks "Reassign" next to Sarah's name
3. Sees 12 active tasks (3 overdue, 5 in progress)
4. Selects John as new assignee
5. Enters reason: "Sarah resigned - effective date 10/20/2025"
6. Checks "Mark Sarah as Inactive"
7. Clicks "Reassign Selected Tasks"
8. System transfers all 12 tasks to John
9. John receives 12 notifications
10. Sarah's account is marked "resigned"
11. All task history is preserved

### Scenario 2: Employee on Medical Leave
**Situation**: Mike is on medical leave for 2 weeks with urgent tasks

**Steps**:
1. Manager reviews Mike's tasks
2. Identifies 3 urgent tasks
3. Uses individual reassignment for each:
   - Task A → Lisa
   - Task B → Tom
   - Task C → Emma
4. Adds reason: "Medical leave - urgent tasks only"
5. Marks Mike as "inactive"
6. Remaining 8 tasks stay with Mike
7. When Mike returns, change status back to "active"

### Scenario 3: Project Handover
**Situation**: Moving all Project X tasks from Alex to Maria

**Steps**:
1. Filter tasks by project
2. Use bulk reassignment
3. Select only Project X tasks
4. Assign to Maria
5. Reason: "Project handover - Alex moving to new project"
6. Keep Alex active for other work

---

## Troubleshooting

### Tasks Not Showing in Bulk Reassignment
**Issue**: No tasks appear for user

**Solution**: Only active tasks (not completed/cancelled) are shown. If user has no active tasks, page will display "No Active Tasks" message.

### Cannot Select New Assignee
**Issue**: Dropdown is empty

**Solution**: Ensure there are other active users in the system. Inactive/resigned users won't appear in the list.

### Reassignment Fails
**Issue**: Error message appears

**Solution**: Check:
1. New assignee is still active
2. Task still exists
3. User has proper permissions
4. Database connection is stable

---

## Future Enhancements (Optional)

### Suggested Features:
1. **Batch Notifications**: Group notifications when many tasks are reassigned
2. **Workload Balancing**: Suggest assignees based on current workload
3. **Skills Matching**: Recommend assignees based on task type/skills
4. **Automated Transitions**: Auto-reassign when user marked as resigned
5. **Reassignment Templates**: Save common reassignment patterns
6. **Bulk Status Changes**: Change multiple users' status at once

---

## Maintenance

### Regular Tasks:
1. **Review Inactive Users**: Monthly check for users who should be reactivated
2. **Audit Resignations**: Ensure all resigned users have no active tasks
3. **Clean History**: Archive old task histories (optional)
4. **Monitor Workloads**: Ensure reassignments are balanced

---

## Support

For questions or issues:
1. Check task history for reassignment records
2. Review user status in Users page
3. Check application logs for errors
4. Contact system administrator

---

## Technical Notes

### Database Transactions
All bulk operations use database transactions to ensure data integrity:
```php
DB::beginTransaction();
try {
    // Reassignment logic
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Error handling
}
```

### Performance
- Bulk reassignments are optimized for large task volumes
- History creation is batched
- Notifications are queued (if queue system is configured)

### Backward Compatibility
- Existing users automatically have status = 'active'
- Old tasks remain unchanged
- History system integrates seamlessly

---

## Summary

This Task Reassignment & Employee Management System provides a complete solution for handling employee transitions while maintaining:
- ✅ Complete task history and audit trail
- ✅ User account preservation (no deletions)
- ✅ Flexible reassignment options (individual or bulk)
- ✅ Automated notifications
- ✅ Transaction safety
- ✅ Manager-level access control
- ✅ Production-ready implementation

**Remember**: Never delete user accounts. Always use the status system to maintain data integrity and audit trails.

