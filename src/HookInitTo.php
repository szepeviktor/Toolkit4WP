<?php

/**
 * Ultra simple hooking for init() method.
 *
 * @author  Viktor Szépe <viktor@szepe.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

use ReflectionClass;

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
     * @param array<string|int> $arguments = [
     *     @type string $class
     *     @type int $pritority
     * ]
     * @throws \ArgumentCountError
     * @throws \ReflectionException
     *
     * phpcs:disable NeutronStandard.MagicMethods.RiskyMagicMethod.RiskyMagicMethod
     */
    public static function __callStatic(string $actionTag, array $arguments): void
    {
        // phpcs:enable NeutronStandard.MagicMethods.RiskyMagicMethod.RiskyMagicMethod
        if ($arguments === []) {
            throw new \ArgumentCountError('Class name must be supplied.');
        }

        $class = $arguments[0];

        $initMethod = (new ReflectionClass($class))->getMethod('init');

        // Hook the constructor.
        add_filter(
            $actionTag,
            static function () use ($class): void {
                // phpcs:ignore NeutronStandard.Functions.VariableFunctions.VariableFunction
                $instance = new $class();
                // Pass hook parameters to init()
                $args = func_get_args();
                $instance->init(...$args);
            },
            \intval($arguments[1]) ?? self::DEFAULT_PRIORITY,
            $initMethod->getNumberOfParameters()
        );
    }
}
