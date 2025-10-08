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
            // Client approval fields
            $table->enum('client_status', ['not_attached', 'approved', 'rejected'])->default('not_attached')->after('rejection_notes');
            $table->text('client_notes')->nullable()->after('client_status');
            $table->timestamp('client_updated_at')->nullable()->after('client_notes');

            // Consultant approval fields
            $table->enum('consultant_status', ['not_attached', 'approved', 'rejected'])->default('not_attached')->after('client_updated_at');
            $table->text('consultant_notes')->nullable()->after('consultant_status');
            $table->timestamp('consultant_updated_at')->nullable()->after('consultant_notes');

            // Combined status for tracking
            $table->string('combined_approval_status')->nullable()->after('consultant_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'client_status',
                'client_notes',
                'client_updated_at',
                'consultant_status',
                'consultant_notes',
                'consultant_updated_at',
                'combined_approval_status'
            ]);
        });
    }
};

