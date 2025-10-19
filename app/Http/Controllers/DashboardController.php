<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\CustomNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // If user is a manager or admin, show comprehensive dashboard
        if ($user->isManager()) {
            $data = $this->getDashboardData();
            return view('dashboard.manager', compact('data'));
        }

        // For regular users, show enhanced user dashboard
        $userData = $this->getUserDashboardData();
        return view('dashboard.user', compact('userData'));
    }

    public function getUserDashboardData()
    {
        $user = Auth::user();
        $now = now();
        $reportService = new \App\Services\ReportService();

        // User's task statistics
        $userTasks = $user->assignedTasks();
        $taskStats = [
            'total' => $userTasks->count(),
            'completed' => $userTasks->where('status', 'completed')->count(),
            'in_progress' => $userTasks->where('status', 'in_progress')->count(),
            'pending' => $userTasks->where('status', 'pending')->count(),
            'assigned' => $userTasks->where('status', 'assigned')->count(),
            'in_review' => $userTasks->where('status', 'in_review')->count(),
            'overdue' => $userTasks->where('due_date', '<', $now)
                ->whereNotIn('status', ['completed', 'approved'])
                ->count(),
            'due_soon' => $userTasks->whereBetween('due_date', [$now, $now->copy()->addDays(7)])
                ->whereNotIn('status', ['completed', 'approved'])
                ->count(),
        ];

        // Calculate completion rate
        $taskStats['completion_rate'] = $taskStats['total'] > 0
            ? round(($taskStats['completed'] / $taskStats['total']) * 100, 1)
            : 0;

        // Recent tasks (last 10)
        $recentTasks = $user->assignedTasks()->with(['project', 'folder'])
            ->latest()
            ->limit(10)
            ->get();

        // Upcoming due dates (next 7 days)
        $upcomingTasks = $user->assignedTasks()->with(['project', 'folder'])
            ->whereBetween('due_date', [$now, $now->copy()->addDays(7)])
            ->whereNotIn('status', ['completed', 'approved'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Overdue tasks
        $overdueTasks = $user->assignedTasks()->with(['project', 'folder'])
            ->where('due_date', '<', $now)
            ->whereNotIn('status', ['completed', 'approved'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Tasks by priority
        $tasksByPriority = $user->assignedTasks()->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Tasks by status
        $tasksByStatus = $user->assignedTasks()->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Weekly completion trend (last 8 weeks)
        $weeklyTrend = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $completed = $userTasks->where('status', 'completed')
                ->whereBetween('completed_at', [$weekStart, $weekEnd])
                ->count();

            $weeklyTrend[] = [
                'week' => $weekStart->format('M d'),
                'completed' => $completed
            ];
        }

        // User's recent activity (last 30 days)
        $recentActivity = $user->assignedTasks()->where('created_at', '>=', $now->copy()->subDays(30))
            ->with(['project', 'folder'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // User's notifications
        $notifications = $user->customNotifications()
            ->latest()
            ->limit(5)
            ->get();

        // Performance metrics
        $performance = [
            'avg_completion_time' => $this->calculateAverageCompletionTime($user),
            'tasks_this_week' => $userTasks->where('created_at', '>=', $now->copy()->startOfWeek())->count(),
            'tasks_this_month' => $userTasks->where('created_at', '>=', $now->copy()->startOfMonth())->count(),
            'completed_this_week' => $userTasks->where('status', 'completed')
                ->where('completed_at', '>=', $now->copy()->startOfWeek())->count(),
            'completed_this_month' => $userTasks->where('status', 'completed')
                ->where('completed_at', '>=', $now->copy()->startOfMonth())->count(),
        ];

        // Get user rankings
        $overallRanking = $reportService->getUserRankings($user->id, 'overall');
        $monthlyRanking = $reportService->getUserRankings($user->id, 'monthly');

        return [
            'user' => $user,
            'task_stats' => $taskStats,
            'recent_tasks' => $recentTasks,
            'upcoming_tasks' => $upcomingTasks,
            'overdue_tasks' => $overdueTasks,
            'tasks_by_priority' => $tasksByPriority,
            'tasks_by_status' => $tasksByStatus,
            'weekly_trend' => $weeklyTrend,
            'recent_activity' => $recentActivity,
            'notifications' => $notifications,
            'performance' => $performance,
            'rankings' => [
                'overall' => $overallRanking,
                'monthly' => $monthlyRanking,
            ],
        ];
    }

    private function calculateAverageCompletionTime($user)
    {
        $completedTasks = $user->assignedTasks()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_at')
            ->get();

        if ($completedTasks->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        foreach ($completedTasks as $task) {
            $totalDays += $task->assigned_at->diffInDays($task->completed_at);
        }

        return round($totalDays / $completedTasks->count(), 1);
    }

    public function getDashboardData()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();

        // Basic counts
        $totalUsers = User::count();
        $totalTasks = Task::count();
        $totalProjects = Project::count();
        $activeUsers = User::whereHas('assignedTasks', function($query) {
            $query->whereIn('status', ['assigned', 'in_progress', 'in_review']);
        })->count();

        // Task statistics
        $taskStats = [
            'total' => $totalTasks,
            'completed' => Task::where('status', 'completed')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'pending' => Task::where('status', 'pending')->count(),
            'assigned' => Task::where('status', 'assigned')->count(),
            'in_review' => Task::where('status', 'in_review')->count(),
            'overdue' => Task::where('due_date', '<', $now)
                ->whereNotIn('status', ['completed', 'approved'])
                ->count(),
            'due_soon' => Task::whereBetween('due_date', [$now, $now->copy()->addDays(7)])
                ->whereNotIn('status', ['completed', 'approved'])
                ->count(),
        ];

        // Task completion rate
        $taskStats['completion_rate'] = $totalTasks > 0
            ? round(($taskStats['completed'] / $totalTasks) * 100, 1)
            : 0;

        // Tasks by priority
        $tasksByPriority = Task::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Tasks by status
        $tasksByStatus = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Top performers (users with most completed tasks) - Overall
        $topPerformers = User::withCount(['assignedTasks as completed_tasks_count' => function($query) {
                $query->where('status', 'completed');
            }])
            ->withCount(['assignedTasks as total_tasks_count'])
            ->having('completed_tasks_count', '>', 0)
            ->orderBy('completed_tasks_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($user) {
                $user->completion_rate = $user->total_tasks_count > 0
                    ? round(($user->completed_tasks_count / $user->total_tasks_count) * 100, 1)
                    : 0;
                return $user;
            });

        // Top performers for current month - simplified and more robust query
        $monthlyTopPerformers = User::withCount(['assignedTasks as completed_tasks_count' => function($query) {
                $query->where('status', 'completed')
                      ->where(function($q) {
                          // Include tasks with completed_at in current month
                          $q->where(function($subQ) {
                              $subQ->whereMonth('completed_at', now()->month)
                                   ->whereYear('completed_at', now()->year);
                          })
                          // OR tasks completed this month but without completed_at (fallback to updated_at)
                          ->orWhere(function($subQ) {
                              $subQ->whereNull('completed_at')
                                   ->whereMonth('updated_at', now()->month)
                                   ->whereYear('updated_at', now()->year);
                          });
                      });
            }])
            ->withCount(['assignedTasks as total_tasks_count' => function($query) {
                $query->where(function($q) {
                    $q->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                });
            }])
            ->whereHas('assignedTasks', function($query) {
                $query->where('status', 'completed');
            })
            ->orderBy('completed_tasks_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($user) {
                $user->completion_rate = $user->total_tasks_count > 0
                    ? round(($user->completed_tasks_count / $user->total_tasks_count) * 100, 1)
                    : 0;
                $user->monthly_performance_score = $user->completed_tasks_count * 10 + $user->completion_rate;
                return $user;
            });

        // If no monthly performers found, get any users with completed tasks as fallback
        if ($monthlyTopPerformers->count() == 0) {
            $monthlyTopPerformers = User::withCount(['assignedTasks as completed_tasks_count' => function($query) {
                    $query->where('status', 'completed');
                }])
                ->withCount(['assignedTasks as total_tasks_count'])
                ->whereHas('assignedTasks', function($query) {
                    $query->where('status', 'completed');
                })
                ->orderBy('completed_tasks_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function($user) {
                    $user->completion_rate = $user->total_tasks_count > 0
                        ? round(($user->completed_tasks_count / $user->total_tasks_count) * 100, 1)
                        : 0;
                    $user->monthly_performance_score = $user->completed_tasks_count * 10 + $user->completion_rate;
                    return $user;
                });
        }

        // Tasks per user
        $tasksPerUser = User::withCount(['assignedTasks as total_tasks', 'assignedTasks as completed_tasks' => function($query) {
                $query->where('status', 'completed');
            }])
            ->whereHas('assignedTasks')
            ->orderBy('total_tasks', 'desc')
            ->get();

        // Recent activity (last 30 days)
        $recentActivity = Task::with(['assignee', 'project'])
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Upcoming due dates (next 7 days)
        $upcomingDueDates = Task::with(['assignee', 'project'])
            ->whereBetween('due_date', [$now, $now->copy()->addDays(7)])
            ->whereNotIn('status', ['completed', 'approved'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Overdue tasks
        $overdueTasks = Task::with(['assignee', 'project'])
            ->where('due_date', '<', $now)
            ->whereNotIn('status', ['completed', 'approved'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Monthly task completion trend (last 12 months)
        $monthlyTrend = Task::select(
                DB::raw('DATE_FORMAT(completed_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as completed_count')
            )
            ->where('completed_at', '>=', $now->copy()->subMonths(12))
            ->whereNotNull('completed_at')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('completed_count', 'month')
            ->toArray();

        // Project statistics
        $projectStats = [
            'total' => $totalProjects,
            'active' => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
        ];

        // Weekly task completion
        $weeklyCompleted = Task::where('status', 'completed')
            ->where('completed_at', '>=', $startOfWeek)
            ->count();

        // Monthly task completion
        $monthlyCompleted = Task::where('status', 'completed')
            ->where('completed_at', '>=', $startOfMonth)
            ->count();

        // Average task completion time
        $avgCompletionTime = Task::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_at')
            ->selectRaw('AVG(DATEDIFF(completed_at, assigned_at)) as avg_days')
            ->value('avg_days');

        // Task distribution by project
        $tasksByProject = Project::withCount('tasks')
            ->having('tasks_count', '>', 0)
            ->orderBy('tasks_count', 'desc')
            ->limit(10)
            ->get();

        // Recent notifications
        $recentNotifications = CustomNotification::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return [
            'overview' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'total_tasks' => $totalTasks,
                'total_projects' => $totalProjects,
                'completion_rate' => $taskStats['completion_rate'],
                'weekly_completed' => $weeklyCompleted,
                'monthly_completed' => $monthlyCompleted,
                'avg_completion_time' => round($avgCompletionTime ?? 0, 1),
            ],
            'task_stats' => $taskStats,
            'project_stats' => $projectStats,
            'tasks_by_priority' => $tasksByPriority,
            'tasks_by_status' => $tasksByStatus,
            'top_performers' => $topPerformers,
            'monthly_top_performers' => $monthlyTopPerformers,
            'quarterly_top_performers' => $this->getTopPerformersForPeriod('quarter'),
            'yearly_top_performers' => $this->getTopPerformersForPeriod('year'),
            'tasks_per_user' => $tasksPerUser,
            'recent_activity' => $recentActivity,
            'upcoming_due_dates' => $upcomingDueDates,
            'overdue_tasks' => $overdueTasks,
            'monthly_trend' => $monthlyTrend,
            'tasks_by_project' => $tasksByProject,
            'recent_notifications' => $recentNotifications,
        ];
    }

    /**
     * Get top performers for different time periods
     */
    public function getTopPerformersForPeriod($period = 'month')
    {
        $now = now();
        $startDate = null;
        $endDate = null;

        switch ($period) {
            case 'month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'quarter':
                $startDate = $now->copy()->startOfQuarter();
                $endDate = $now->copy()->endOfQuarter();
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
        }

        return User::withCount(['assignedTasks as completed_tasks_count' => function($query) use ($startDate, $endDate) {
                $query->where('status', 'completed')
                      ->where(function($q) use ($startDate, $endDate) {
                          // Include tasks with completed_at in period
                          $q->where(function($subQ) use ($startDate, $endDate) {
                              $subQ->whereBetween('completed_at', [$startDate, $endDate]);
                          })
                          // OR tasks completed in period but without completed_at (fallback to updated_at)
                          ->orWhere(function($subQ) use ($startDate, $endDate) {
                              $subQ->whereNull('completed_at')
                                   ->whereBetween('updated_at', [$startDate, $endDate]);
                          });
                      });
            }])
            ->withCount(['assignedTasks as total_tasks_count' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->whereHas('assignedTasks', function($query) use ($startDate, $endDate) {
                $query->where('status', 'completed')
                      ->where(function($q) use ($startDate, $endDate) {
                          $q->where(function($subQ) use ($startDate, $endDate) {
                              $subQ->whereBetween('completed_at', [$startDate, $endDate]);
                          })
                          ->orWhere(function($subQ) use ($startDate, $endDate) {
                              $subQ->whereNull('completed_at')
                                   ->whereBetween('updated_at', [$startDate, $endDate]);
                          });
                      });
            })
            ->orderBy('completed_tasks_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($user) {
                $user->completion_rate = $user->total_tasks_count > 0
                    ? round(($user->completed_tasks_count / $user->total_tasks_count) * 100, 1)
                    : 0;
                $user->performance_score = $user->completed_tasks_count * 10 + $user->completion_rate;
                return $user;
            });
    }

    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'monthly_trend');

        switch ($type) {
            case 'monthly_trend':
                return $this->getMonthlyTrendData();
            case 'tasks_by_status':
                return $this->getTasksByStatusData();
            case 'tasks_by_priority':
                return $this->getTasksByPriorityData();
            case 'user_performance':
                return $this->getUserPerformanceData();
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    private function getMonthlyTrendData()
    {
        $now = now();
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $monthName = $month->format('M Y');

            $completed = Task::where('status', 'completed')
                ->whereYear('completed_at', $month->year)
                ->whereMonth('completed_at', $month->month)
                ->count();

            $data[] = [
                'month' => $monthName,
                'completed' => $completed
            ];
        }

        return response()->json($data);
    }

    private function getTasksByStatusData()
    {
        $statuses = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $data = [];
        foreach ($statuses as $status => $count) {
            $data[] = [
                'status' => ucfirst(str_replace('_', ' ', $status)),
                'count' => $count
            ];
        }

        return response()->json($data);
    }

    private function getTasksByPriorityData()
    {
        $priorities = Task::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $data = [];
        foreach ($priorities as $priority => $count) {
            $data[] = [
                'priority' => ucfirst($priority),
                'count' => $count
            ];
        }

        return response()->json($data);
    }

    private function getUserPerformanceData()
    {
        $users = User::withCount(['assignedTasks as completed_tasks_count' => function($query) {
                $query->where('status', 'completed');
            }])
            ->withCount(['assignedTasks as total_tasks_count'])
            ->having('completed_tasks_count', '>', 0)
            ->orderBy('completed_tasks_count', 'desc')
            ->limit(10)
            ->get();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'name' => $user->name,
                'completed' => $user->completed_tasks_count,
                'total' => $user->total_tasks_count,
                'rate' => $user->total_tasks_count > 0
                    ? round(($user->completed_tasks_count / $user->total_tasks_count) * 100, 1)
                    : 0
            ];
        }

        return response()->json($data);
    }
}
