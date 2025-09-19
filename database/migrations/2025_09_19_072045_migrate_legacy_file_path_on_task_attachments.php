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
        Schema::table('task_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('task_attachments', 'path')) {
                $table->string('path')->nullable();
            }
        });

        // Copy file_path -> path if legacy column exists
        if (Schema::hasColumn('task_attachments', 'file_path')) {
            DB::table('task_attachments')->whereNull('path')->update([
                'path' => DB::raw('file_path')
            ]);
            // Make path not nullable afterwards
            Schema::table('task_attachments', function (Blueprint $table) {
                $table->string('path')->nullable(false)->change();
            });
            // Drop legacy column
            Schema::table('task_attachments', function (Blueprint $table) {
                $table->dropColumn('file_path');
            });
        } else {
            // Ensure path is not nullable
            Schema::table('task_attachments', function (Blueprint $table) {
                $table->string('path')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Can't restore file_path values reliably; only recreate the column
        if (!Schema::hasColumn('task_attachments', 'file_path')) {
            Schema::table('task_attachments', function (Blueprint $table) {
                $table->string('file_path')->nullable();
            });
        }
    }
};
