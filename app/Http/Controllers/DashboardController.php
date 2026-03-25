<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show dashboard homepage
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get user projects
        $projects = $user->projects()->get();
        
        // Get stats from all projects
        $totalSubmissions = $user->projects()
            ->join('submission_logs', 'projects.id', '=', 'submission_logs.project_id')
            ->count();
        
        $passedSubmissions = $user->projects()
            ->join('submission_logs', 'projects.id', '=', 'submission_logs.project_id')
            ->where('submission_logs.status', 'passed')
            ->count();
        
        $blockedSubmissions = $user->projects()
            ->join('submission_logs', 'projects.id', '=', 'submission_logs.project_id')
            ->where('submission_logs.status', 'blocked')
            ->count();
        
        $emailsSent = $user->projects()
            ->join('submission_logs', 'projects.id', '=', 'submission_logs.project_id')
            ->where('submission_logs.email_sent', true)
            ->count();
        
        $blockRate = $totalSubmissions > 0 
            ? round(($blockedSubmissions / $totalSubmissions) * 100, 2)
            : 0;
        
        // Get last 7 days data for chart
        $dailyStats = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $passed = $user->projects()
                ->join('submission_logs', 'projects.id', '=', 'submission_logs.project_id')
                ->whereDate('submission_logs.created_at', $date)
                ->where('submission_logs.status', 'passed')
                ->count();
            
            $blocked = $user->projects()
                ->join('submission_logs', 'projects.id', '=', 'submission_logs.project_id')
                ->whereDate('submission_logs.created_at', $date)
                ->where('submission_logs.status', 'blocked')
                ->count();
            
            $dailyStats->push([
                'date' => $date,
                'passed' => $passed,
                'blocked' => $blocked,
            ]);
        }

        return view('dashboard.index', [
            'projects' => $projects,
            'totalSubmissions' => $totalSubmissions,
            'passedSubmissions' => $passedSubmissions,
            'blockedSubmissions' => $blockedSubmissions,
            'emailsSent' => $emailsSent,
            'blockRate' => $blockRate,
            'dailyStats' => $dailyStats,
        ]);
    }

    /**
     * Get system health status
     */
    public function health()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'mail' => $this->checkMail(),
        ];

        $allHealthy = collect($checks)->every(fn($c) => $c['status'] === 'healthy');

        return view('dashboard.health', [
            'checks' => $checks,
            'allHealthy' => $allHealthy,
        ]);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            return [
                'status' => 'healthy',
                'message' => 'Database connection is working',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test_value', 1);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            if ($value === 'test_value') {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache is working',
                ];
            }

            return [
                'status' => 'unhealthy',
                'message' => 'Cache operation failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache check failed',
            ];
        }
    }

    private function checkQueue(): array
    {
        return [
            'status' => 'healthy',
            'message' => 'Queue driver: ' . config('queue.default'),
        ];
    }

    private function checkMail(): array
    {
        return [
            'status' => 'healthy',
            'message' => 'Mail driver: ' . config('mail.default'),
        ];
    }
}
