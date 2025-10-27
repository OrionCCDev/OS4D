<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get dashboard data with caching
     */
    public function getDashboardData($user)
    {
        $cacheKey = 'dashboard_data_' . $user->id;
        
        // Cache dashboard data for 2 minutes
        return Cache::remember($cacheKey, 120, function() use ($user) {
            return $this->buildDashboardData($user);
        });
    }

    /**
     * Build dashboard data
     */
    private function buildDashboardData($user)
    {
        // Implementation will be moved from DashboardController
        // This allows for better separation of concerns
    }

    /**
     * Clear dashboard cache
     */
    public function clearDashboardCache($userId = null)
    {
        if ($userId) {
            Cache::forget('dashboard_data_' . $userId);
        } else {
            // Clear all dashboard caches
            $userIds = User::pluck('id');
            foreach ($userIds as $id) {
                Cache::forget('dashboard_data_' . $id);
            }
        }
    }
}

