<?php

namespace Fanmade\ServiceBinding\Validator;

use Fanmade\ServiceBinding\Resolver\InvalidConfigurationException;

class BindingConfigurationValidator
{
    /**
     * @param string $model
     * @param string $interface
     * @param array $config
     * @return array|string|callable
     * @throws \Fanmade\ServiceBinding\Resolver\InvalidConfigurationException
     */
    public function validateSettings(string $model, string $interface, array $config)
    {
        if (!array_key_exists('_use', $config)) {
            throw new InvalidConfigurationException(
                "Missing 'use' setting for interface '{$interface}' binding configuration of model {$model}",
                1600291820280
            );
        }

        if (!array_key_exists($config['_use'], $config)) {
            throw new InvalidConfigurationException(
                "Invalid '_use' of value '{$config['_use']}' setting for interface '{$interface}' binding "
                . "configuration of model {$model}",
                1600291945252
            );
        }

        if (array_key_exists('_type', $config) && !in_array($config['_type'], ['bind', 'singleton'])) {
            throw new InvalidConfigurationException(
                "Invalid '_type' setting for binding configuration of model {$model}",
                16014570968532
            );
        }

        if (!interface_exists($interface)) {
            throw new InvalidConfigurationException(
                "'{$interface}' is not a valid interface for binding configuration of model {$model}",
                1601457096826
            );
        }

        return $config[$config['_use']];
    }
}
