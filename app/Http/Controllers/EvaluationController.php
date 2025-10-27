<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmployeeEvaluation;
use App\Services\UserEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    protected $evaluationService;

    public function __construct(UserEvaluationService $evaluationService)
    {
        $this->middleware('auth');
        $this->evaluationService = $evaluationService;
    }

    /**
     * Show evaluation dashboard
     */
    public function index()
    {
        $period = request('period', 'month');
        
        $user = Auth::user();
        
        // Get user's evaluation
        $evaluation = $this->evaluationService->calculateUserEvaluation($user, $period);
        
        // Get all users' evaluations for comparison (managers only)
        $allEvaluations = [];
        if ($user->isManager()) {
            $allEvaluations = $this->getAllEvaluations($period);
        }
        
        return view('evaluations.index', compact('evaluation', 'allEvaluations', 'period'));
    }

    /**
     * Show detailed evaluation for a user
     */
    public function show($userId)
    {
        $user = Auth::user();
        $targetUser = User::findOrFail($userId);
        
        // Only allow managers to view other users' evaluations
        if (!$user->isManager() && $user->id != $targetUser->id) {
            abort(403);
        }
        
        $period = request('period', 'month');
        $evaluation = $this->evaluationService->calculateUserEvaluation($targetUser, $period);
        
        // Get user's tasks
        $tasks = $targetUser->assignedTasks()->latest()->paginate(20);
        
        return view('evaluations.show', compact('evaluation', 'targetUser', 'tasks', 'period'));
    }

    /**
     * Get all users' evaluations (for managers)
     */
    private function getAllEvaluations($period)
    {
        $users = User::whereHas('assignedTasks')->get();
        
        $evaluations = [];
        foreach ($users as $user) {
            $evaluation = $this->evaluationService->calculateUserEvaluation($user, $period);
            $evaluation['user'] = $user;
            $evaluations[] = $evaluation;
        }
        
        // Sort by overall score descending
        usort($evaluations, function($a, $b) {
            return $b['overall_score'] <=> $a['overall_score'];
        });
        
        return $evaluations;
    }

    /**
     * Generate evaluation report
     */
    public function report($userId)
    {
        $user = Auth::user();
        $targetUser = User::findOrFail($userId);
        
        if (!$user->isManager()) {
            abort(403);
        }
        
        $period = request('period', 'month');
        $evaluation = $this->evaluationService->calculateUserEvaluation($targetUser, $period);
        
        return view('evaluations.report', compact('evaluation', 'targetUser', 'period'));
    }
}

