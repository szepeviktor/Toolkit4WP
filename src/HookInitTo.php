<?php

/**
 * Ultra simple hooking for init() method.
 *
 * @package Toolkit4WP
 * @author  Viktor Szépe <viktor@szepe.net>
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

use ReflectionClass;
use ArgumentCountError;

use function add_filter;

/**
 * Hook init() method on to a specific action.
 *
 * Example call with priority zero.
 *
 *     HookInitTo::plugins_loaded(MyClass::class, 0);
 */
class HookInitTo
{
    protected const DEFAULT_PRIORITY = 10;

    /**
     * Hook to the action in the method name.
     *
     * @param string $actionTag
     * @param array $arguments = [
     *     @type string $class
     *     @type int $pritority
     * ]
     * @throws \ReflectionException
     */
    // phpcs:ignore NeutronStandard.MagicMethods.RiskyMagicMethod.RiskyMagicMethod
    public static function __callStatic(string $actionTag, array $arguments): void
    {
        if ($arguments === []) {
            throw new ArgumentCountError('Class name must be supplied.');
        }

        $class = $arguments[0];

        $initMethod = (new ReflectionClass($class))->getMethod('init');

        // Hook the constructor.
        add_filter(
            $actionTag,
            function () use ($class) {
                // phpcs:ignore NeutronStandard.Functions.VariableFunctions.VariableFunction
                $instance = new $class();
                // Pass hook parameters to init()
                $args = func_get_args();
                $instance->init(...$args);
            },
            $arguments[1] ?? self::DEFAULT_PRIORITY,
            $initMethod->getNumberOfParameters()
        );
    }
}
