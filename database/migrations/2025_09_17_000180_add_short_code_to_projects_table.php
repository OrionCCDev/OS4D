<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'short_code')) {
                $table->string('short_code', 12)->nullable()->after('name');
                $table->index('short_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'short_code')) {
                $table->dropIndex(['short_code']);
                $table->dropColumn('short_code');
            }
        });
    }
};


