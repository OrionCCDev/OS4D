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
            // Add internal approval fields
            $table->enum('internal_status', ['pending', 'approved', 'rejected'])->default('pending')->after('consultant_updated_at');
            $table->text('internal_notes')->nullable()->after('internal_status');
            $table->timestamp('internal_updated_at')->nullable()->after('internal_notes');
            $table->unsignedBigInteger('internal_approved_by')->nullable()->after('internal_updated_at');

            // Add foreign key constraint
            $table->foreign('internal_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['internal_approved_by']);
            $table->dropColumn(['internal_status', 'internal_notes', 'internal_updated_at', 'internal_approved_by']);
        });
    }
};
