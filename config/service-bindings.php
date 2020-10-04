<?php

/**
 * Add any binding via this configuration configuration
 * Format:
 * [group] => [
 *   [type] => [
 *      'interface' => IInterface::class,
 *      'use' => env(...),
 *      [variant1] => []
 *      [variant2] => []
 *      ...
 *   ]
 * ]
 * The 'group' can be chosen as preferred, the model name is recommended
 * The 'type' should be either 'repository', 'searchEngine', or whatever is supposed to be bound here
 * The interface class name is mandatory
 * The 'use' value is also mandatory and should be the environment setting which will then be used to switch between
 *  configurations
 * The 'variant' keys are what has to be set via the "use" setting
 * Any binding should return a class. It can be set via ['class' => [classname]] or just be the classname
 * If the class itself does require any arguments in the constructor, this can be defined in an array like this:
 * [
 *    'class' => [classname],
 *    'arguments' => [
 *      [argument1],
 *      [
 *        'class' => [classname],
 *        'arguments' => [...]
 *      ],
 *      ...
 *    ]
 * ]
 * An argument can be a hardcoded value, another env() call, or even another class name.
 * The settings are parsed recursively, so if any argument is a class which does also require any arguments, you can add
 *  another array in that place with the 'class' and 'arguments' keys again
 * You can also return a closure (WARNING: This may cause issues if you want to use Laravels config-cache):
 * [group] => fn() => new [className](
 *  new [requiredClassName](
 *  env([SOME_VAR], [default])
 *  )
 *)
 */
return [
    'default_binding' => 'bind', // either 'singleton' or 'bind'
    'bindings' => [

    ],
];
