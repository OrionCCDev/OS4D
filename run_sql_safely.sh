#!/bin/bash

# Safe SQL Execution Script for Production
# This script helps you run the SQL test script safely

echo "=========================================="
echo "Force Delete User - SQL Execution Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${RED}Error: .env file not found!${NC}"
    exit 1
fi

# Read database credentials from .env
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_PORT=$(grep "^DB_PORT=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" || echo "3306")
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")

echo "Database Configuration:"
echo "  Host: $DB_HOST"
echo "  Port: $DB_PORT"
echo "  Database: $DB_DATABASE"
echo "  Username: $DB_USERNAME"
echo ""

# Prompt for user ID
read -p "Enter User ID to delete: " USER_ID

if [ -z "$USER_ID" ]; then
    echo -e "${RED}Error: User ID is required!${NC}"
    exit 1
fi

# Confirm
echo ""
echo -e "${YELLOW}WARNING: You are about to delete user ID: $USER_ID${NC}"
read -p "Are you sure? (type 'yes' to continue): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Cancelled."
    exit 0
fi

# Create temporary SQL file with user ID
TEMP_SQL=$(mktemp)
sed "s/SET @USER_ID = 16;/SET @USER_ID = $USER_ID;/" test_force_delete_user.sql > "$TEMP_SQL"

echo ""
echo "=========================================="
echo "Running SQL Script (with ROLLBACK)..."
echo "=========================================="
echo ""

# Run SQL script
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < "$TEMP_SQL"

SQL_EXIT_CODE=$?

# Clean up temp file
rm "$TEMP_SQL"

if [ $SQL_EXIT_CODE -eq 0 ]; then
    echo ""
    echo -e "${GREEN}SQL script executed successfully!${NC}"
    echo ""
    echo "Review the output above for any errors."
    echo ""
    read -p "Did the script run without errors? (yes/no): " NO_ERRORS
    
    if [ "$NO_ERRORS" = "yes" ]; then
        echo ""
        echo -e "${YELLOW}To actually delete the user, you need to:${NC}"
        echo "1. Edit test_force_delete_user.sql"
        echo "2. Comment out: -- ROLLBACK;"
        echo "3. Uncomment: COMMIT;"
        echo "4. Run this script again"
    else
        echo ""
        echo -e "${RED}Please fix the errors and try again.${NC}"
        echo "The transaction was rolled back, so no changes were made."
    fi
else
    echo ""
    echo -e "${RED}Error: SQL script failed with exit code $SQL_EXIT_CODE${NC}"
    echo "Please check the error messages above."
fi

