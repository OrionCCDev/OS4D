# How to Run SQL Script in Production

## ⚠️ IMPORTANT SAFETY WARNINGS

1. **BACKUP FIRST**: Always backup your database before running deletion scripts in production
2. **USE TRANSACTIONS**: The script uses transactions - you can ROLLBACK if something goes wrong
3. **TEST FIRST**: Test with a non-critical user ID first
4. **VERIFY USER ID**: Double-check the user ID before running
5. **OFF-PEAK HOURS**: Run during low-traffic periods if possible

## Method 1: Using MySQL Command Line (Recommended for Production)

### Step 1: Connect to Production Database
```bash
# SSH into your production server
ssh user@your-production-server.com

# Connect to MySQL (use credentials from .env file)
mysql -h [DB_HOST] -u [DB_USERNAME] -p[DB_PASSWORD] [DB_DATABASE]

# Or if using local socket
mysql -u [DB_USERNAME] -p [DB_DATABASE]
```

### Step 2: Run the Script
```bash
# Option A: Run from file
mysql -h [DB_HOST] -u [DB_USERNAME] -p[DB_PASSWORD] [DB_DATABASE] < test_force_delete_user.sql

# Option B: Copy and paste into MySQL prompt
# Open the file, copy contents, paste into MySQL command line
```

### Step 3: Review Results
- Check for any errors
- If errors occur, the transaction will prevent changes
- If no errors, you can COMMIT (uncomment the COMMIT line)

## Method 2: Using phpMyAdmin (If Available)

1. **Login to phpMyAdmin**
   - Navigate to your production phpMyAdmin URL
   - Login with database credentials

2. **Select Database**
   - Click on your database name from the left sidebar

3. **Open SQL Tab**
   - Click on the "SQL" tab at the top

4. **Paste Script**
   - Open `test_force_delete_user.sql` in a text editor
   - Copy the entire contents
   - Paste into the SQL query box
   - **IMPORTANT**: Make sure `SET @USER_ID = 16;` has the correct user ID

5. **Execute**
   - Click "Go" button
   - Review the results
   - Check for any errors

6. **Commit or Rollback**
   - If no errors and you want to proceed, uncomment `COMMIT;` and run again
   - If errors occurred, the transaction will auto-rollback

## Method 3: Using MySQL Workbench / DBeaver / TablePlus

1. **Connect to Production Database**
   - Create a new connection using credentials from `.env`:
     - Host: `DB_HOST`
     - Port: `DB_PORT` (usually 3306)
     - Database: `DB_DATABASE`
     - Username: `DB_USERNAME`
     - Password: `DB_PASSWORD`

2. **Open SQL Editor**
   - Create a new SQL script/query window

3. **Load Script**
   - Open `test_force_delete_user.sql`
   - Copy and paste into the SQL editor
   - Update `SET @USER_ID = 16;` with correct user ID

4. **Execute**
   - Run the script (F5 or Execute button)
   - Review results in the output panel

5. **Commit or Rollback**
   - If successful, uncomment `COMMIT;` and run again
   - If errors, transaction will rollback automatically

## Method 4: Using Laravel Tinker (Alternative)

If you prefer using Laravel, you can create a command or use Tinker:

```bash
# SSH into production server
cd /path/to/your/laravel/app

# Run Tinker
php artisan tinker
```

Then manually execute the deletion logic (not recommended - use SQL script instead for safety).

## Method 5: Using cPanel / Hosting Panel

If your hosting provider has a database management tool:

1. **Login to cPanel/Hosting Panel**
2. **Find Database Tools**
   - Usually under "Databases" → "phpMyAdmin" or "MySQL Databases"
3. **Open SQL Interface**
   - Click on "SQL" or "Run SQL Query"
4. **Paste and Execute**
   - Copy script from `test_force_delete_user.sql`
   - Paste and execute
   - Review results

## Step-by-Step Execution Guide

### Before Running:

1. **Verify User ID**
   ```sql
   SELECT id, name, email, role, status FROM users WHERE id = 16;
   ```
   Make sure this is the user you want to delete!

2. **Check Dependencies**
   ```sql
   -- See what references this user
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
   ```

### Running the Script:

1. **Open the SQL file** (`test_force_delete_user.sql`)
2. **Update User ID** (line 5): `SET @USER_ID = 16;`
3. **Execute the script** using one of the methods above
4. **Review output** for any errors
5. **If NO errors**:
   - Uncomment line 95: `COMMIT;`
   - Comment line 92: `-- ROLLBACK;`
   - Run the script again to actually delete
6. **If ERRORS occur**:
   - The transaction will prevent changes
   - Fix the errors in the script
   - Re-run

### After Running:

1. **Verify Deletion**
   ```sql
   SELECT id, name, email FROM users WHERE id = 16;
   -- Should return empty result
   ```

2. **Check Application**
   - Try to access the user in the application
   - Should show "User not found" or similar

## Quick Reference: Database Credentials

Your database credentials are in `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Troubleshooting

### Error: "Table doesn't exist"
- Some tables might not exist in your database
- Comment out those DELETE statements for non-existent tables
- The script will continue with other operations

### Error: "Foreign key constraint fails"
- This means there's a relationship we didn't account for
- Check the constraint name in the error
- Add the missing DELETE/UPDATE statement to the script

### Error: "Access denied"
- Check database user has DELETE/UPDATE permissions
- Verify credentials are correct

### Script runs but user still exists
- Make sure you uncommented `COMMIT;` and commented `ROLLBACK;`
- The transaction prevents changes until COMMIT is executed

## Safety Checklist

- [ ] Database backup created
- [ ] User ID verified (correct user to delete)
- [ ] Script reviewed (all steps make sense)
- [ ] Tested in transaction first (ROLLBACK enabled)
- [ ] No errors in output
- [ ] Ready to commit changes
- [ ] Off-peak hours (if possible)
- [ ] Team notified (if required)

## Need Help?

If you encounter errors:
1. Copy the full error message
2. Check which step failed
3. Review the error for constraint/table names
4. Update the script to handle the missing relationship
5. Re-run with ROLLBACK enabled first

