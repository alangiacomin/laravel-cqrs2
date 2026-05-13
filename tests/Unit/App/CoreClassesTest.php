<?php

namespace Tests\Unit\App;

use AlanGiacomin\LaravelCqrs\App\Application\Commands\SyncCommand;
use PHPUnit\Framework\TestCase;

class CoreClassesTest extends TestCase
{
    public function test_sync_command_can_expose_response(): void
    {
        $command = new TestSyncBaseCommand(123);

        $this->assertSame(123, $command->getResponse());
    }
}

class TestSyncBaseCommand extends SyncCommand
{
    public function __construct(private readonly mixed $response) {}

    public function getResponse(): mixed
    {
        return $this->response;
    }
}
