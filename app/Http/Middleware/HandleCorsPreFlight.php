<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $project = Project::where('project_token', $projectToken)
            ->where('is_active', true)
            ->first();

        // SECURITY: Require explicit allowed_origins configuration
        // No wildcard default - must be configured by project owner
        if (!$project || !$project->allowed_origins || !is_array($project->allowed_origins) || empty($project->allowed_origins)) {
            if (!$project) {
                Log::warning('CORS: Project not found', ['token' => $projectToken, 'origin' => $origin, 'ip' => $request->ip()]);
            }
            
            // Deny preflight
            if ($request->isMethod('OPTIONS')) {
                return response('CORS policy: Project not configured', 403);
            }
            return $next($request);
        }

        $allowedOrigins = $project->allowed_origins;

        // Check if origin is allowed (with proper URL validation)
        $originAllowed = $this->isOriginAllowed($origin, $allowedOrigins);

        // Log CORS violations for security monitoring
        if (!$originAllowed) {
            Log::warning('CORS policy violation', [
                'origin' => $origin,
                'project_token' => $projectToken,
                'allowed_origins' => $allowedOrigins,
                'ip' => $request->ip(),
                'user_agent' => $request->header('user-agent')
            ]);
        }

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            if ($originAllowed) {
                return response('', 200)
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                    ->header('Access-Control-Max-Age', '3600')
                    ->header('Access-Control-Allow-Credentials', 'true');
            }
            
            // Deny preflight if origin not allowed
            return response('CORS policy violation', 403);
        }

        // Process the actual request and add CORS headers to response
        $response = $next($request);

        if ($originAllowed) {
            $response->header('Access-Control-Allow-Origin', $origin)
                     ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                     ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                     ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    /**
     * Validate origin against allowed origins with proper URL parsing
     * 
     * SECURITY: Prevents subdomain spoofing like:
     * - allowed: https://empor.mx
     * - attack: https://empor.mx.attacker.com ← Should fail
     */
    private function isOriginAllowed(string $origin, array $allowedOrigins): bool
    {
        if (empty($origin)) {
            return false;
        }

        $originUrl = parse_url($origin);
        if (!$originUrl || !isset($originUrl['host']) || !isset($originUrl['scheme'])) {
            return false;
        }

        foreach ($allowedOrigins as $allowed) {
            $allowedUrl = parse_url($allowed);
            
            if (!$allowedUrl || !isset($allowedUrl['host']) || !isset($allowedUrl['scheme'])) {
                continue;
            }

            // Compare scheme and host exactly
            if ($allowedUrl['scheme'] === $originUrl['scheme'] && 
                $allowedUrl['host'] === $originUrl['host']) {
                
                // Validate port if specified
                $allowedPort = $allowedUrl['port'] ?? null;
                $originPort = $originUrl['port'] ?? null;
                
                if ($allowedPort === $originPort) {
                    return true;
                }
            }
        }

        return false;
    }
}

