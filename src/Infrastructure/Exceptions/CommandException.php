<?php

namespace AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions;

use Exception;

class CommandException extends Exception
{
    public function getStatusCode(): int
    {
        return 500;
    }
}
