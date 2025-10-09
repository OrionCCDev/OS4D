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
        // Check if column already exists before adding
        if (!Schema::hasColumn('emails', 'cc')) {
            Schema::table('emails', function (Blueprint $table) {
                $table->text('cc')->nullable()->after('to_email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('emails', 'cc')) {
            Schema::table('emails', function (Blueprint $table) {
                $table->dropColumn('cc');
            });
        }
    }
};

