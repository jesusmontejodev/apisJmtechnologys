<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class HealthController extends Controller
{
    /**
     * Get system health status
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'mail' => $this->checkMail(),
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'healthy');
        $overallStatus = $allHealthy ? 'healthy' : 'degraded';

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Check database connectivity
     */
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
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connectivity
     */
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
                'message' => 'Cache check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue connectivity
     */
    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            if ($connection === 'sync') {
                return [
                    'status' => 'healthy',
                    'message' => 'Queue driver: sync (synchronous)',
                ];
            }

            return [
                'status' => 'healthy',
                'message' => 'Queue driver: ' . $connection,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Queue check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check mail connectivity
     */
    private function checkMail(): array
    {
        try {
            $mailer = config('mail.default');
            if ($mailer === 'log' || $mailer === 'array') {
                return [
                    'status' => 'healthy',
                    'message' => 'Mail driver: ' . $mailer . ' (no connectivity check needed)',
                ];
            }

            return [
                'status' => 'healthy',
                'message' => 'Mail driver: ' . $mailer,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Mail check failed: ' . $e->getMessage(),
            ];
        }
    }
}
