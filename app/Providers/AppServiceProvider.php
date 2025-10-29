<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Blade helper function for cleaning email body
        \Illuminate\Support\Facades\Blade::directive('cleanEmailBody', function ($expression) {
            return "<?php echo cleanEmailBody($expression); ?>";
        });

        // Set default pagination view to Bootstrap 5
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.bootstrap-5');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-5');

        // Share user ranking data to header view (cached for performance)
        View::composer('layouts.header', function ($view) {
            if (Auth::check() && Auth::user()->isRegularUser()) {
                try {
                    $reportService = new \App\Services\ReportService();
                    $userRanking = $reportService->getUserRankings(Auth::user()->id, 'overall');
                    $view->with('userRanking', $userRanking);
                } catch (\Exception $e) {
                    // Fallback if ranking calculation fails
                    \Illuminate\Support\Facades\Log::error('Error getting user ranking in header: ' . $e->getMessage());
                    $view->with('userRanking', null);
                }
            }
        });
    }
}
