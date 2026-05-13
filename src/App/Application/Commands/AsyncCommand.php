<?php

namespace AlanGiacomin\LaravelCqrs\App\Application\Commands;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

abstract class AsyncCommand extends Command implements ShouldQueue
{
    use Queueable;
}
