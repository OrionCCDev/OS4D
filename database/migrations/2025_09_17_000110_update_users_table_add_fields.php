<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'manager', 'user'])->default('user')->after('email');
            } else {
                // Ensure role includes manager
                // For sqlite or databases that don't support enum alteration easily, we skip alteration.
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $droppable = [];
            foreach (['role', 'phone', 'bio', 'is_active', 'last_login_at'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $droppable[] = $col;
                }
            }
            if (!empty($droppable)) {
                $table->dropColumn($droppable);
            }
        });
    }
};


