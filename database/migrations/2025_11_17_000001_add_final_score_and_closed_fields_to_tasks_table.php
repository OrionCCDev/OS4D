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
        Schema::table('tasks', function (Blueprint $table) {
            // Store the final score when task is closed by admin
            $table->decimal('final_score', 8, 2)->nullable()->after('combined_approval_status');

            // Track who closed the task
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete()->after('final_score');

            // When the task was closed
            $table->timestamp('closed_at')->nullable()->after('closed_by');

            // Closure reason/notes
            $table->text('closure_notes')->nullable()->after('closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['final_score', 'closed_by', 'closed_at', 'closure_notes']);
        });
    }
};
