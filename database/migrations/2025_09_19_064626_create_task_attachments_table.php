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
        if (!Schema::hasTable('task_attachments')) {
            Schema::create('task_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->onDelete('cascade');
                $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->string('disk')->default('public');
                $table->string('path');
                $table->timestamps();

                $table->index(['task_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
    }
};
