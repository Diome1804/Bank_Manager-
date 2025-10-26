<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Format de réponse standard pour les succès
     */
    protected function successResponse($data = null, string $message = 'Opération réussie', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Format de réponse standard pour les erreurs
     */
    protected function errorResponse(string $message = 'Une erreur est survenue', int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Format de réponse paginée
     */
    protected function paginatedResponse($data, string $message = 'Données récupérées avec succès'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'currentPage' => $data->currentPage(),
                'totalPages' => $data->lastPage(),
                'totalItems' => $data->total(),
                'itemsPerPage' => $data->perPage(),
                'hasNext' => $data->hasMorePages(),
                'hasPrevious' => $data->currentPage() > 1
            ],
            'links' => [
                'self' => $data->url($data->currentPage()),
                'next' => $data->nextPageUrl(),
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage())
            ]
        ]);
    }
}