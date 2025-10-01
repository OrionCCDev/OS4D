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
        Schema::create('email_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email_source')->default('designers_inbox');
            $table->timestamp('last_fetch_at')->nullable();
            $table->string('last_message_id')->nullable();
            $table->integer('last_message_count')->default(0);
            $table->integer('total_fetched')->default(0);
            $table->integer('total_stored')->default(0);
            $table->integer('total_skipped')->default(0);
            $table->json('last_errors')->nullable();
            $table->timestamps();

            $table->index(['email_source']);
            $table->index(['last_fetch_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_fetch_logs');
    }
};
