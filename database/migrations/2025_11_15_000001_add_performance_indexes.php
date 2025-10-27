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
        // Helper function to add index if it doesn't exist
        $addIndexIfNotExists = function($table, $indexName, $callback) use (&$addIndexIfNotExists) {
            try {
                $connection = Schema::getConnection();
                $sm = $connection->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes($table);
                $indexNames = array_keys($indexesFound);
                
                if (!in_array($indexName, $indexNames)) {
                    Schema::table($table, $callback);
                }
            } catch (\Exception $e) {
                // Index might exist, continue
            }
        };

        // Add indexes to tasks table for frequently queried columns
        $addIndexIfNotExists('tasks', 'tasks_status_idx', function(Blueprint $table) {
            $table->index('status', 'tasks_status_idx');
        });
        
        $addIndexIfNotExists('tasks', 'tasks_priority_idx', function(Blueprint $table) {
            $table->index('priority', 'tasks_priority_idx');
        });
        
        $addIndexIfNotExists('tasks', 'tasks_assigned_to_idx', function(Blueprint $table) {
            $table->index('assigned_to', 'tasks_assigned_to_idx');
        });
        
        $addIndexIfNotExists('tasks', 'tasks_due_date_idx', function(Blueprint $table) {
            $table->index('due_date', 'tasks_due_date_idx');
        });
        
        $addIndexIfNotExists('tasks', 'tasks_created_at_idx', function(Blueprint $table) {
            $table->index('created_at', 'tasks_created_at_idx');
        });
        
        $addIndexIfNotExists('tasks', 'tasks_status_due_date_idx', function(Blueprint $table) {
            $table->index(['status', 'due_date'], 'tasks_status_due_date_idx');
        });
        
        $addIndexIfNotExists('tasks', 'tasks_assigned_status_idx', function(Blueprint $table) {
            $table->index(['assigned_to', 'status'], 'tasks_assigned_status_idx');
        });
        
        $addIndexIfNotExists('tasks', 'tasks_project_status_idx', function(Blueprint $table) {
            $table->index(['project_id', 'status'], 'tasks_project_status_idx');
        });

        // Add indexes to users table
        $addIndexIfNotExists('users', 'users_role_idx', function(Blueprint $table) {
            $table->index('role', 'users_role_idx');
        });
        
        $addIndexIfNotExists('users', 'users_status_idx', function(Blueprint $table) {
            $table->index('status', 'users_status_idx');
        });

        // Add indexes to projects table
        $addIndexIfNotExists('projects', 'projects_status_idx', function(Blueprint $table) {
            $table->index('status', 'projects_status_idx');
        });
        
        $addIndexIfNotExists('projects', 'projects_owner_id_idx', function(Blueprint $table) {
            $table->index('owner_id', 'projects_owner_id_idx');
        });

        // Add indexes to notifications table
        $addIndexIfNotExists('unified_notifications', 'unified_notifications_user_read_idx', function(Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'unified_notifications_user_read_idx');
        });
        
        $addIndexIfNotExists('unified_notifications', 'unified_notifications_type_read_idx', function(Blueprint $table) {
            $table->index(['type', 'is_read'], 'unified_notifications_type_read_idx');
        });
        
        $addIndexIfNotExists('unified_notifications', 'unified_notifications_created_at_idx', function(Blueprint $table) {
            $table->index('created_at', 'unified_notifications_created_at_idx');
        });

        // Add index to custom_notifications table - Skip if table doesn't exist or column doesn't exist
        // Note: This table already has indexes in its migration, so we might skip adding them
        try {
            if (Schema::hasTable('custom_notifications') && Schema::hasColumn('custom_notifications', 'read')) {
                Schema::table('custom_notifications', function (Blueprint $table) {
                    // Use raw SQL to check if index exists before creating
                    $table->index(['user_id', 'read'], 'custom_notifications_user_read_idx');
                });
            }
        } catch (\Exception $e) {
            // Index might already exist, continue
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

