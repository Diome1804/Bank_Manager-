<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Login pour clients et admins
     *
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Authentification utilisateur",
     *     description="Authentifie un client ou admin et retourne les tokens",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="client1@test.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentification réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Authentification réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="refresh_token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Credentials invalides"),
     *     @OA\Response(response=422, description="Données invalides")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = null;
        $scope = '';

        // Essayer d'abord comme client
        $user = Client::where('email', $request->email)->first();
        if ($user) {
            $scope = 'client';
        } else {
            // Essayer comme admin
            $user = Admin::where('email', $request->email)->first();
            if ($user) {
                $scope = 'admin';
            }
        }

        if (!$user || !Hash::check($request->password, $user->password_temp)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification sont incorrectes.'],
            ]);
        }

        // Créer le token avec le scope approprié
        $token = $user->createToken('API Token', [$scope]);

        // Stocker le refresh token dans un cookie sécurisé
        $cookie = cookie(
            'refresh_token',
            $token->refreshToken->id,
            60 * 24 * 7, // 7 jours
            '/',
            null,
            true, // secure
            true, // httpOnly
            false,
            'Strict'
        );

        return $this->successResponse([
            'user' => $user,
            'access_token' => $token->accessToken,
            'refresh_token' => $token->refreshToken->id,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 1 heure
            'scope' => $scope,
        ], 'Authentification réussie')->withCookie($cookie);
    }

    /**
     * Rafraîchir le token d'accès
     *
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Rafraîchir le token d'accès",
     *     description="Utilise le refresh token pour obtenir un nouveau token d'accès",
     *     operationId="refresh",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Refresh token invalide")
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshTokenId = $request->cookie('refresh_token');

        if (!$refreshTokenId) {
            return $this->errorResponse('Refresh token manquant', 401);
        }

        $user = Auth::guard('api')->user();

        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }

        // Révoquer l'ancien token
        $user->tokens()->where('id', $refreshTokenId)->delete();

        // Créer un nouveau token
        $scope = $user instanceof Client ? 'client' : 'admin';
        $token = $user->createToken('API Token', [$scope]);

        return $this->successResponse([
            'access_token' => $token->accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => $scope,
        ], 'Token rafraîchi avec succès');
    }

    /**
     * Déconnexion
     *
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Déconnexion",
     *     description="Révoque tous les tokens de l'utilisateur",
     *     operationId="logout",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            // Révoquer tous les tokens de l'utilisateur
            $user->tokens()->delete();
        }

        // Supprimer le cookie refresh_token
        $cookie = cookie()->forget('refresh_token');

        return $this->successResponse(null, 'Déconnexion réussie')->withCookie($cookie);
    }
}