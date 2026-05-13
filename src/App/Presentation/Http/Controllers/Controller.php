<?php

namespace AlanGiacomin\LaravelCqrs\App\Presentation\Http\Controllers;

use AlanGiacomin\LaravelCqrs\App\Application\Commands\SyncCommand;
use AlanGiacomin\LaravelCqrs\Infrastructure\Bus\MessageBus;
use AlanGiacomin\LaravelCqrs\Infrastructure\Routing\LocalizedRouteGenerator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    use AuthorizesRequests;

    public function execute(SyncCommand $command): mixed
    {
        return $this->bus()->dispatch($command);
    }

    public function flashSuccess(mixed $returnValue): RedirectResponse
    {
        return back()->with('success', $returnValue);
    }

    public function spaRedirect(string $route): RedirectResponse
    {
        return redirect()->intended($route);
    }

    public function hardRedirect(string $route): Response
    {
        return Inertia::location(redirect()->intended($route)->getTargetUrl());
    }

    protected function bus(): MessageBus
    {
        return app(MessageBus::class);
    }

    protected function routeGenerator(): LocalizedRouteGenerator
    {
        return app(LocalizedRouteGenerator::class);
    }
}
