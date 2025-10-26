<?php

namespace App\Services;

use App\Models\Compte;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompteQueryService
{
    /**
     * Applique les filtres à la requête des comptes
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        // Filtre par type
        if (isset($filters['type']) && $filters['type']) {
            $type = $this->normalizeType($filters['type']);
            $query->where('type_compte', $type);
        }

        // Filtre par statut
        if (isset($filters['statut']) && $filters['statut']) {
            $query->where('statut', $filters['statut']);
        }

        // Filtre par recherche
        if (isset($filters['search']) && $filters['search']) {
            $this->applySearchFilter($query, $filters['search']);
        }

        return $query;
    }

    /**
     * Applique le tri à la requête
     */
    public function applySorting(Builder $query, ?string $sortField, string $sortOrder = 'desc'): Builder
    {
        if (!$sortField) {
            return $query->orderBy('date_ouverture', $sortOrder);
        }

        switch ($sortField) {
            case 'dateCreation':
                return $query->orderBy('date_ouverture', $sortOrder);
            case 'solde':
                return $query->orderBy('solde', $sortOrder);
            case 'titulaire':
                return $this->applyTitulaireSorting($query, $sortOrder);
            default:
                return $query->orderBy('date_ouverture', $sortOrder);
        }
    }

    /**
     * Applique la pagination
     */
    public function applyPagination(Builder $query, int $perPage = 10): LengthAwarePaginator
    {
        return $query->paginate(min($perPage, 100));
    }

    /**
     * Construit la requête complète avec tous les filtres et tri
     */
    public function buildQuery(array $filters = [], ?string $sortField = null, string $sortOrder = 'desc'): Builder
    {
        $query = Compte::with('client');

        $query = $this->applyFilters($query, $filters);
        $query = $this->applySorting($query, $sortField, $sortOrder);

        return $query;
    }

    /**
     * Normalise le type de compte (cheque -> courant)
     */
    private function normalizeType(string $type): string
    {
        return $type === 'cheque' ? 'courant' : $type;
    }

    /**
     * Applique le filtre de recherche
     */
    private function applySearchFilter(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('numero_compte', 'like', "%{$search}%")
              ->orWhereHas('client', function ($clientQuery) use ($search) {
                  $clientQuery->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Applique le tri par titulaire
     */
    private function applyTitulaireSorting(Builder $query, string $sortOrder): Builder
    {
        return $query->join('clients', 'comptes.client_id', '=', 'clients.id')
                    ->orderBy('clients.nom', $sortOrder)
                    ->orderBy('clients.prenom', $sortOrder)
                    ->select('comptes.*');
    }
}