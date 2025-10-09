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
        // Add 'processing' and 'failed' to the status enum for task_email_preparations
        DB::statement("ALTER TABLE task_email_preparations MODIFY COLUMN status ENUM('draft', 'processing', 'sent', 'failed') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE task_email_preparations MODIFY COLUMN status ENUM('draft', 'sent') NOT NULL DEFAULT 'draft'");
    }
};

