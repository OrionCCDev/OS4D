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
            $table->text('gmail_token')->nullable();
            $table->string('gmail_refresh_token')->nullable();
            $table->boolean('gmail_connected')->default(false);
            $table->timestamp('gmail_connected_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gmail_token',
                'gmail_refresh_token',
                'gmail_connected',
                'gmail_connected_at'
            ]);
        });
    }
};
