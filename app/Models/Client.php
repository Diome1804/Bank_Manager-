<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'nci',
        'date_naissance',
        'adresse',
        'type_user',
        'password_temp',
        'code_verification',
    ];

    protected $casts = [
        'salaire_mensuel' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comptes(): HasMany
    {
        return $this->hasMany(Compte::class);
    }
}
