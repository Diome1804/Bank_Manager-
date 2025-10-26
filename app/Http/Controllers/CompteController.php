<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Http\Resources\CompteResource;
use App\Http\Requests\ListComptesRequest;
use App\Http\Requests\CreateCompteRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Services\CompteQueryService;
use App\Services\CompteUpdateService;
use App\Services\CompteDeletionService;
use App\Traits\ApiResponseTrait;
use App\Exceptions\CompteException;
use Illuminate\Http\JsonResponse;

class CompteController extends Controller
{
    use ApiResponseTrait;

    protected CompteQueryService $queryService;
    protected CompteUpdateService $updateService;
    protected CompteDeletionService $deletionService;

    public function __construct(
        CompteQueryService $queryService,
        CompteUpdateService $updateService,
        CompteDeletionService $deletionService
    ) {
        $this->queryService = $queryService;
        $this->updateService = $updateService;
        $this->deletionService = $deletionService;
    }

    /**
     * Lister tous les comptes (non supprimés)
     *
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Lister tous les comptes",
     *     description="Récupère la liste de tous les comptes non supprimés avec possibilité de filtrage et pagination",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type (courant, epargne)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"courant", "epargne"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par titulaire ou numéro",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Tri (dateCreation, solde, titulaire)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"})
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Paramètres invalides"),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */

    public function index(ListComptesRequest $request): JsonResponse
    {
        try {
            logger()->info('Début de la récupération des comptes', [
                'request_params' => $request->all(),
                'app_env' => app()->environment(),
                'db_connection' => config('database.default')
            ]);

            $filters = $request->only(['type', 'statut', 'search']);
            $sortField = $request->input('sort');
            $sortOrder = $request->input('order', 'desc');
            $perPage = min($request->input('limit', 10), 100);

            logger()->info('Filtres appliqués', ['filters' => $filters]);

            $query = $this->queryService->buildQuery($filters, $sortField, $sortOrder);
            $comptes = $this->queryService->applyPagination($query, $perPage);

            logger()->info('Comptes récupérés', ['count' => $comptes->count()]);

            return $this->paginatedResponse(
                CompteResource::collection($comptes),
                'Liste des comptes récupérée avec succès'
            );

        } catch (\Exception $e) {
            logger()->error('Erreur lors de la récupération des comptes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'filters' => $request->only(['type', 'statut', 'search', 'sort', 'order'])
            ]);

            // Return a simple error response for production
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des comptes',
                'error' => $e->getMessage(), // Temporarily show full error for debugging
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }

    /**
     * Créer un nouveau compte
     *
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     summary="Créer un nouveau compte",
     *     description="Crée un nouveau compte bancaire avec génération automatique du numéro et création du client si nécessaire",
     *     operationId="createCompte",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"courant", "epargne", "cheque"}, example="cheque"),
     *             @OA\Property(property="soldeInitial", type="number", minimum=10000, example=500000),
     *             @OA\Property(property="devise", type="string", enum={"FCFA", "XOF"}, example="FCFA"),
     *             @OA\Property(property="client", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", nullable=true, description="ID du client existant"),
     *                 @OA\Property(property="nom", type="string", minLength=2, maxLength=255, description="Requis si nouveau client"),
     *                 @OA\Property(property="prenom", type="string", minLength=2, maxLength=255, description="Requis si nouveau client"),
     *                 @OA\Property(property="email", type="string", format="email", description="Requis si nouveau client"),
     *                 @OA\Property(property="telephone", type="string", description="Requis si nouveau client"),
     *                 @OA\Property(property="nci", type="string", minLength=13, maxLength=13, description="Requis si nouveau client"),
     *                 @OA\Property(property="adresse", type="string", minLength=5, maxLength=500, description="Requis si nouveau client")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="660f9511-f30c-52e5-b827-557766551111"),
     *                 @OA\Property(property="numeroCompte", type="string", example="SN241025123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Cheikh Sy"),
     *                 @OA\Property(property="type", type="string", example="cheque"),
     *                 @OA\Property(property="solde", type="number", example=500000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time"),
     *                 @OA\Property(property="statut", type="string", example="actif"),
     *                 @OA\Property(property="metadata", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données de requête invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function store(CreateCompteRequest $request): JsonResponse
    {
        try {
            // Les données sont validées par CreateCompteRequest
            // L'observer CompteObserver gère la logique métier

            $validatedData = $request->validated();

            // Créer le compte (l'observer gère la création du client si nécessaire)
            $compte = new Compte([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type_compte' => $validatedData['type'],
                'solde' => $validatedData['soldeInitial'],
                'date_ouverture' => now(),
                'statut' => 'actif',
                // Les autres champs sont gérés par l'observer
            ]);

            $compte->save();

            return $this->successResponse(
                new CompteResource($compte->load('client')),
                'Compte créé avec succès',
                201
            );

        } catch (\Exception $e) {
            // Logger l'erreur pour le debugging
            logger()->error('Erreur lors de la création du compte', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return $this->errorResponse('Erreur lors de la création du compte', 500);
        }
    }

    /**
     * Mettre à jour les informations du client d'un compte
     *
     * @OA\Patch(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Mettre à jour les informations du client",
     *     description="Met à jour les informations du client associé à un compte. Tous les champs sont optionnels mais au moins un doit être fourni.",
     *     operationId="updateClient",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte dont on veut modifier le client",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior", description="Nouveau nom complet du titulaire"),
     *             @OA\Property(property="informationsClient", type="object",
     *                 @OA\Property(property="nom", type="string", example="Diallo", description="Nouveau nom"),
     *                 @OA\Property(property="prenom", type="string", example="Amadou Junior", description="Nouveau prénom"),
     *                 @OA\Property(property="email", type="string", format="email", example="amadou.junior@example.com", description="Nouvel email (doit être unique)"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234568", description="Nouveau téléphone (doit être unique et valide)"),
     *                 @OA\Property(property="nci", type="string", example="1234567890124", description="Nouveau numéro NCI (doit être unique et valide)"),
     *                 @OA\Property(property="adresse", type="string", example="Dakar, Sénégal", description="Nouvelle adresse")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations du client mises à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *                 @OA\Property(property="type", type="string", example="epargne"),
     *                 @OA\Property(property="solde", type="number", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time"),
     *                 @OA\Property(property="statut", type="string", example="bloque"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides ou aucun champ fourni",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Au moins un champ de modification doit être fourni."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function update(UpdateClientRequest $request, Compte $compte): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $updatedCompte = $this->updateService->updateClient($compte, $validatedData);

            return $this->successResponse(
                new CompteResource($updatedCompte),
                'Compte mis à jour avec succès'
            );

        } catch (\Exception $e) {
            logger()->error('Erreur lors de la mise à jour du compte', [
                'error' => $e->getMessage(),
                'compte_id' => $compte->id,
                'request_data' => $request->all()
            ]);

            return $this->errorResponse('Erreur lors de la mise à jour du compte', 500);
        }
    }

    /**
     * Récupérer un compte spécifique
     *
     * @OA\Get(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Récupérer un compte spécifique",
     *     description="Récupère les détails d'un compte spécifique par son ID",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte à récupérer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
     *                 @OA\Property(property="type", type="string", example="epargne"),
     *                 @OA\Property(property="solde", type="number", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time"),
     *                 @OA\Property(property="statut", type="string", example="bloque"),
     *                 @OA\Property(property="motifBlocage", type="string", nullable=true),
     *                 @OA\Property(property="metadata", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="compteId", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function show(Compte $compte): JsonResponse
    {
        try {
            // Vérifier si le compte existe (le model binding Laravel le fait automatiquement)
            // mais on peut ajouter une vérification supplémentaire si nécessaire

            return $this->successResponse(
                new CompteResource($compte->load('client')),
                'Compte récupéré avec succès'
            );

        } catch (\Exception $e) {
            // Logger l'erreur pour le debugging
            logger()->error('Erreur lors de la récupération du compte', [
                'error' => $e->getMessage(),
                'compte_id' => $compte->id ?? null
            ]);

            return $this->errorResponse('Erreur lors de la récupération du compte', 500);
        }
    }

    /**
     * Supprimer un compte (soft delete)
     *
     * @OA\Delete(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Supprimer un compte",
     *     description="Effectue un soft delete du compte en changeant son statut à 'ferme' et en enregistrant la date de fermeture",
     *     operationId="deleteCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte à supprimer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="statut", type="string", example="ferme"),
     *                 @OA\Property(property="dateFermeture", type="string", format="date-time", example="2025-10-19T11:15:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Impossible de supprimer le compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Impossible de supprimer un compte avec un solde positif")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function destroy(Compte $compte): JsonResponse
    {
        try {
            $deletionData = $this->deletionService->delete($compte);

            return $this->successResponse($deletionData, 'Compte supprimé avec succès');

        } catch (\Exception $e) {
            logger()->error('Erreur lors de la suppression du compte', [
                'error' => $e->getMessage(),
                'compte_id' => $compte->id ?? null
            ]);

            return $this->errorResponse('Erreur lors de la suppression du compte', 500);
        }
    }
}
