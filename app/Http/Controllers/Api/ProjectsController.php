<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Models\SubmissionLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class ProjectsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Auth::user()->projects()->get();

        return response()->json([
            'success' => true,
            'message' => 'Projects retrieved successfully',
            'data' => $projects,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'endpoint_destino' => 'nullable|url',
                'recaptcha_type' => 'required|in:v2,v3',
                'recaptcha_site_key' => 'required|string',
                'recaptcha_secret_key' => 'required|string',
                'recaptcha_min_score' => 'nullable|numeric|between:0,1',
                'allowed_origins' => 'nullable|array',
                'destination_email' => 'required|email',
                'email_subject' => 'nullable|string|max:255',
            ]);

            $project = Auth::user()->projects()->create([
                'name' => $validated['name'],
                'endpoint_destino' => $validated['endpoint_destino'] ?? null,
                'recaptcha_type' => $validated['recaptcha_type'],
                'recaptcha_site_key' => $validated['recaptcha_site_key'],
                'recaptcha_secret_key' => $validated['recaptcha_secret_key'],
                'recaptcha_min_score' => $validated['recaptcha_min_score'] ?? 0.5,
                'allowed_origins' => $validated['allowed_origins'] ?? [],
                'project_token' => Str::uuid(),
                'destination_email' => $validated['destination_email'],
                'email_subject' => $validated['email_subject'] ?? 'Nuevo mensaje del formulario',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $e->errors()
            ], 422);
        }
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $project = Auth::user()->projects()->where('slug', $slug)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Project retrieved successfully',
            'data' => $project,
        ], 200);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $project = Auth::user()->projects()->where('slug', $slug)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'data' => null
            ], 404);
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'endpoint_destino' => 'sometimes|url',
                'recaptcha_type' => 'sometimes|in:v2,v3',
                'recaptcha_site_key' => 'sometimes|string',
                'recaptcha_secret_key' => 'sometimes|string',
                'recaptcha_min_score' => 'sometimes|numeric|between:0,1',
                'allowed_origins' => 'sometimes|array',
                'is_active' => 'sometimes|boolean',
                'destination_email' => 'sometimes|email',
                'email_subject' => 'sometimes|string|max:255',
            ]);

            $project->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $project = Auth::user()->projects()->where('slug', $slug)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'data' => null
            ], 404);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
            'data' => null
        ], 200);
    }

    public function regenerateToken(Request $request, string $slug): JsonResponse
    {
        $project = Auth::user()->projects()->where('slug', $slug)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'data' => null
            ], 404);
        }

        $project->update(['project_token' => Str::uuid()]);

        return response()->json([
            'success' => true,
            'message' => 'Project token regenerated successfully',
            'data' => $project,
        ], 200);
    }

    public function logs(Request $request, string $slug): JsonResponse
    {
        $project = Auth::user()->projects()->where('slug', $slug)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'data' => null
            ], 404);
        }

        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 50);
        $status = $request->query('status');

        $query = $project->submissionLogs();

        if ($status) {
            $query->where('status', $status);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Logs retrieved successfully',
            'data' => $logs,
        ], 200);
    }

    public function stats(Request $request, string $slug): JsonResponse
    {
        $project = Auth::user()->projects()->where('slug', $slug)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'data' => null
            ], 404);
        }

        $now = \Carbon\Carbon::now();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        $total = $project->submissionLogs()->count();
        $passed = $project->submissionLogs()->where('status', 'passed')->count();
        $blocked = $project->submissionLogs()->where('status', 'blocked')->count();
        $errors = $project->submissionLogs()->where('status', 'error')->count();

        $blockRate = $total > 0 ? round(($blocked / $total) * 100, 2) : 0;

        // Stats by day for last 30 days
        $dailyStats = $project->submissionLogs()
            ->whereBetween('created_at', [$thirtyDaysAgo, $now])
            ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return response()->json([
            'success' => true,
            'message' => 'Stats retrieved successfully',
            'data' => [
                'total' => $total,
                'passed' => $passed,
                'blocked' => $blocked,
                'errors' => $errors,
                'block_rate' => $blockRate,
                'daily_stats' => $dailyStats,
            ],
        ], 200);
    }
}
}
