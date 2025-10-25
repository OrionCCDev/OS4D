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
        Schema::create('project_contractor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('contractor_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable(); // e.g., 'client', 'consultant', 'orion_staff'
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            // Ensure unique combination of project and contractor
            $table->unique(['project_id', 'contractor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_contractor');
    }
};
