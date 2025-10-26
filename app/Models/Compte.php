<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compte extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'numero_compte',
        'solde',
        'type_compte',
        'date_ouverture',
        'statut',
        'client_id',
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'date_ouverture' => 'date',
        'date_fermeture' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope global pour récupérer uniquement les comptes non supprimés
     */
    protected static function booted()
    {
        static::addGlobalScope('nonSupprimes', function ($builder) {
            $builder->whereNull('deleted_at');
        });
    }

    /**
     * Scope local pour récupérer un compte par son numéro
     */
    public function scopeNumero($query, $numero)
    {
        return $query->where('numero_compte', $numero);
    }

    /**
     * Scope local pour récupérer les comptes d'un client basé sur le téléphone
     */
    public function scopeClient($query, $telephone)
    {
        return $query->whereHas('client', function ($q) use ($telephone) {
            $q->where('telephone', $telephone);
        });
    }

    /**
     * Scope pour appliquer les filtres de requête
     */
    public function scopeApplyFilters($query, array $filters)
    {
        return $query->when($filters['type'] ?? null, function ($q, $type) {
            return $q->where('type_compte', $type);
        })->when($filters['statut'] ?? null, function ($q, $statut) {
            return $q->where('statut', $statut);
        })->when($filters['search'] ?? null, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('numero_compte', 'like', "%{$search}%")
                      ->orWhereHas('client', function ($clientQuery) use ($search) {
                          $clientQuery->where('nom', 'like', "%{$search}%")
                                    ->orWhere('prenom', 'like', "%{$search}%");
                      });
            });
        });
    }

    /**
     * Scope pour appliquer le tri
     */
    public function scopeApplySorting($query, ?string $sortField = null, ?string $sortOrder = 'desc')
    {
        $sortField = $sortField ?: 'date_ouverture';

        return match ($sortField) {
            'dateCreation' => $query->orderBy('date_ouverture', $sortOrder),
            'solde' => $query->orderBy('solde', $sortOrder),
            'titulaire' => $query->join('clients', 'comptes.client_id', '=', 'clients.id')
                                ->orderBy('clients.nom', $sortOrder)
                                ->orderBy('clients.prenom', $sortOrder)
                                ->select('comptes.*'),
            default => $query->orderBy('date_ouverture', $sortOrder),
        };
    }

    /**
     * Scope pour appliquer la pagination avec limite
     */
    public function scopeApplyPagination($query, int $perPage = 10)
    {
        return $query->paginate(min($perPage, 100));
    }

    /**
     * Attribut calculé pour le solde (temporairement = soldeInitial)
     * TODO: Phase ultérieure - Calcul réel basé sur les transactions
     */
    public function getSoldeCalculeAttribute(): float
    {
        // Pour l'instant, retourner le soldeInitial
        // Plus tard: Somme dépôts - somme retraits
        return $this->solde;
    }
}
