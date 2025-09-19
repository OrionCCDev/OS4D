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
        Schema::table('task_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('task_attachments', 'task_id')) {
                $table->foreignId('task_id')->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('task_attachments', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->after('task_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('task_attachments', 'original_name')) {
                $table->string('original_name')->after('uploaded_by');
            }
            if (!Schema::hasColumn('task_attachments', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('original_name');
            }
            if (!Schema::hasColumn('task_attachments', 'size_bytes')) {
                $table->unsignedBigInteger('size_bytes')->default(0)->after('mime_type');
            }
            if (!Schema::hasColumn('task_attachments', 'disk')) {
                $table->string('disk')->default('public')->after('size_bytes');
            }
            if (!Schema::hasColumn('task_attachments', 'path')) {
                $table->string('path')->after('disk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('task_attachments', 'task_id')) {
                $table->dropConstrainedForeignId('task_id');
            }
            if (Schema::hasColumn('task_attachments', 'uploaded_by')) {
                $table->dropConstrainedForeignId('uploaded_by');
            }
            foreach (['original_name','mime_type','size_bytes','disk','path'] as $col) {
                if (Schema::hasColumn('task_attachments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
