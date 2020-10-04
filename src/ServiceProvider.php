<?php

namespace Fanmade\ServiceBinding;

use Fanmade\ServiceBinding\Console\ServiceBindingCheck;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/config/service-binding.php' => config_path('service-binding.php'),
            ]
        );

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    ServiceBindingCheck::class,
                ]
            );
        }
    }

}
