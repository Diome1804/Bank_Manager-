<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;

class CompteException extends Exception
{
    use ApiResponseTrait;

    protected $statusCode;
    protected $errors;

    public function __construct(string $message = 'Erreur liée aux comptes', int $statusCode = 400, array $errors = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function render(): JsonResponse
    {
        return $this->errorResponse($this->getMessage(), $this->statusCode, $this->errors);
    }

    public static function compteNotFound(string $numero = null): self
    {
        $message = $numero ? "Compte avec numéro {$numero} non trouvé" : "Compte non trouvé";
        return new self($message, 404);
    }

    public static function compteBloque(string $numero): self
    {
        return new self("Le compte {$numero} est bloqué", 403);
    }

    public static function clientNotFound(string $telephone): self
    {
        return new self("Client avec téléphone {$telephone} non trouvé", 404);
    }

    public static function unauthorized(): self
    {
        return new self("Accès non autorisé aux comptes", 403);
    }
}
