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
        Schema::create('employee_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluated_by')->constrained('users')->cascadeOnDelete();
            $table->enum('evaluation_type', ['monthly', 'quarterly', 'annual']);
            $table->date('evaluation_period_start');
            $table->date('evaluation_period_end');
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->integer('tasks_completed')->default(0);
            $table->decimal('on_time_completion_rate', 5, 2)->default(0);
            $table->decimal('quality_score', 5, 2)->default(0);
            $table->integer('early_completions')->default(0);
            $table->integer('overdue_tasks')->default(0);
            $table->integer('rejected_tasks')->default(0);
            $table->integer('rank')->nullable();
            $table->text('manager_notes')->nullable();
            $table->text('employee_notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'evaluation_type', 'evaluation_period_start'], 'emp_eval_user_type_period_idx');
            $table->index(['evaluation_type', 'evaluation_period_start'], 'emp_eval_type_period_idx');
            $table->unique(['user_id', 'evaluation_type', 'evaluation_period_start'], 'emp_eval_user_type_period_unq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_evaluations');
    }
};
