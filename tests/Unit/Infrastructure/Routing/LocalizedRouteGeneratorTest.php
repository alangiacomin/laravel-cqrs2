<?php

namespace Tests\Unit\Infrastructure\Routing;

use AlanGiacomin\LaravelCqrs\Infrastructure\Routing\LocalizedRouteGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Mockery;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class LocalizedRouteGeneratorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @throws ReflectionException
     */
    public function test_resolve_route_name_adds_localized_prefix_when_locale_exists(): void
    {
        app()->setLocale('it');

        $name = $this->invoke(new LocalizedRouteGenerator(), 'resolveRouteName', ['posts.show']);

        $this->assertSame('localized.posts.show', $name);
    }

    /**
     * @throws ReflectionException
     */
    public function test_resolve_route_name_returns_plain_name_without_locale(): void
    {
        $generator = new class() extends LocalizedRouteGenerator
        {
            protected function locale(): ?string
            {
                return null;
            }
        };

        $name = $this->invoke($generator, 'resolveRouteName', ['posts.show']);

        $this->assertSame('posts.show', $name);
    }

    /**
     * @throws ReflectionException
     */
    public function test_route_throws_when_named_route_is_missing(): void
    {
        $this->expectException(RuntimeException::class);

        $this->invoke(new LocalizedRouteGenerator(), 'resolveParameters', ['really.missing.route', []]);
    }

    public function test_route_generates_localized_url_with_filtered_parameters(): void
    {
        $urlGenerator = Mockery::mock();
        $urlGenerator->shouldReceive('route')
            /** @phpstan-ignore-next-line */
            ->once()
            ->with('posts.show', ['post' => 15], true)
            ->andReturn('http://localhost/posts/15');
        app()->instance('url', $urlGenerator);

        $generator = new class() extends LocalizedRouteGenerator
        {
            protected function resolveRouteName(string $name): string
            {
                return $name;
            }

            protected function resolveParameters(string $name, array $params): array
            {
                return ['post' => $params['post']];
            }
        };

        $url = $generator->route('posts.show', ['post' => 15, 'ignored' => 'nope']);

        $this->assertSame('http://localhost/posts/15', $url);
    }

    public function test_signed_route_generates_temporary_signed_url(): void
    {
        $expiration = Carbon::now()->addMinutes(10);

        URL::shouldReceive('temporarySignedRoute')
            ->once()
            ->with('localized.download', $expiration, ['locale' => 'it', 'file' => 'report'])
            ->andReturn('http://localhost/signed');

        $generator = new class() extends LocalizedRouteGenerator
        {
            protected function resolveRouteName(string $name): string
            {
                return "localized.$name";
            }

            protected function resolveParameters(string $name, array $params): array
            {
                return ['locale' => 'it', 'file' => $params['file']];
            }
        };

        $url = $generator->signedRoute('download', $expiration, ['file' => 'report']);

        $this->assertSame('http://localhost/signed', $url);
    }

    /**
     * @throws ReflectionException
     */
    public function test_resolve_parameters_keeps_only_route_parameters_and_injects_locale(): void
    {
        $route = new class()
        {
            /** @noinspection PhpUnused */
            public function parameterNames(): array
            {
                return ['locale', 'post'];
            }
        };

        Route::shouldReceive('getRoutes->getByName')
            ->once()
            ->with('localized.posts.show')
            ->andReturn($route);

        $generator = new class() extends LocalizedRouteGenerator
        {
            protected function locale(): string
            {
                return 'it';
            }
        };

        $params = $this->invoke($generator, 'resolveParameters', ['posts.show', ['post' => 7, 'ignored' => 'x']]);

        $this->assertSame(['locale' => 'it', 'post' => 7], $params);
    }

    /**
     * @throws ReflectionException
     */
    public function test_locale_prefers_request_route_parameter_over_app_locale(): void
    {
        app()->setLocale('en');
        Route::get('{locale}/ping', fn () => 'ok')->name('localized.ping');
        $this->get('/it/ping');

        $locale = $this->invoke(new LocalizedRouteGenerator(), 'locale');

        $this->assertSame('it', $locale);
    }

    /**
     * @throws ReflectionException
     */
    private function invoke(object $target, string $method, array $args = []): mixed
    {
        $reflection = new ReflectionMethod($target, $method);

        return $reflection->invokeArgs($target, $args);
    }
}
