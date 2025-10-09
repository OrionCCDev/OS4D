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
        // Add 'on_client_consultant_review' to the status enum for tasks
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending','assigned','in_progress','submitted_for_review','in_review','approved','ready_for_email','on_client_consultant_review','rejected','completed') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum values
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending','assigned','in_progress','submitted_for_review','in_review','approved','ready_for_email','rejected','completed') NOT NULL DEFAULT 'pending'");
    }
};

