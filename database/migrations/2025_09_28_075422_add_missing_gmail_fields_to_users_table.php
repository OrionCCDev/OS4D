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
        Schema::table('users', function (Blueprint $table) {
            // Add missing Gmail fields that the User model expects
            if (!Schema::hasColumn('users', 'gmail_connected')) {
                $table->boolean('gmail_connected')->default(false);
            }
            if (!Schema::hasColumn('users', 'gmail_connected_at')) {
                $table->timestamp('gmail_connected_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gmail_connected',
                'gmail_connected_at'
            ]);
        });
    }
};
