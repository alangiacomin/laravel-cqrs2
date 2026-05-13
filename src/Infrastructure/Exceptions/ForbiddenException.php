<?php

namespace AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions;

use Exception;

class ForbiddenException extends Exception
{
    public function getStatusCode(): int
    {
        return 403;
    }
}
