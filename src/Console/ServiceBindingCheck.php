<?php

namespace Fanmade\ServiceBinding\Console;

use Fanmade\ServiceBinding\Resolver\InvalidConfigurationException;
use Fanmade\ServiceBinding\Validator\BindingConfigurationValidator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;

class ServiceBindingCheck extends Command
{
    protected $signature = 'fanmade:check-bindings';

    protected $description = 'Check service binding configuration';

    public function handle(Application $app)
    {
        $this->info('Checking configuration!');

        $validator = new BindingConfigurationValidator();

        $counter = 0;

        /** @noinspection PhpUndefinedFieldInspection */
        $config = $app->config['service-bindings'];
        if (!$config || !array_key_exists('bindings', $config)) {
            $this->info('No configuration found.');

            return 0;
        }
        foreach ($config['bindings'] as $model => $bindings) {
            foreach ($bindings as $interface => $settings) {
                try {
                    $validator->validateSettings($model, $interface, $settings);
                    $this->comment(
                        "Validated configuration for model '{$model}' using interface '{$interface}'.",
                        'vv'
                    );

                    $resolved = get_class($app->make($interface));
                    $this->comment(
                        "Resolved '{$resolved}'.",
                        'vv'
                    );
                    $counter++;
                } catch (InvalidConfigurationException $e) {
                    $this->error($e->getMessage());
                }
            }
        }

        $this->info("Sucessfully checked {$counter} bindings.");
        return 0;
    }
}
