<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception spécifique pour les erreurs liées à l'API OpenRouter
 */
class OpenRouterException extends Exception
{
    protected $statusCode;

    public function __construct($message, $statusCode = 0, ?Exception $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Retourne le code de statut HTTP associé à l'erreur
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
