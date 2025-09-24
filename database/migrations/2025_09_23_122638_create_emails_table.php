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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('from_email');
            $table->string('to_email');
            $table->string('subject');
            $table->text('body');
            $table->timestamp('received_at');
            $table->enum('status', ['received', 'read', 'replied', 'archived'])->default('received');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->json('attachments')->nullable();
            $table->string('message_id')->nullable();
            $table->unsignedBigInteger('reply_to_email_id')->nullable();
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            $table->foreign('reply_to_email_id')->references('id')->on('emails')->onDelete('set null');
            $table->index(['from_email', 'received_at']);
            $table->index(['task_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
