<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     * Log les opérations importantes pour l'audit
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // en millisecondes

        // Log des opérations importantes
        $this->logOperation($request, $response, $duration);

        return $response;
    }

    /**
     * Log les détails de l'opération
     */
    private function logOperation(Request $request, Response $response, float $duration): void
    {
        $route = $request->route();
        $method = $request->method();
        $statusCode = $response->getStatusCode();

        // Ne logger que les opérations importantes (POST, PUT, DELETE)
        if (!in_array($method, ['POST', 'PUT', 'DELETE'])) {
            return;
        }

        $logData = [
            'timestamp' => now()->toISOString(),
            'method' => $method,
            'url' => $request->fullUrl(),
            'route' => $route ? $route->getName() : 'unknown',
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'host' => $request->getHost(),
            'operation' => $this->getOperationName($method, $route),
        ];

        // Niveau de log selon le statut
        if ($statusCode >= 500) {
            Log::error('Operation failed', $logData);
        } elseif ($statusCode >= 400) {
            Log::warning('Operation client error', $logData);
        } else {
            Log::info('Operation successful', $logData);
        }
    }

    /**
     * Détermine le nom de l'opération
     */
    private function getOperationName(string $method, $route): string
    {
        if (!$route) {
            return 'unknown_operation';
        }

        $routeName = $route->getName();

        // Mapping des routes aux opérations métier
        $operations = [
            'comptes.index' => 'liste_comptes',
            'comptes.show' => 'consultation_compte',
            'comptes.store' => 'creation_compte',
            'comptes.update' => 'modification_compte',
            'comptes.destroy' => 'suppression_compte',
        ];

        return $operations[$routeName] ?? $routeName ?? 'unknown_operation';
    }
}
