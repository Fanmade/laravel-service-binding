<?php

namespace Fanmade\ServiceBinding\Resolver;

use Fanmade\ServiceBinding\Validator\BindingConfigurationValidator;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerInterface;

class DependencyResolver
{
    protected ContainerInterface $container;

    protected string $defaultType;

    protected int $resolveCount = 0;

    protected BindingConfigurationValidator $validator;

    protected function __construct(ContainerInterface $app)
    {
        $this->container = $app;
        $this->validator = new BindingConfigurationValidator();
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
            $config = $this->validator->validateSettings($model, $interface, $settings);

            $resolved = $this->resolveConfiguration($config);
            $type = $settings['_type'] ?? $this->defaultType;

            $this->container->$type($interface, fn ($app) => $resolved);
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
}
