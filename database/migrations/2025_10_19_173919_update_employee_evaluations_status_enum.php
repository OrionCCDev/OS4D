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
        // Update the status enum to include 'completed'
        DB::statement("ALTER TABLE employee_evaluations MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'completed') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the status enum to original values
        DB::statement("ALTER TABLE employee_evaluations MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft'");
    }
};
