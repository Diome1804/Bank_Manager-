<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\HasApiTokens;

class Admin extends Model
{
    use HasFactory, HasApiTokens;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'role',
        'type_user',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
