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
        Schema::table('contractors', function (Blueprint $table) {
            // Add 'mobile' column (keeping 'phone' for backward compatibility)
            if (!Schema::hasColumn('contractors', 'mobile')) {
                $table->string('mobile')->nullable();
            }

            // Add 'position' column
            if (!Schema::hasColumn('contractors', 'position')) {
                $table->string('position')->nullable();
            }

            // Add 'company_name' column (keeping 'company' for backward compatibility)
            if (!Schema::hasColumn('contractors', 'company_name')) {
                $table->string('company_name')->nullable();
            }

            // Add 'type' enum column
            if (!Schema::hasColumn('contractors', 'type')) {
                $table->enum('type', ['client', 'consultant'])->default('client');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropColumn(['mobile', 'position', 'company_name', 'type']);
        });
    }
};

