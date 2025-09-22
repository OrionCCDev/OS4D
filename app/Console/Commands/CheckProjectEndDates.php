<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class CheckProjectEndDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:check-end-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check project end dates and create notifications for projects ending soon or overdue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking project end dates...');

        $projects = Project::whereNotNull('end_date')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        $notificationsCreated = 0;

        foreach ($projects as $project) {
            $daysRemaining = $project->days_remaining;
            $isOverdue = $project->is_overdue;

            // Check if notification should be created
            $shouldNotify = false;
            $notificationType = '';

            if ($isOverdue) {
                $shouldNotify = true;
                $notificationType = 'overdue';
            } elseif ($daysRemaining <= 3 && $daysRemaining > 0) {
                $shouldNotify = true;
                $notificationType = 'ending_soon';
            }

            if ($shouldNotify) {
                // Check if notification already exists for today
                $existingNotification = $project->owner->customNotifications()
                    ->where('type', 'project_' . $notificationType)
                    ->where('data->project_id', $project->id)
                    ->whereDate('created_at', today())
                    ->first();

                if (!$existingNotification) {
                    $project->createEndDateNotification();
                    $notificationsCreated++;
                    $this->line("Created notification for project: {$project->name} ({$notificationType})");
                }
            }
        }

        $this->info("Created {$notificationsCreated} notifications for project end dates.");
        return 0;
    }
}
