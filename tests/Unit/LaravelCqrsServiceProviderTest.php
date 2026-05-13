<?php

namespace Tests\Unit;

use AlanGiacomin\LaravelCqrs\App\Domain\Repositories\IRepository;
use AlanGiacomin\LaravelCqrs\LaravelCqrsServiceProvider;
use Illuminate\Support\Facades\Event;
use ReflectionException;
use ReflectionMethod;
use Tests\TestCase;

class LaravelCqrsServiceProviderTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function test_path_to_class_converts_app_path_to_fqdn(): void
    {
        $provider = new LaravelCqrsServiceProvider(app());
        $result = $this->invoke($provider, 'pathToClass', [app_path('Areas/Blog/Foo.php')]);

        $this->assertSame('App\\Areas\\Blog\\Foo', $result);
    }

    /**
     * @throws ReflectionException
     */
    public function test_register_listener_for_event_registers_non_builtin_event_parameter(): void
    {
        Event::fake();

        $provider = new LaravelCqrsServiceProvider(app());
        $this->invoke($provider, 'registerListenerForEvent', [TestListener::class]);

        Event::assertListening(TestEvent::class, TestListener::class);
    }

    /**
     * @throws ReflectionException
     */
    public function test_extract_repository_interfaces_returns_only_interfaces_extending_repository(): void
    {
        $provider = new LaravelCqrsServiceProvider(app());

        $interfaces = $this->invoke($provider, 'extractRepositoryInterfaces', [TestRepository::class]);

        $this->assertSame([TestUserRepository::class], $interfaces);
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

class TestEvent {}

class TestListener
{
    public function handle(TestEvent $event): void {}
}

interface TestUserRepository extends IRepository {}

interface TestOtherContract {}

class TestRepository implements TestOtherContract, TestUserRepository {}
