<?php

namespace AlanGiacomin\LaravelCqrs\Infrastructure\Bus;

use AlanGiacomin\LaravelCqrs\App\Application\Commands\AsyncCommand;
use AlanGiacomin\LaravelCqrs\App\Application\Commands\Command;
use AlanGiacomin\LaravelCqrs\App\Application\Commands\SyncCommand;
use Illuminate\Support\Facades\Bus;

final class MessageBus
{
    public function dispatch(Command $command): mixed
    {
        if ($command instanceof AsyncCommand) {
            Bus::dispatch($command);

            return null;
        }

        Bus::dispatchSync($command);

        if ($command instanceof SyncCommand) {
            return $command->getResponse();
        }

        return null;
    }
}
