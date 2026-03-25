<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCorsPreFlight
{
    /**
     * Handle preflight CORS requests and add CORS headers to all responses
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract project token from URL: /api/submit/{project_token}
        $projectToken = $request->route('project_token');

        if (!$projectToken) {
            return $next($request);
        }

        // Get origin from request
        $origin = $request->header('origin') ?? $request->header('referer');

        // Find project and get allowed origins
        $allowedOrigins = ['*']; // Default: allow all

        if ($projectToken) {
            $project = Project::where('project_token', $projectToken)->where('is_active', true)->first();
            
            if ($project && $project->allowed_origins && is_array($project->allowed_origins) && !empty($project->allowed_origins)) {
                $allowedOrigins = $project->allowed_origins;
            }
        }

        // Check if origin is allowed
        $originAllowed = in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins);

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            if ($originAllowed) {
                return response('', 200)
                    ->header('Access-Control-Allow-Origin', $origin ?? '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                    ->header('Access-Control-Max-Age', '3600');
            }
            
            // Deny preflight if origin not allowed
            return response('CORS policy violation', 403);
        }

        // Process the actual request and add CORS headers to response
        $response = $next($request);

        if ($originAllowed) {
            $response->header('Access-Control-Allow-Origin', $origin ?? '*')
                     ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                     ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                     ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
