<?php

namespace Tests\Unit\Infrastructure\Exceptions;

use AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions\BadRequestException;
use AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions\CommandException;
use AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions\ForbiddenException;
use AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions\NotFoundException;
use AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions\UnauthorizedException;
use AlanGiacomin\LaravelCqrs\Infrastructure\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    public function test_http_like_status_codes_are_correct(): void
    {
        $this->assertSame(400, new BadRequestException()->getStatusCode());
        $this->assertSame(500, new CommandException()->getStatusCode());
        $this->assertSame(403, new ForbiddenException()->getStatusCode());
        $this->assertSame(404, new NotFoundException()->getStatusCode());
        $this->assertSame(401, new UnauthorizedException()->getStatusCode());
        $this->assertSame(422, new ValidationException()->getStatusCode());
    }

    public function test_validation_exception_keeps_errors_payload_and_message(): void
    {
        $exception = new ValidationException('Invalid payload', ['name' => ['Required']]);

        $this->assertSame('Invalid payload', $exception->getMessage());
        $this->assertSame(['name' => ['Required']], $exception->errors);
    }
}
