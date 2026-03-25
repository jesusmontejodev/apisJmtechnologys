<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectsWebController extends Controller
{
    /**
     * Show all projects
     */
    public function index(Request $request)
    {
        $projects = Auth::user()->projects()->paginate(10);
        return view('projects.index', ['projects' => $projects]);
    }

    /**
     * Show create project form
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store new project
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'recaptcha_type' => 'required|in:v2,v3',
            'recaptcha_site_key' => 'required|string',
            'recaptcha_secret_key' => 'required|string',
            'recaptcha_min_score' => 'nullable|numeric|between:0,1',
            'destination_email' => 'required|email',
            'email_subject' => 'nullable|string|max:255',
            'allowed_origins' => 'nullable|string',
        ]);

        // Parse allowed_origins (newline-separated)
        $allowedOrigins = [];
        if ($validated['allowed_origins']) {
            $allowedOrigins = array_filter(array_map('trim', explode("\n", $validated['allowed_origins'])));
        }

        $project = Auth::user()->projects()->create([
            'name' => $validated['name'],
            'recaptcha_type' => $validated['recaptcha_type'],
            'recaptcha_site_key' => $validated['recaptcha_site_key'],
            'recaptcha_secret_key' => $validated['recaptcha_secret_key'],
            'recaptcha_min_score' => $validated['recaptcha_min_score'] ?? 0.5,
            'destination_email' => $validated['destination_email'],
            'email_subject' => $validated['email_subject'] ?? 'Nuevo mensaje del formulario',
            'allowed_origins' => $allowedOrigins,
            'project_token' => Str::uuid(),
        ]);

        return redirect()->route('projects.show', $project->slug)
            ->with('success', 'Proyecto creado correctamente');
    }

    /**
     * Show single project
     */
    public function show(Request $request, string $slug)
    {
        $project = Auth::user()->projects()->where('slug', $slug)->firstOrFail();
        $logs = $project->submissionLogs()->orderBy('created_at', 'desc')->paginate(10);
        
        return view('projects.show', [
            'project' => $project,
            'logs' => $logs,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(Request $request, string $slug)
    {
        $project = Auth::user()->projects()->where('slug', $slug)->firstOrFail();
        return view('projects.edit', ['project' => $project]);
    }

    /**
     * Update project
     */
    public function update(Request $request, string $slug)
    {
        $project = Auth::user()->projects()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'recaptcha_type' => 'sometimes|in:v2,v3',
            'recaptcha_site_key' => 'sometimes|string',
            'recaptcha_secret_key' => 'sometimes|string',
            'recaptcha_min_score' => 'sometimes|numeric|between:0,1',
            'destination_email' => 'sometimes|email',
            'email_subject' => 'sometimes|string|max:255',
            'allowed_origins' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['allowed_origins'])) {
            // Split by newline and clean
            $origins = array_filter(
                array_map('trim', explode("\n", $validated['allowed_origins']))
            );

            // Validate each origin
            $validOrigins = [];
            foreach ($origins as $origin) {
                if (filter_var($origin, FILTER_VALIDATE_URL)) {
                    $validOrigins[] = $origin;
                }
            }

            $validated['allowed_origins'] = $validOrigins;
        }

        $project->update($validated);

        return redirect()->route('projects.show', $project->slug)
            ->with('success', 'Proyecto actualizado correctamente');
    }

    /**
     * Delete project
     */
    public function destroy(Request $request, string $slug)
    {
        $project = Auth::user()->projects()->where('slug', $slug)->firstOrFail();
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Proyecto eliminado correctamente');
    }

    /**
     * Show stats for project
     */
    public function stats(Request $request, string $slug)
    {
        $project = Auth::user()->projects()->where('slug', $slug)->firstOrFail();

        $totalSubmissions = $project->submissionLogs()->count();
        $passedSubmissions = $project->submissionLogs()->where('status', 'passed')->count();
        $blockedSubmissions = $project->submissionLogs()->where('status', 'blocked')->count();
        $emails_sent = $project->submissionLogs()->where('email_sent', true)->count();

        $blockRate = $totalSubmissions > 0 
            ? round(($blockedSubmissions / $totalSubmissions) * 100, 2)
            : 0;

        // Get daily stats for last 30 days
        $dailyStats = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $passed = $project->submissionLogs()
                ->whereDate('created_at', $date)
                ->where('status', 'passed')
                ->count();
            
            $blocked = $project->submissionLogs()
                ->whereDate('created_at', $date)
                ->where('status', 'blocked')
                ->count();
            
            if ($passed > 0 || $blocked > 0) {
                $dailyStats->push([
                    'date' => $date,
                    'passed' => $passed,
                    'blocked' => $blocked,
                ]);
            }
        }

        return view('projects.stats', [
            'project' => $project,
            'totalSubmissions' => $totalSubmissions,
            'passedSubmissions' => $passedSubmissions,
            'blockedSubmissions' => $blockedSubmissions,
            'emails_sent' => $emails_sent,
            'blockRate' => $blockRate,
            'dailyStats' => $dailyStats,
        ]);
    }
}
