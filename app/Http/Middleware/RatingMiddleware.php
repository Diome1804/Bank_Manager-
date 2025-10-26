<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RatingMiddleware
{
    /**
     * Handle an incoming request.
     * Enregistre les informations sur les utilisateurs qui atteignent le rate limit
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Vérifier si la réponse indique un rate limiting (429)
        if ($response->getStatusCode() === 429) {
            $this->logRateLimitExceeded($request);
        }

        return $response;
    }

    /**
     * Log les informations sur le rate limit dépassé
     */
    private function logRateLimitExceeded(Request $request): void
    {
        $user = $request->user();
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $route = $request->route() ? $request->route()->getName() : 'unknown';
        $method = $request->method();
        $url = $request->fullUrl();

        Log::warning('Rate limit exceeded', [
            'user_id' => $user ? $user->id : null,
            'user_type' => $user ? $user->type_user : null,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'route' => $route,
            'method' => $method,
            'url' => $url,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
