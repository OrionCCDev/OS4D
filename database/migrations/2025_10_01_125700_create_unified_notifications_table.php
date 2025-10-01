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
        Schema::create('unified_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Notification category: 'task' or 'email'
            $table->enum('category', ['task', 'email'])->index();

            // Specific notification type within category
            $table->string('type')->index(); // task_assigned, email_reply, etc.

            // Notification content
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data

            // Related models (nullable)
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('email_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');

            // Read status
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();

            // Priority level
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->index();

            // Notification status
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active')->index();

            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'category', 'is_read']);
            $table->index(['category', 'type', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unified_notifications');
    }
};
