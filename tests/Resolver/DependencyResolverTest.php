<?php
/**
 * @copyright Copyright (c) 2020 ACTINEO GmbH.
 */

namespace Fanmade\ServiceBinding\Tests\Resolver;

use Fanmade\ServiceBinding\Resolver\DependencyResolver;
use Fanmade\ServiceBinding\Resolver\InvalidConfigurationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Mockery;
use PHPUnit\Framework\TestCase;

interface TestInterface
{

}

interface TestInterfaceRequiringArguments
{
    public function __construct(string $foo, array $bar);
}

class TestClass implements TestInterface
{
    public function __construct()
    {
    }
}

class TestClassRequiringArguments implements TestInterfaceRequiringArguments
{
    public function __construct(string $foo, array $bar)
    {
    }
}

class DependencyResolverTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_resolve_does_handle_usage_of_reserved_key()
    {
        $app = Mockery::mock(Application::class);
        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'use' => 'use',
                        'eloquent' => new class {
                        }
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_missing_use()
    {
        $app = Mockery::mock(Application::class);
        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'eloquent' => new class {
                        }
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_missing_interface()
    {
        $app = Mockery::mock(Application::class);
        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'use' => 'eloquent',
                        'eloquent' => new class {
                        }
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_invalid_interface()
    {
        $app = Mockery::mock(Application::class);
        /** @noinspection PhpUndefinedClassInspection */
        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => Test_Interface_Ok_A::class,
                        'use' => 'eloquent',
                        'eloquent' => new class {
                        }
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_unset_use()
    {
        $app = Mockery::mock(Application::class);
        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'use' => 'eloquent',
                        'foo' => new class {
                        }
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_invalid_binding_type()
    {
        $app = Mockery::mock(Application::class);
        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'type' => 'eloquent',
                        'use' => 'eloquent',
                        'eloquent' => new class {
                        },
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_empty_config()
    {
        $app = Mockery::mock(Application::class);
        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'type' => 'eloquent',
                        'use' => 'eloquent',
                        'eloquent' => [],
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_unresolvable_config()
    {
        $app = Mockery::mock(Application::class);
        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'type' => 'eloquent',
                        'use' => 'eloquent',
                        'eloquent' => [
                            'foo' => 'bar',
                        ],
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_bind_class()
    {
        $cls = new class () {
        };
        $app = Mockery::mock(Application::class);

        $app->shouldReceive('bind')->withArgs(
            function ($interface, $closure) use ($cls, $app) {
                $this->assertTrue(is_callable($closure));
                $resolved = $closure($app);
                $this->assertEquals($cls, $resolved);
                $this->assertEquals(TestInterface::class, $interface);
                return true;
            }
        );

        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'use' => 'eloquent',
                        'eloquent' => [
                            'class' => $cls,
                        ],
                    ]
                ]
            ]
        ];
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_class_arguments()
    {
        $app = Mockery::mock(Application::class);

        $app->shouldReceive('bind')->withArgs(
            function ($interface, $closure) use ($app) {
                $this->assertTrue(is_callable($closure));
                $resolved = $closure($app);
                $this->assertTrue(Mockery::type(TestClassRequiringArguments::class)->match($resolved));
                $this->assertEquals(TestInterface::class, $interface);
                return true;
            }
        );

        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'use' => 'eloquent',
                        'eloquent' => [
                            'class' => TestClassRequiringArguments::class,
                            'arguments' => [
                                'foo' => 'foo',
                                'bar' => ['hey' => 'ho'],
                            ]
                        ],
                    ]
                ]
            ]
        ];
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_direct_class_name()
    {
        $app = Mockery::mock(Application::class);

        $app->shouldReceive('bind')->withArgs(
            function ($interface, $closure) use ($app) {
                $this->assertTrue(is_callable($closure));
                $resolved = $closure($app);
                $this->assertTrue(Mockery::type(TestClass::class)->match($resolved));
                $this->assertEquals(TestInterface::class, $interface);
                return true;
            }
        );

        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'use' => 'eloquent',
                        'eloquent' => TestClass::class,
                    ]
                ]
            ]
        ];
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_class_name()
    {
        $app = Mockery::mock(Application::class);

        $app->shouldReceive('bind')->withArgs(
            function ($interface, $closure) use ($app) {
                $this->assertTrue(is_callable($closure));
                $resolved = $closure($app);
                $this->assertTrue(Mockery::type(TestClass::class)->match($resolved));
                $this->assertEquals(TestInterface::class, $interface);
                return true;
            }
        );

        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'use' => 'eloquent',
                        'eloquent' => [
                            'class' => TestClass::class
                        ],
                    ]
                ]
            ]
        ];
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_unresolvable_interface()
    {
        $app = Mockery::mock(Application::class);

        $app->shouldReceive('bind')->withArgs(
            function ($interface, $closure) use ($app) {
                $this->assertTrue(is_callable($closure));
                $resolved = $closure($app);
                $this->assertTrue(Mockery::type(TestClass::class)->match($resolved));
                $this->assertEquals(TestInterface::class, $interface);
                return true;
            }
        );

        $app->shouldReceive('make')->withArgs(
            function ($interface) use ($app) {
                $this->assertEquals(TestInterfaceRequiringArguments::class, $interface);
                return true;
            }
        )->andThrow(BindingResolutionException::class);

        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'use' => 'eloquent',
                        'eloquent' => [
                            'class' => TestClassRequiringArguments::class,
                            'arguments' => [
                                'foo' => 'foo',
                                [
                                    'interface' => TestInterfaceRequiringArguments::class,
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

    public function test_resolve_does_handle_unresolvable_interface_with_arguments()
    {
        $app = Mockery::mock(Application::class);

        $app->shouldReceive('bind')->withArgs(
            function ($interface, $closure) use ($app) {
                $this->assertTrue(is_callable($closure));
                $resolved = $closure($app);
                $this->assertTrue(Mockery::type(TestClass::class)->match($resolved));
                $this->assertEquals(TestInterface::class, $interface);
                return true;
            }
        );

        $app->shouldReceive('makeWith')->withArgs(
            function ($interface, $arguments) use ($app) {
                $this->assertEquals(TestInterfaceRequiringArguments::class, $interface);
                $this->assertEquals(
                    [
                        'foo',
                        ['boo' => 'bar']
                    ],
                    $arguments
                );
                return true;
            }
        )->andThrow(BindingResolutionException::class);

        $config = [
            'bindings' => [
                'foo' => [
                    'bar' => [
                        'interface' => TestInterface::class,
                        'use' => 'eloquent',
                        'eloquent' => [
                            'class' => TestClassRequiringArguments::class,
                            'arguments' => [
                                'foo' => 'foo',
                                [
                                    'interface' => TestInterfaceRequiringArguments::class,
                                    'arguments' => [
                                        'foo' => 'foo',
                                        'bar' => ['boo' => 'bar']
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        DependencyResolver::resolve($config, $app);
    }

}
