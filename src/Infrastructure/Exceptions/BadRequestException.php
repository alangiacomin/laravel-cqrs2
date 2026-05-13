<?php

namespace AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    public function getStatusCode(): int
    {
        return 400;
    }
}
