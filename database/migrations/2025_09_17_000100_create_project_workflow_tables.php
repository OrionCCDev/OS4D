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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'on_hold', 'completed', 'cancelled'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('role', ['manager', 'member', 'viewer'])->default('member');
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('project_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('project_folders')->cascadeOnUpdate()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['project_id', 'parent_id']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('project_folders')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'in_review', 'approved', 'rejected', 'done'])->default('todo');
            $table->unsignedTinyInteger('priority')->default(3); // 1-high, 5-low
            $table->timestamps();
            $table->softDeletes();
            $table->index(['project_id', 'folder_id']);
        });

        Schema::create('task_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'user_id']);
        });

        Schema::create('task_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('from_status', ['todo', 'in_progress', 'in_review', 'approved', 'rejected', 'done'])->nullable();
            $table->enum('to_status', ['todo', 'in_progress', 'in_review', 'approved', 'rejected', 'done']);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['task_id', 'to_status']);
        });

        Schema::create('task_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('decision', ['approved', 'rejected']);
            $table->text('comment')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();
            $table->unique(['task_id', 'reviewer_id']);
        });

        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamps();
            $table->index(['task_id', 'uploaded_by']);
        });

        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('contractor_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('contractor_id')->constrained('contractors')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sent_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->timestamp('sent_at');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index(['task_id', 'contractor_id']);
        });

        Schema::create('contractor_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_email_id')->constrained('contractor_emails')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('received_at');
            $table->text('message')->nullable();
            $table->string('from_email')->nullable();
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('scope', ['project', 'task', 'user_task']);
            $table->unsignedBigInteger('scope_id');
            $table->string('title');
            $table->json('data');
            $table->timestamps();
            $table->index(['scope', 'scope_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('contractor_replies');
        Schema::dropIfExists('contractor_emails');
        Schema::dropIfExists('contractors');
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_approvals');
        Schema::dropIfExists('task_status_changes');
        Schema::dropIfExists('task_assignees');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('project_folders');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};


