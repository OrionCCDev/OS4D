<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Support\Facades\Log;

class RequiredFileAttachmentService
{
    /**
     * Get all required files for a task that should be automatically attached to emails
     */
    public function getRequiredFilesForTask(Task $task): array
    {
        $requiredFiles = [];

        // Get only task attachments marked as required for email
        $requiredTaskAttachments = $task->requiredAttachments;

        if ($requiredTaskAttachments && $requiredTaskAttachments->count() > 0) {
            Log::info('RequiredFileAttachmentService: Found ' . $requiredTaskAttachments->count() . ' required task attachments for task ' . $task->id);

            foreach ($requiredTaskAttachments as $attachment) {
                $fullPath = storage_path('app/public/' . $attachment->path);

                if (file_exists($fullPath)) {
                    $requiredFiles[] = [
                        'id' => $attachment->id,
                        'original_name' => $attachment->original_name,
                        'path' => $attachment->path,
                        'full_path' => $fullPath,
                        'mime_type' => $attachment->mime_type,
                        'size_bytes' => $attachment->size_bytes,
                        'uploaded_by' => $attachment->uploaded_by,
                        'created_at' => $attachment->created_at,
                        'required_for_email' => $attachment->required_for_email,
                        'required_notes' => $attachment->required_notes,
                        'type' => 'required_task_attachment'
                    ];
                } else {
                    Log::warning('RequiredFileAttachmentService: Required task attachment file not found: ' . $fullPath);
                }
            }
        }

        return $requiredFiles;
    }

    /**
     * Check if a task has any required files
     */
    public function hasRequiredFiles(Task $task): bool
    {
        return $task->requiredAttachments && $task->requiredAttachments->count() > 0;
    }

    /**
     * Get required files count for a task
     */
    public function getRequiredFilesCount(Task $task): int
    {
        return $task->requiredAttachments ? $task->requiredAttachments->count() : 0;
    }

    /**
     * Validate required files before email sending
     */
    public function validateRequiredFiles(Task $task): array
    {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'files_count' => 0
        ];

        $requiredTaskAttachments = $task->requiredAttachments;

        if ($requiredTaskAttachments && $requiredTaskAttachments->count() > 0) {
            $validation['files_count'] = $requiredTaskAttachments->count();

            foreach ($requiredTaskAttachments as $attachment) {
                $fullPath = storage_path('app/public/' . $attachment->path);

                if (!file_exists($fullPath)) {
                    $validation['valid'] = false;
                    $validation['errors'][] = "Required file not found: {$attachment->original_name}";
                } else {
                    $fileSize = filesize($fullPath);
                    $maxSize = 100 * 1024 * 1024; // 100MB limit

                    if ($fileSize > $maxSize) {
                        $validation['valid'] = false;
                        $validation['errors'][] = "Required file too large: {$attachment->original_name} ({$fileSize} bytes)";
                    }
                }
            }
        }

        return $validation;
    }

    /**
     * Get attachment data for email sending
     */
    public function getAttachmentDataForEmail(Task $task): array
    {
        $attachments = [];

        $requiredTaskAttachments = $task->requiredAttachments;

        if ($requiredTaskAttachments && $requiredTaskAttachments->count() > 0) {
            foreach ($requiredTaskAttachments as $attachment) {
                $fullPath = storage_path('app/public/' . $attachment->path);

                if (file_exists($fullPath)) {
                    $fileSize = filesize($fullPath);
                    $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

                    $attachments[] = [
                        'filename' => $attachment->original_name,
                        'mime_type' => $mimeType,
                        'content' => file_get_contents($fullPath),
                        'size' => $fileSize,
                        'type' => 'required_file',
                        'required_notes' => $attachment->required_notes
                    ];
                }
            }
        }

        return $attachments;
    }

    /**
     * Log required files information for debugging
     */
    public function logRequiredFilesInfo(Task $task): void
    {
        $requiredTaskAttachments = $task->requiredAttachments;

        if ($requiredTaskAttachments && $requiredTaskAttachments->count() > 0) {
            Log::info('RequiredFileAttachmentService: Task ' . $task->id . ' has ' . $requiredTaskAttachments->count() . ' required files:');

            foreach ($requiredTaskAttachments as $attachment) {
                $fullPath = storage_path('app/public/' . $attachment->path);
                $exists = file_exists($fullPath);
                $size = $exists ? filesize($fullPath) : 0;

                Log::info('RequiredFileAttachmentService: - ' . $attachment->original_name .
                         ' (Path: ' . $attachment->path . ', Exists: ' . ($exists ? 'Yes' : 'No') .
                         ', Size: ' . $size . ' bytes, Notes: ' . ($attachment->required_notes ?? 'None') . ')');
            }
        } else {
            Log::info('RequiredFileAttachmentService: Task ' . $task->id . ' has no required files');
        }
    }
}
