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
            // Email provider settings
            $table->string('email_provider')->nullable()->after('email'); // gmail, outlook, yahoo, custom
            $table->string('email_smtp_host')->nullable()->after('email_provider');
            $table->integer('email_smtp_port')->nullable()->after('email_smtp_host');
            $table->string('email_smtp_username')->nullable()->after('email_smtp_port');
            $table->text('email_smtp_password')->nullable()->after('email_smtp_username'); // encrypted
            $table->string('email_smtp_encryption')->nullable()->after('email_smtp_password'); // tls, ssl, none
            $table->boolean('email_credentials_configured')->default(false)->after('email_smtp_encryption');
            $table->timestamp('email_credentials_updated_at')->nullable()->after('email_credentials_configured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_provider',
                'email_smtp_host',
                'email_smtp_port',
                'email_smtp_username',
                'email_smtp_password',
                'email_smtp_encryption',
                'email_credentials_configured',
                'email_credentials_updated_at'
            ]);
        });
    }
};
