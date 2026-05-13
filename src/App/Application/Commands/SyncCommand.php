<?php

namespace AlanGiacomin\LaravelCqrs\App\Application\Commands;

abstract class SyncCommand extends Command
{
    /**
     * @codeCoverageIgnore
     */
    abstract public function getResponse(): mixed;
}
