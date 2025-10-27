<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponseTrait;

class RoleMiddleware
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }

        // Vérifier le rôle de l'utilisateur
        $userRole = $user->type_user;

        if ($userRole !== $role) {
            logger()->warning('Tentative d\'accès non autorisé', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'required_role' => $role,
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'ip' => $request->ip(),
            ]);

            return $this->errorResponse('Accès refusé. Rôle insuffisant.', 403);
        }

        // Ajouter les informations de rôle à la requête pour utilisation ultérieure
        $request->merge([
            'current_user' => $user,
            'user_role' => $userRole,
            'user_permissions' => $this->getUserPermissions($user),
        ]);

        return $next($request);
    }

    /**
     * Récupérer les permissions de l'utilisateur
     */
    private function getUserPermissions($user): array
    {
        $permissions = [];

        if ($user->type_user === 'client') {
            $permissions = [
                'view_own_accounts',
                'create_transactions',
                'view_own_transactions',
                'update_profile',
            ];
        } elseif ($user->type_user === 'admin') {
            $permissions = [
                'view_all_accounts',
                'manage_accounts',
                'view_all_transactions',
                'manage_users',
                'view_reports',
                'system_admin',
            ];
        }

        return $permissions;
    }
}