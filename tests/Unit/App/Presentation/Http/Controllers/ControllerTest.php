<?php

namespace Tests\Unit\App\Presentation\Http\Controllers;

use AlanGiacomin\LaravelCqrs\App\Application\Commands\SyncCommand;
use AlanGiacomin\LaravelCqrs\App\Presentation\Http\Controllers\Controller;
use AlanGiacomin\LaravelCqrs\Infrastructure\Bus\MessageBus;
use AlanGiacomin\LaravelCqrs\Infrastructure\Routing\LocalizedRouteGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ControllerTest extends TestCase
{
    public function test_execute_dispatches_sync_command_through_bus(): void
    {
        Bus::fake();
        $command = new TestControllerSyncCommand('done');
        app()->instance(MessageBus::class, new MessageBus());

        $controller = new TestController();

        $this->assertSame('done', $controller->execute($command));
        Bus::assertDispatchedSync(TestControllerSyncCommand::class);
    }

    public function test_flash_success_returns_redirect_with_session_message(): void
    {
        new TestController()->flashSuccess('created');

        $this->assertSame('created', session('success'));
    }

    public function test_spa_redirect_redirects_to_intended_route(): void
    {
        URL::useOrigin('http://localhost');

        $controller = new TestController();
        $spa = $controller->spaRedirect('/target');

        $this->assertSame('http://localhost/target', $spa->getTargetUrl());
    }

    public function test_hard_redirect_returns_inertia_location_response(): void
    {
        URL::useOrigin('http://localhost');
        Request::macro('inertia', fn (): bool => (bool) $this->header('X-Inertia'));
        request()->headers->set('X-Inertia', 'true');

        $response = new TestController()->hardRedirect('/target');

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame('http://localhost/target', $response->headers->get('X-Inertia-Location'));
    }

    public function test_route_generator_is_resolved_from_container(): void
    {
        $generator = new LocalizedRouteGenerator();
        app()->instance(LocalizedRouteGenerator::class, $generator);

        $controller = new TestController();

        $this->assertSame($generator, $controller->exposedRouteGenerator());
    }
}

class TestController extends Controller
{
    public function exposedRouteGenerator(): LocalizedRouteGenerator
    {
        return $this->routeGenerator();
    }
}

class TestControllerSyncCommand extends SyncCommand
{
    public function __construct(private readonly mixed $response) {}

    public function getResponse(): mixed
    {
        return $this->response;
    }
}
