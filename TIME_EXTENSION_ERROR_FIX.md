# Time Extension Request Error Fix

## Error Description
When trying to submit the time extension request form, you get: "Failed to submit time extension request."

## Root Cause
The database table `task_time_extension_requests` does not exist yet. The migration hasn't been run.

## Solution

### Step 1: Run the Migration
```bash
php artisan migrate
```

This will create the `task_time_extension_requests` table with all necessary columns.

### Step 2: Verify the Setup
After running the migration, check if it worked:
```bash
php artisan migrate:status
```

You should see the migration `2025_10_26_085357_create_task_time_extension_requests_table` listed.

### Step 3: Test Again
Try submitting a time extension request again. It should work now.

## Files Ready
All necessary code is already in place:
- ✅ Migration file created
- ✅ Model created
- ✅ Controller methods ready
- ✅ Routes configured
- ✅ Frontend UI implemented

The only missing piece is running the migration.

## Alternative: Manual Table Creation

If for some reason you cannot run migrations, you can create the table manually using this SQL:

```sql
CREATE TABLE `task_time_extension_requests` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_id` BIGINT UNSIGNED NOT NULL,
    `requested_by` BIGINT UNSIGNED NOT NULL,
    `reviewed_by` BIGINT UNSIGNED NULL,
    `requested_days` INT NOT NULL,
    `reason` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `approved_days` INT NULL,
    `manager_notes` TEXT NULL,
    `reviewed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX (`task_id`),
    INDEX (`requested_by`),
    INDEX (`status`)
);
```

## After Fix
Once the migration is run, the feature will work fully:
- Users can request time extensions
- Managers will see pending requests
- Managers can approve/reject
- Task due dates will be updated automatically
- All events will be logged in task history
