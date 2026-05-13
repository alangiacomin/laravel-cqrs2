<?php

namespace Tests\Unit\Infrastructure\Bus;

use AlanGiacomin\LaravelCqrs\App\Application\Commands\AsyncCommand;
use AlanGiacomin\LaravelCqrs\App\Application\Commands\Command;
use AlanGiacomin\LaravelCqrs\App\Application\Commands\SyncCommand;
use AlanGiacomin\LaravelCqrs\Infrastructure\Bus\MessageBus;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class MessageBusTest extends TestCase
{
    public function test_dispatch_async_command_uses_async_bus_and_returns_null(): void
    {
        Bus::fake();

        $command = new TestAsyncCommand();
        $result = new MessageBus()->dispatch($command);

        Bus::assertDispatched(TestAsyncCommand::class);
        $this->assertNull($result);
    }

    public function test_dispatch_sync_command_returns_command_response(): void
    {
        Bus::fake();

        $command = new TestSyncCommand('ok');
        $result = new MessageBus()->dispatch($command);

        Bus::assertDispatchedSync(TestSyncCommand::class);
        $this->assertSame('ok', $result);
    }

    public function test_dispatch_plain_command_returns_null(): void
    {
        Bus::fake();

        $command = new TestPlainCommand();
        $result = new MessageBus()->dispatch($command);

        Bus::assertDispatchedSync(TestPlainCommand::class);
        $this->assertNull($result);
    }
}

class TestPlainCommand extends Command {}

class TestAsyncCommand extends AsyncCommand {}

class TestSyncCommand extends SyncCommand
{
    public function __construct(private readonly mixed $response) {}

    public function getResponse(): mixed
    {
        return $this->response;
    }
}
