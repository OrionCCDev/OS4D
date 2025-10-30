<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include 'client' and 'consultant' roles
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'sub-admin', 'user', 'client', 'consultant') NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the previous enum values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'sub-admin', 'user') NOT NULL DEFAULT 'user'");
    }
};
