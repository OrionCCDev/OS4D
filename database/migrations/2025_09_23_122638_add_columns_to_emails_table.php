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
        Schema::table('emails', function (Blueprint $table) {
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
        Schema::table('emails', function (Blueprint $table) {
            $table->dropIndex(['from_email', 'received_at']);
            $table->dropIndex(['task_id', 'status']);
            $table->dropForeign(['task_id']);
            $table->dropForeign(['reply_to_email_id']);
            $table->dropColumn([
                'from_email',
                'to_email', 
                'subject',
                'body',
                'received_at',
                'status',
                'task_id',
                'attachments',
                'message_id',
                'reply_to_email_id'
            ]);
        });
    }
};
