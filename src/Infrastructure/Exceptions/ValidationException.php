<?php

namespace AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public array $errors = [];

    public function __construct(
        string $message = 'Validation failed',
        array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        return 422;
    }
}
