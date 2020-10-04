<?php

namespace Fanmade\ServiceBinding;

use Fanmade\ServiceBinding\Console\ServiceBindingCheck;
use Fanmade\ServiceBinding\Resolver\DependencyResolver;
use Illuminate\Support\ServiceProvider;

class BindingServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $config = $this->app->config['service-bindings'];
        if (!$config) {
            return;
        }
        DependencyResolver::resolve($config, $this->app);
    }

    public function boot()
    {
        if (function_exists('config_path')) {
            $this->publishes(
                [
                    __DIR__ . '/../config/service-bindings.php' => config_path('service-bindings.php'),
                ]
            );
        }

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    ServiceBindingCheck::class,
                ]
            );
        }
    }

}
