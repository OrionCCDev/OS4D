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
        // Check if tasks table exists, if not try 'task' table
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('due_date');
            });
        } elseif (Schema::hasTable('task')) {
            Schema::table('task', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('due_date');
            });
        } else {
            throw new \Exception('Neither tasks nor task table found. Please check your database structure.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if tasks table exists, if not try 'task' table
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('start_date');
            });
        } elseif (Schema::hasTable('task')) {
            Schema::table('task', function (Blueprint $table) {
                $table->dropColumn('start_date');
            });
        }
    }
};
