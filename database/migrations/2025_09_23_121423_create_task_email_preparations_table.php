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
        Schema::create('task_email_preparations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('prepared_by')->constrained('users')->cascadeOnDelete();
            $table->string('to_emails')->nullable(); // Comma-separated email addresses
            $table->string('cc_emails')->nullable(); // Comma-separated email addresses
            $table->string('bcc_emails')->nullable(); // Comma-separated email addresses
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->json('attachments')->nullable(); // Store attachment file paths
            $table->enum('status', ['draft', 'sent'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_email_preparations');
    }
};
