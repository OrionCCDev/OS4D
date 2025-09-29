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
            // Add email tracking fields
            $table->string('gmail_message_id')->nullable()->after('message_id');
            $table->string('thread_id')->nullable()->after('gmail_message_id');
            $table->enum('email_type', ['sent', 'received'])->default('received')->after('status');
            $table->timestamp('sent_at')->nullable()->after('received_at');
            $table->timestamp('delivered_at')->nullable()->after('sent_at');
            $table->timestamp('opened_at')->nullable()->after('delivered_at');
            $table->timestamp('replied_at')->nullable()->after('opened_at');
            $table->json('cc_emails')->nullable()->after('to_email');
            $table->json('bcc_emails')->nullable()->after('cc_emails');
            $table->string('tracking_pixel_url')->nullable()->after('replied_at');
            $table->boolean('is_tracked')->default(false)->after('tracking_pixel_url');
            $table->unsignedBigInteger('user_id')->nullable()->after('task_id');

            // Add foreign key for user
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Add indexes for better performance
            $table->index(['gmail_message_id']);
            $table->index(['thread_id']);
            $table->index(['email_type', 'sent_at']);
            $table->index(['user_id', 'email_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropIndex(['gmail_message_id']);
            $table->dropIndex(['thread_id']);
            $table->dropIndex(['email_type', 'sent_at']);
            $table->dropIndex(['user_id', 'email_type']);

            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'gmail_message_id',
                'thread_id',
                'email_type',
                'sent_at',
                'delivered_at',
                'opened_at',
                'replied_at',
                'cc_emails',
                'bcc_emails',
                'tracking_pixel_url',
                'is_tracked',
                'user_id'
            ]);
        });
    }
};
