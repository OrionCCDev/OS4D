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
        // Update the enum to include 'sent' status
        DB::statement("ALTER TABLE emails MODIFY COLUMN status ENUM('received', 'read', 'replied', 'archived', 'sent') DEFAULT 'received'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE emails MODIFY COLUMN status ENUM('received', 'read', 'replied', 'archived') DEFAULT 'received'");
    }
};
