<?php

namespace AlanGiacomin\LaravelCqrs;

use AlanGiacomin\LaravelCqrs\App\Domain\Repositories\IRepository;
use Composer\InstalledVersions;
use Exception;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use ReflectionNamedType;

class LaravelCqrsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        AboutCommand::add(
            'Laravel CQRS',
            fn () => [
                'Version' => InstalledVersions::getPrettyVersion('alangiacomin/laravel-cqrs'),
            ]
        );

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                ]
            );
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/typescript-transformer.php',
            'typescript-transformer'
        );

        if (config('architecture.events.auto_discover', false)) {
            $this->autoDiscoverListeners();
        }

        if (config('architecture.repositories.auto_discover', false)) {
            $this->autoDiscoverRepositories();
        }
    }

    /**
     * Auto-discover and register listeners from Areas.
     */
    protected function autoDiscoverListeners(): void
    {
        $areas = config('architecture.areas', []);

        foreach ($areas as $area) {
            $basePath = app_path("Areas/$area");

            $pattern = "$basePath/*/{Domain,Infrastructure}/Listeners/*/*.php";
            $files = glob($pattern, GLOB_BRACE);

            foreach ($files as $file) {
                $class = $this->pathToClass($file);

                if (!$class || !class_exists($class)) {
                    continue;
                }

                $this->registerListenerForEvent($class);
            }
        }
    }

    /**
     * Convert the file path to a fully qualified class name.
     */
    protected function pathToClass(string $path): ?string
    {
        $appPath = app_path();

        // Rimuovi il path dell'app
        $relativePath = str_replace($appPath.'/', '', $path);

        // Rimuovi .php
        $relativePath = str_replace('.php', '', $relativePath);

        // Converti path in namespace
        return 'App\\'.str_replace('/', '\\', $relativePath);
    }

    /**
     * Register a listener by inspecting its handle method.
     */
    protected function registerListenerForEvent(string $listenerClass): void
    {
        try {
            $reflection = new ReflectionClass($listenerClass);

            if (!$reflection->hasMethod('handle')) {
                return;
            }

            $handleMethod = $reflection->getMethod('handle');
            $parameters = $handleMethod->getParameters();

            if ($parameters === []) {
                return;
            }

            $eventType = $parameters[0]->getType();

            if (!$eventType instanceof ReflectionNamedType) {
                return;
            }

            if ($eventType->isBuiltin()) {
                return;
            }

            $eventClass = $eventType->getName();

            Event::listen($eventClass, $listenerClass);
        } catch (Exception) {
            // Silently skip problematic classes
        }
    }

    /**
     * Auto-discover and register repositories from Areas.
     */
    protected function autoDiscoverRepositories(): void
    {
        $shouldCache = config('architecture.repositories.cache_bindings')
            && !$this->app->environment('local', 'testing');

        if ($shouldCache) {
            $bindings = $this->getCachedBindings();
        } else {
            $bindings = $this->discoverRepositoryBindings();
        }

        // Registra tutti i bindings
        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Get cached repository bindings or discover and cache them.
     *
     * @return array<string, string>
     */
    protected function getCachedBindings(): array
    {
        $cacheKey = config('architecture.repositories.cache_key');
        $cacheTtl = config('architecture.repositories.cache_ttl');

        return cache()->remember($cacheKey, $cacheTtl, function () {
            return $this->discoverRepositoryBindings();
        });
    }

    /**
     * Discover all repository bindings.
     *
     * @return array<string, string> Array with interface => implementation mappings
     */
    protected function discoverRepositoryBindings(): array
    {
        $bindings = [];
        $areas = config('architecture.areas', []);

        // scansiona le Areas
        foreach ($areas as $area) {
            $basePath = app_path("Areas/$area");
            $pattern = "$basePath/*/Infrastructure/Repositories/*Repository.php";

            $areaBindings = $this->scanRepositoryFiles(glob($pattern));
            $bindings = array_merge($bindings, $areaBindings);
        }

        // scansiona App\Infrastructure\Repositories
        $infraPattern = app_path('Infrastructure/Repositories/*Repository.php');
        $infraBindings = $this->scanRepositoryFiles(glob($infraPattern));

        return array_merge($bindings, $infraBindings);
    }

    /**
     * Scan repository files and extract interface-implementation mappings.
     *
     * @param  array<int, string>  $files
     * @return array<string, string>
     */
    protected function scanRepositoryFiles(array $files): array
    {
        $bindings = [];

        foreach ($files as $file) {
            $className = $this->pathToClass($file);

            if (!$className || !class_exists($className)) {
                continue;
            }

            $interfaces = $this->extractRepositoryInterfaces($className);

            foreach ($interfaces as $interface) {
                $bindings[$interface] = $className;
            }
        }

        return $bindings;
    }

    /**
     * Extract repository interfaces from a class.
     *
     * @return array<int, string>
     */
    protected function extractRepositoryInterfaces(string $repositoryClass): array
    {
        $interfaces = [];

        try {
            $reflection = new ReflectionClass($repositoryClass);
            $classInterfaces = $reflection->getInterfaces();

            foreach ($classInterfaces as $interface) {
                // Includi solo interfacce che estendono IRepository (escluso IRepository stesso)
                if ($interface->implementsInterface(IRepository::class) &&
                    $interface->getName() !== IRepository::class) {
                    $interfaces[] = $interface->getName();
                }
            }
        } catch (Exception $e) {
            // Log solo in development
            if ($this->app->environment('local')) {
                logger()->warning("Failed to extract interfaces from: $repositoryClass", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $interfaces;
    }
}
