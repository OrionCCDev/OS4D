# Production Deployment Instructions

## Step-by-Step Instructions for cPanel Terminal

Run these commands in your cPanel terminal:

```bash
# 1. Navigate to your Laravel application
cd ~/public_html/odc.com

# 2. Fetch latest changes from remote
git fetch origin

# 3. Check available branches
git branch -a

# 4. You should now see origin/performance-enhancement
# Switch to the branch
git checkout performance-enhancement

# 5. Pull latest changes
git pull origin performance-enhancement

# 6. Run the migration (SAFE - won't duplicate indexes)
php artisan migrate

# 7. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 8. Test the application
```

## If `git checkout performance-enhancement` still fails:

Try this instead:

```bash
# Fetch and checkout in one command
git fetch origin performance-enhancement:performance-enhancement
git checkout performance-enhancement
```

## Alternative: Merge into Main (Recommended for Production)

If you prefer to keep main as production, merge the changes:

```bash
# On the server
cd ~/public_html/odc.com

# Switch to main
git checkout main

# Pull latest from main
git pull origin main

# Merge performance-enhancement into main
git merge performance-enhancement

# Run migration
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

## Verification Steps

After deployment, verify the indexes were created:

```bash
# Connect to MySQL
mysql -u username -p database_name

# Check if indexes exist
SHOW INDEX FROM tasks;
SHOW INDEX FROM users;
SHOW INDEX FROM projects;
SHOW INDEX FROM unified_notifications;

# You should see the new indexes with names like:
# - tasks_status_idx
# - tasks_priority_idx
# - users_role_idx
# - etc.
```

## Rollback Instructions (If Needed)

If something goes wrong:

```bash
# Switch back to main
git checkout main

# Run rollback
php artisan migrate:rollback --step=1

# Clear caches
php artisan cache:clear
php artisan config:clear
```

