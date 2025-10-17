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
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['project', 'task', 'user', 'evaluation', 'custom']);
            $table->json('filters')->nullable(); // Store filter configuration
            $table->json('columns')->nullable(); // Store column configuration
            $table->json('settings')->nullable(); // Store additional settings
            $table->boolean('is_default')->default(false);
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['type', 'is_public']);
            $table->index(['created_by', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
