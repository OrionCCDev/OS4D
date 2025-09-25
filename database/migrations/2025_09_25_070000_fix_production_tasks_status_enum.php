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
        // First, let's check what the current enum values are
        $currentEnum = DB::select("SHOW COLUMNS FROM tasks WHERE Field = 'status'")[0]->Type;

        // Check if ready_for_email is already in the enum
        if (strpos($currentEnum, 'ready_for_email') === false) {
            // Add ready_for_email to the enum
            DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending','assigned','in_progress','submitted_for_review','in_review','approved','ready_for_email','rejected','completed') NOT NULL DEFAULT 'pending'");

            // Log the change
            \Log::info('Updated tasks status enum to include ready_for_email');
        } else {
            \Log::info('ready_for_email already exists in tasks status enum');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove ready_for_email from the enum (be careful with this in production)
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending','assigned','in_progress','submitted_for_review','in_review','approved','rejected','completed') NOT NULL DEFAULT 'pending'");

        \Log::info('Removed ready_for_email from tasks status enum');
    }
};
