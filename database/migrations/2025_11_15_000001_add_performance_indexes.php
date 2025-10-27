<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to tasks table for frequently queried columns
        Schema::table('tasks', function (Blueprint $table) {
            // Add indexes for common queries
            $table->index('status', 'tasks_status_idx');
            $table->index('priority', 'tasks_priority_idx');
            $table->index('assigned_to', 'tasks_assigned_to_idx');
            $table->index('due_date', 'tasks_due_date_idx');
            $table->index('created_at', 'tasks_created_at_idx');
            $table->index(['status', 'due_date'], 'tasks_status_due_date_idx');
            $table->index(['assigned_to', 'status'], 'tasks_assigned_status_idx');
            $table->index(['project_id', 'status'], 'tasks_project_status_idx');
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_idx');
            $table->index('status', 'users_status_idx');
        });

        // Add indexes to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->index('status', 'projects_status_idx');
            $table->index('owner_id', 'projects_owner_id_idx');
        });

        // Add indexes to notifications table
        Schema::table('unified_notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'unified_notifications_user_read_idx');
            $table->index(['type', 'is_read'], 'unified_notifications_type_read_idx');
            $table->index('created_at', 'unified_notifications_created_at_idx');
        });

        // Add index to custom_notifications table
        if (Schema::hasTable('custom_notifications')) {
            Schema::table('custom_notifications', function (Blueprint $table) {
                $table->index(['user_id', 'is_read'], 'custom_notifications_user_read_idx');
                $table->index('created_at', 'custom_notifications_created_at_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_status_idx');
            $table->dropIndex('tasks_priority_idx');
            $table->dropIndex('tasks_assigned_to_idx');
            $table->dropIndex('tasks_due_date_idx');
            $table->dropIndex('tasks_created_at_idx');
            $table->dropIndex('tasks_status_due_date_idx');
            $table->dropIndex('tasks_assigned_status_idx');
            $table->dropIndex('tasks_project_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_idx');
            $table->dropIndex('users_status_idx');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_status_idx');
            $table->dropIndex('projects_owner_id_idx');
        });

        Schema::table('unified_notifications', function (Blueprint $table) {
            $table->dropIndex('unified_notifications_user_read_idx');
            $table->dropIndex('unified_notifications_type_read_idx');
            $table->dropIndex('unified_notifications_created_at_idx');
        });

        if (Schema::hasTable('custom_notifications')) {
            Schema::table('custom_notifications', function (Blueprint $table) {
                $table->dropIndex('custom_notifications_user_read_idx');
                $table->dropIndex('custom_notifications_created_at_idx');
            });
        }
    }
};

