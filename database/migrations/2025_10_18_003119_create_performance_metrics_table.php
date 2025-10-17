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
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->date('metric_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->integer('tasks_assigned')->default(0);
            $table->integer('tasks_completed')->default(0);
            $table->integer('tasks_on_time')->default(0);
            $table->integer('tasks_early')->default(0);
            $table->integer('tasks_overdue')->default(0);
            $table->integer('tasks_rejected')->default(0);
            $table->integer('high_priority_tasks')->default(0);
            $table->integer('medium_priority_tasks')->default(0);
            $table->integer('low_priority_tasks')->default(0);
            $table->decimal('average_completion_time', 8, 2)->nullable(); // in hours
            $table->decimal('efficiency_score', 5, 2)->default(0);
            $table->decimal('quality_score', 5, 2)->default(0);
            $table->decimal('punctuality_score', 5, 2)->default(0);
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->integer('rank')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'metric_date', 'period_type'], 'perf_metric_user_date_type_idx');
            $table->index(['project_id', 'metric_date', 'period_type'], 'perf_metric_proj_date_type_idx');
            $table->index(['period_type', 'metric_date'], 'perf_metric_type_date_idx');
            $table->unique(['user_id', 'project_id', 'metric_date', 'period_type'], 'perf_metric_user_proj_date_type_unq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
