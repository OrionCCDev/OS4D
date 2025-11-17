-- SQL Script to Test Force Delete User
-- This script will help identify any database constraint errors
-- CURRENT USER ID: 16

SET @USER_ID = 16; -- User ID to delete

-- Start transaction for testing
START TRANSACTION;

-- Check if user exists
SELECT 'Checking if user exists...' AS Step;
SELECT id, name, email, role, status FROM users WHERE id = @USER_ID;

-- Step 1: Delete Spatie permissions
SELECT 'Step 1: Deleting Spatie permissions...' AS Step;
DELETE FROM model_has_roles WHERE model_id = @USER_ID AND model_type = 'App\\Models\\User';
DELETE FROM model_has_permissions WHERE model_id = @USER_ID AND model_type = 'App\\Models\\User';

-- Step 2: Update task relationships (set to NULL)
SELECT 'Step 2: Updating task relationships...' AS Step;
UPDATE tasks SET created_by = NULL WHERE created_by = @USER_ID;
UPDATE tasks SET assigned_to = NULL WHERE assigned_to = @USER_ID;
UPDATE tasks SET internal_approved_by = NULL WHERE internal_approved_by = @USER_ID;
UPDATE tasks SET manager_override_by = NULL WHERE manager_override_by = @USER_ID;
UPDATE tasks SET closed_by = NULL WHERE closed_by = @USER_ID;

-- Step 3: Delete from task cascading tables (if they exist)
SELECT 'Step 3: Deleting from task cascade tables...' AS Step;
-- Check if tables exist first, then delete
DELETE FROM task_assignees WHERE user_id = @USER_ID;
DELETE FROM task_status_changes WHERE changed_by = @USER_ID;
DELETE FROM task_approvals WHERE reviewer_id = @USER_ID;
DELETE FROM task_comments WHERE user_id = @USER_ID;
DELETE FROM task_histories WHERE user_id = @USER_ID;
DELETE FROM task_email_preparations WHERE prepared_by = @USER_ID;
DELETE FROM task_time_extension_requests WHERE requested_by = @USER_ID;
UPDATE task_time_extension_requests SET reviewed_by = NULL WHERE reviewed_by = @USER_ID;

-- Step 4: Delete contractor emails
SELECT 'Step 4: Deleting contractor emails...' AS Step;
DELETE FROM contractor_emails WHERE sent_by = @USER_ID;

-- Step 5: Update project relationships
SELECT 'Step 5: Updating project relationships...' AS Step;
DELETE FROM project_user WHERE user_id = @USER_ID;
UPDATE projects SET owner_id = NULL WHERE owner_id = @USER_ID;

-- Step 6: Delete evaluations and performance
SELECT 'Step 6: Deleting evaluations and performance...' AS Step;
DELETE FROM employee_evaluations WHERE user_id = @USER_ID;
DELETE FROM employee_evaluations WHERE evaluated_by = @USER_ID;
DELETE FROM performance_metrics WHERE user_id = @USER_ID;
DELETE FROM report_templates WHERE created_by = @USER_ID;

-- Step 7: Delete notifications
SELECT 'Step 7: Deleting notifications...' AS Step;
DELETE FROM custom_notifications WHERE user_id = @USER_ID;
DELETE FROM unified_notifications WHERE user_id = @USER_ID;

-- Step 8: Delete from other tables
SELECT 'Step 8: Deleting from other tables...' AS Step;
DELETE FROM delete_requests WHERE requester_id = @USER_ID;
UPDATE delete_requests SET reviewed_by = NULL WHERE reviewed_by = @USER_ID;
UPDATE activity_logs SET user_id = NULL WHERE user_id = @USER_ID;
DELETE FROM time_trackings WHERE user_id = @USER_ID;
DELETE FROM user_preferences WHERE user_id = @USER_ID;
DELETE FROM project_folder_files WHERE uploaded_by = @USER_ID;

-- Step 9: Check for any remaining foreign key constraints
SELECT 'Step 9: Checking for foreign key constraints...' AS Step;
-- This will show any tables that still reference this user
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    REFERENCED_TABLE_NAME = 'users'
    AND REFERENCED_COLUMN_NAME = 'id'
    AND TABLE_SCHEMA = DATABASE();

-- Step 10: Finally delete the user
SELECT 'Step 10: Deleting user record...' AS Step;
DELETE FROM users WHERE id = @USER_ID;

-- Verify deletion
SELECT 'Verifying deletion...' AS Step;
SELECT id, name, email FROM users WHERE id = @USER_ID;

-- ROLLBACK to undo changes (remove this line if you want to actually delete)
-- ROLLBACK;

-- COMMIT to apply changes (uncomment this line if you want to actually delete)
-- COMMIT;

SELECT 'Test completed. Check for errors above. If no errors, uncomment COMMIT to apply changes.' AS Result;

