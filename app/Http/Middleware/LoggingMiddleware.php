<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user();
        $startTime = microtime(true);

        // Logger la requête entrante
        logger()->info('Début de requête authentifiée', [
            'user_id' => $user ? $user->id : null,
            'user_type' => $user ? get_class($user) : null,
            'method' => $request->method(),
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $this->sanitizeRequestData($request),
        ]);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // en millisecondes

        // Logger la réponse
        $logLevel = $response->getStatusCode() >= 400 ? 'warning' : 'info';

        logger()->log($logLevel, 'Fin de requête authentifiée', [
            'user_id' => $user ? $user->id : null,
            'method' => $request->method(),
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'response_size' => strlen($response->getContent()),
        ]);

        return $response;
    }

    /**
     * Nettoyer les données sensibles de la requête pour le logging
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();

        // Masquer les champs sensibles
        $sensitiveFields = ['password', 'password_temp', 'code_verification', 'token', 'refresh_token'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***masked***';
            }
        }

        // Limiter la taille des données pour éviter les logs trop volumineux
        $data = $this->limitArraySize($data);

        return $data;
    }

    /**
     * Limiter la taille des tableaux pour le logging
     */
    private function limitArraySize(array $data, int $maxItems = 10): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (count($value) > $maxItems) {
                    $result[$key] = array_slice($value, 0, $maxItems);
                    $result[$key]['...'] = (count($value) - $maxItems) . ' more items';
                } else {
                    $result[$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
