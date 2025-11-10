# Deploy Blade Syntax Fix to Production

## The Problem
The production server at https://odc.com.orion-contracting.com is still using the old cached version of `resources/views/tasks/show.blade.php` which has the Blade syntax error.

## The Solution
The fix has been committed and pushed to the repository. You need to deploy it to production.

## Deployment Steps

### Option 1: SSH into Production Server (Recommended)

```bash
# 1. SSH into your production server
ssh user@odc.com.orion-contracting.com

# 2. Navigate to your Laravel application directory
cd /path/to/your/laravel/app

# 3. Pull the latest changes
git fetch origin
git pull origin claude/fix-tasks-show-blade-syntax-011CUzKBrJSuM83ZeJd3WJUR

# OR if you want to pull from main after merging:
# git pull origin main

# 4. Clear ALL Laravel caches (IMPORTANT!)
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 5. Optimize for production (optional but recommended)
php artisan optimize

# 6. Test the fix
# Visit: https://odc.com.orion-contracting.com/tasks/60
```

### Option 2: Use Deployment Tool

If you use a deployment tool like Laravel Forge, Envoyer, or Deployer:

1. Deploy the branch `claude/fix-tasks-show-blade-syntax-011CUzKBrJSuM83ZeJd3WJUR`
2. Make sure the deployment script includes cache clearing commands
3. Test the URL after deployment

### Option 3: Manual File Upload (Not Recommended)

If you can't access via git:

1. Download the fixed file from: https://github.com/OrionCCDev/OS4D/blob/claude/fix-tasks-show-blade-syntax-011CUzKBrJSuM83ZeJd3WJUR/resources/views/tasks/show.blade.php
2. Upload it to your server at: `resources/views/tasks/show.blade.php`
3. Clear caches via your hosting control panel or SSH

## What Was Fixed

**Lines 64-66** - Separated consecutive `@elseif` directives:

### Before (Broken):
```blade
@elseif($isAccepted && $daysUntilStart <= 0) {{-- Don't show anything --}} @elseif($daysUntilStart>= 0)
```

### After (Fixed):
```blade
@elseif($isAccepted && $daysUntilStart <= 0)
    {{-- Don't show anything if task is accepted and start date has passed --}}
@elseif($daysUntilStart >= 0)
```

## Verification

After deployment, verify the fix by:
1. Visiting https://odc.com.orion-contracting.com/tasks/60
2. The page should load without "ParseError - Internal Server Error"
3. Check other task pages to ensure they work too

## Troubleshooting

If the error persists after deployment:

1. **Clear browser cache** - Press Ctrl+F5 or Cmd+Shift+R
2. **Check file timestamp** - Verify the file was actually updated on the server
3. **Verify git branch** - Make sure you're on the correct branch: `git branch --show-current`
4. **Check file permissions** - Ensure the web server can read the file
5. **Restart web server** (if using php-fpm): `sudo service php-fpm restart`
6. **Check Laravel logs** - Look in `storage/logs/laravel.log` for any errors
