# Fix Script Permissions

## Issue
Getting "Permission denied" when trying to run `run_sql_safely.sh`

## Solution

Run this command to make the script executable:

```bash
chmod +x run_sql_safely.sh
```

Then run it again:

```bash
./run_sql_safely.sh
```

## Alternative: Run without execute permission

If you can't change permissions, you can run it directly with bash:

```bash
bash run_sql_safely.sh
```

## Quick Commands

```bash
# Make executable
chmod +x run_sql_safely.sh

# Run the script
./run_sql_safely.sh

# OR run directly with bash (if permissions can't be changed)
bash run_sql_safely.sh
```

