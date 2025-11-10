<?php

namespace App\Services;

use App\Models\Contractor;
use App\Models\DeleteRequest;
use App\Models\EmailTemplate;
use App\Models\ExternalStakeholder;
use App\Models\Project;
use App\Models\ProjectFolder;
use App\Models\ProjectFolderFile;
use App\Models\ProjectManager;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeleteRequestService
{
    /**
     * Resolve target instance for a given request.
     */
    public function resolveTarget(DeleteRequest $request)
    {
        return match ($request->target_type) {
            'user' => User::find($request->target_id),
            'project' => Project::with(['tasks', 'folders', 'users'])->find($request->target_id),
            'project_folder' => ProjectFolder::with(['tasks', 'children', 'project'])->find($request->target_id),
            'project_file' => ProjectFolderFile::with(['project', 'folder'])->find($request->target_id),
            'contractor' => Contractor::withCount('projects')->find($request->target_id),
            'project_manager' => ProjectManager::withCount('projects')->find($request->target_id),
            'external_stakeholder' => ExternalStakeholder::find($request->target_id),
            'email_template' => EmailTemplate::find($request->target_id),
            'task' => Task::with(['project', 'folder', 'assignee'])->find($request->target_id),
            'queue_job' => $request->target_id,
            default => null,
        };
    }

    /**
     * Build a human readable summary of the delete impact.
     */
    public function buildEffectSummary(DeleteRequest $request): string
    {
        $target = $this->resolveTarget($request);

        if (!$target) {
            return 'Target no longer exists. Approving will simply mark this request as completed.';
        }

        return match ($request->target_type) {
            'user' => sprintf(
                "Deleting user \"%s\" will remove their profile, image, and detach them from tasks/projects.",
                $target->name
            ),
            'project' => sprintf(
                "Deleting project \"%s\" will remove %d tasks, %d folders, and detach %d team members. Files stored under the project directory will also be removed.",
                $target->name,
                $target->tasks()->count(),
                $target->folders()->count(),
                $target->users()->count()
            ),
            'project_folder' => sprintf(
                "Deleting folder \"%s\" will remove all subfolders and tasks inside it.",
                $target->name
            ),
            'project_file' => sprintf(
                "Deleting file \"%s\" will remove it permanently from storage.",
                $target->display_name ?? $target->original_name
            ),
            'contractor' => sprintf(
                "Deleting contractor \"%s\" will detach them from %d projects.",
                $target->name,
                $target->projects()->count()
            ),
            'project_manager' => sprintf(
                "Deleting project manager \"%s\" will detach them from %d projects.",
                $target->name,
                $target->projects()->count()
            ),
            'external_stakeholder' => sprintf(
                "Deleting stakeholder \"%s\" will remove their contact details.",
                $target->name
            ),
            'email_template' => sprintf(
                "Deleting email template \"%s\" will remove it from the template library.",
                $target->name
            ),
            'task' => sprintf(
                "Deleting task \"%s\" will remove its attachments, history, and notifications.",
                $target->title
            ),
            'queue_job' => 'Deleting this queue job will remove it from the failed jobs table.',
            default => 'Approving this request will delete the selected item.',
        };
    }

    /**
     * Execute the delete operation for the request.
     *
     * @throws \Throwable
     */
    public function execute(DeleteRequest $request): void
    {
        $target = $this->resolveTarget($request);

        if (!$target) {
            Log::warning('DeleteRequest target missing, marking as completed', [
                'request_id' => $request->id,
                'target_type' => $request->target_type,
                'target_id' => $request->target_id,
            ]);
            return;
        }

        switch ($request->target_type) {
            case 'user':
                $this->deleteUser($target);
                break;
            case 'project':
                $this->deleteProject($target);
                break;
            case 'project_folder':
                $this->deleteProjectFolder($target);
                break;
            case 'project_file':
                $this->deleteProjectFile($target);
                break;
            case 'contractor':
                $target->delete();
                break;
            case 'project_manager':
                if ($target->projects()->count() > 0) {
                    throw new RuntimeException('Cannot delete project manager with assigned projects.');
                }
                $target->delete();
                break;
            case 'external_stakeholder':
                $target->delete();
                break;
            case 'email_template':
                $target->delete();
                break;
            case 'task':
                $target->delete();
                break;
            case 'queue_job':
                Artisan::call('queue:forget', ['id' => $request->target_id]);
                break;
            default:
                throw new RuntimeException('Unsupported delete request type: ' . $request->target_type);
        }
    }

    protected function deleteUser(User $user): void
    {
        if ($user->img && !in_array($user->img, ['default.png', 'default.jpg', '1.png', 'default_user.jpg', 'default-user.jpg'])) {
            $oldPath = public_path('uploads/users/' . $user->img);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $user->delete();
    }

    protected function deleteProject(Project $project): void
    {
        DB::transaction(function () use ($project) {
            $tasksCount = $project->tasks()->count();
            $foldersCount = $project->folders()->count();
            $teamMembersCount = $project->users()->count();

            $project->tasks()->delete();
            $project->folders()->delete();
            $project->users()->detach();

            $this->deleteProjectDirectory($project);

            $project->delete();

            Log::info('Project deleted via DeleteRequest', [
                'project_id' => $project->id,
                'tasks_deleted' => $tasksCount,
                'folders_deleted' => $foldersCount,
                'team_detached' => $teamMembersCount,
            ]);
        });
    }

    protected function deleteProjectFolder(ProjectFolder $folder): void
    {
        $projectId = $folder->project_id;
        $parentId = $folder->parent_id;

        try {
            $path = $this->buildFolderPath($folder);
            $fullPath = public_path($path);
            if (file_exists($fullPath)) {
                $this->deleteDirectory($fullPath);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed deleting folder directory', [
                'folder_id' => $folder->id,
                'error' => $e->getMessage(),
            ]);
        }

        $folder->delete();
    }

    protected function deleteProjectFile(ProjectFolderFile $file): void
    {
        $fullPath = public_path($file->path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        $file->delete();
    }

    protected function deleteProjectDirectory(Project $project): void
    {
        $slug = \Illuminate\Support\Str::slug($project->name);
        $path = 'projectsofus/' . $project->id . '-' . $slug;
        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            $this->deleteDirectory($fullPath);
        }
    }

    protected function buildFolderPath(ProjectFolder $folder): string
    {
        $projectSlug = \Illuminate\Support\Str::slug($folder->project->name);
        $segments = [];
        $current = $folder;
        while ($current) {
            array_unshift($segments, \Illuminate\Support\Str::slug($current->name));
            $current = $current->parent;
        }
        $relative = implode('/', $segments);
        return 'projectsofus/' . $folder->project_id . '-' . $projectSlug . '/' . $relative;
    }

    protected function deleteDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}

