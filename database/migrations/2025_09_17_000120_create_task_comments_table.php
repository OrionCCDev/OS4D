<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('comment');
            $table->foreignId('parent_id')->nullable()->constrained('task_comments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
            $table->index(['task_id', 'created_at']);
            $table->index('user_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_comments');
    }
};


