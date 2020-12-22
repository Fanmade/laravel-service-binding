<?php

namespace Fanmade\ServiceBinding\Resolver;

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerInterface;

class DependencyResolver
{
    protected ContainerInterface $container;

    protected string $defaultType;

    protected int $resolveCount = 0;

    protected function __construct(ContainerInterface $app)
    {
        $this->container = $app;
    }

    /**
     * @param array $configurationArray
     * @param \Psr\Container\ContainerInterface $app
     * @throws \Fanmade\ServiceBinding\Resolver\InvalidConfigurationException
     */
    public static function resolve(array $configurationArray, ContainerInterface $app): void
    {
        $resolver = new static($app);
        $resolver->defaultType = $configurationArray['default_binding'] ?? 'bind';

        foreach (($configurationArray['bindings'] ?? []) as $model => $bindings) {
            $resolver->resolveModelBindings($bindings, $model);
        }
    }

    /**
     * @param array $bindings
     * @param string $model
     * @throws \Fanmade\ServiceBinding\Resolver\InvalidConfigurationException
     */
    protected function resolveModelBindings(array $bindings, string $model): void
    {
        foreach ($bindings as $interface => $settings) {
            $config = $this->validateSettings($model, $interface, $settings);

            $resolved = $this->resolveConfiguration($config);
            $type = $settings['_type'] ?? $this->defaultType;

            $this->container->$type($interface, fn($app) => $resolved);
            $this->resolveCount++;
        }
    }

    /**
     * @param mixed $configuration
     * @param bool $isArgument
     * @return mixed
     * @throws \Fanmade\ServiceBinding\Resolver\InvalidConfigurationException
     */
    protected function resolveConfiguration($configuration, bool $isArgument = false)
    {
        if (empty($configuration)) {
            throw new InvalidConfigurationException('Empty configuration', 1601456193377);
        }
        if (is_callable($configuration)) {
            return $configuration();
        }
        if (is_object($configuration)) {
            return $configuration;
        }
        if (!is_array($configuration)) {
            return class_exists($configuration) ? new $configuration() : $configuration;
        }
        $arguments = [];
        if (array_key_exists('_arguments', $configuration)) {
            foreach ($configuration['_arguments'] as $argument) {
                $arguments[] = $this->resolveConfiguration($argument, true);
            }
        }
        if (array_key_exists('_interface', $configuration)) {
            return $this->resolveInterface($configuration['_interface'], $arguments);
        }
        if (array_key_exists('_class', $configuration)) {
            return $this->resolveClass($configuration['_class'], $arguments);
        }
        // if this is an argument, it might just require an array
        if ($isArgument) {
            return $configuration;
        }

        throw new InvalidConfigurationException(
            'Could not resolve configuration ' . print_r($configuration, true),
            1601456296190
        );
    }

    protected function resolveClass($class, $arguments)
    {
        if (is_object($class)) {
            return $class;
        }
        if (is_array($arguments) && !empty($arguments)) {
            return new $class(...$arguments);
        }

        return new $class();
    }

    /**
     * @param string $interface
     * @param array $arguments
     * @return mixed
     * @throws \Fanmade\ServiceBinding\Resolver\InvalidConfigurationException
     */
    protected function resolveInterface(string $interface, array $arguments)
    {
        try {
            if (!empty($arguments)) {
                /** @noinspection PhpUndefinedMethodInspection */
                return $this->container->makeWith($interface, $arguments);
            }

            return $this->container->make($interface);
        } catch (BindingResolutionException $e) {
            throw new InvalidConfigurationException(
                "Can't resolve interface '{$interface}' in binding configuration",
                1601452874405
            );
        }
    }

    /**
     * @param string $model
     * @param string $interface
     * @param array $config
     * @return array|string|callable
     * @throws \Fanmade\ServiceBinding\Resolver\InvalidConfigurationException
     */
    protected function validateSettings(string $model, string $interface, array $config)
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
