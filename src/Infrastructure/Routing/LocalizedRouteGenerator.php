<?php

namespace AlanGiacomin\LaravelCqrs\Infrastructure\Routing;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class LocalizedRouteGenerator
{
    public function route(string $name, array $params = []): string
    {
        return route(
            $this->resolveRouteName($name),
            $this->resolveParameters($name, $params)
        );
    }

    public function signedRoute(
        string $name,
        Carbon $expiration,
        array $params = []
    ): string {
        return URL::temporarySignedRoute(
            $this->resolveRouteName($name),
            $expiration,
            $this->resolveParameters($name, $params)
        );
    }

    protected function resolveRouteName(string $name): string
    {
        $locale = $this->locale();

        return $locale ? "localized.$name" : $name;
    }

    protected function locale(): ?string
    {
        return request()->route('locale')
            ?: app()->getLocale();
    }

    /* ------------------------- */

    protected function resolveParameters(string $name, array $params): array
    {
        $routeName = $this->resolveRouteName($name);
        $route = Route::getRoutes()->getByName($routeName);

        if (!$route) {
            throw new RuntimeException(__('error.route_not_found', ['route' => $routeName]));
        }

        $resolved = [];
        $locale = $this->locale();

        foreach ($route->parameterNames() as $parameter) {
            if ($parameter === 'locale') {
                if ($locale) {
                    $resolved['locale'] = $locale;
                }

                continue;
            }

            if (array_key_exists($parameter, $params)) {
                $resolved[$parameter] = $params[$parameter];
            }
        }

        return $resolved;
    }
}
