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
            // Add client/consultant response tracking fields
            $table->enum('client_response_status', ['pending', 'approved', 'rejected'])->default('pending')->after('internal_approved_by');
            $table->text('client_response_notes')->nullable()->after('client_response_status');
            $table->timestamp('client_response_updated_at')->nullable()->after('client_response_notes');

            $table->enum('consultant_response_status', ['pending', 'approved', 'rejected'])->default('pending')->after('client_response_updated_at');
            $table->text('consultant_response_notes')->nullable()->after('consultant_response_status');
            $table->timestamp('consultant_response_updated_at')->nullable()->after('consultant_response_notes');

            // Combined response status (e.g., "client-reject-consultant-approve")
            $table->string('combined_response_status')->nullable()->after('consultant_response_updated_at');

            // Manager override fields
            $table->enum('manager_override_status', ['none', 'reject', 'reset_for_review'])->default('none')->after('combined_response_status');
            $table->text('manager_override_notes')->nullable()->after('manager_override_status');
            $table->timestamp('manager_override_updated_at')->nullable()->after('manager_override_notes');
            $table->unsignedBigInteger('manager_override_by')->nullable()->after('manager_override_updated_at');

            // Add foreign key constraint
            $table->foreign('manager_override_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['manager_override_by']);
            $table->dropColumn([
                'client_response_status',
                'client_response_notes',
                'client_response_updated_at',
                'consultant_response_status',
                'consultant_response_notes',
                'consultant_response_updated_at',
                'combined_response_status',
                'manager_override_status',
                'manager_override_notes',
                'manager_override_updated_at',
                'manager_override_by'
            ]);
        });
    }
};
