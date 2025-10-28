<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Admin;
use App\Traits\ApiResponseTrait;

class SetupController extends Controller
{
    use ApiResponseTrait;

    /**
     * Créer un admin de test (temporaire)
     *
     * @OA\Post(
     *     path="/api/v1/setup-admin",
     *     summary="Créer un admin de test (temporaire)",
     *     description="Crée un administrateur par défaut pour les tests. Cette route sera supprimée en production.",
     *     operationId="setupAdmin",
     *     tags={"Administration"},
     *     @OA\Response(
     *         response=201,
     *         description="Admin créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Admin créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="email", type="string", example="admin@test.com"),
     *                 @OA\Property(property="password", type="string", example="admin123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Un admin existe déjà",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Un admin existe déjà")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function setupAdmin(Request $request): JsonResponse
    {
        try {
            // Vérifier si un admin existe déjà
            if (Admin::count() > 0) {
                return $this->errorResponse('Un admin existe déjà', 400);
            }

            // Créer l'admin
            $admin = Admin::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'nom' => 'Admin',
                'prenom' => 'Super',
                'email' => 'admin@test.com',
                'password_temp' => bcrypt('admin123'),
                'type_user' => 'admin'
            ]);

            return $this->successResponse([
                'email' => $admin->email,
                'password' => 'admin123'
            ], 'Admin créé avec succès', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la création de l\'admin', 500);
        }
    }
}