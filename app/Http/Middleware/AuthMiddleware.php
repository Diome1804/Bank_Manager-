<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponseTrait;

class AuthMiddleware
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié via Passport
        if (!Auth::guard('api')->check()) {
            return $this->errorResponse('Accès non autorisé. Token manquant ou invalide.', 401);
        }

        // Logger l'accès authentifié
        logger()->info('Accès authentifié', [
            'user_id' => Auth::guard('api')->id(),
            'user_type' => get_class(Auth::guard('api')->user()),
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $next($request);
    }
}