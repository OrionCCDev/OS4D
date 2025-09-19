<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->timestamps();
            $table->index(['task_id', 'user_id']);
            $table->index(['user_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_tracking');
    }
};


