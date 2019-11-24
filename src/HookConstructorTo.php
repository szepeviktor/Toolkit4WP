<?php

/**
 * Ultra simple hooking for class constructor.
 *
 * @package Toolkit4WP
 * @author  Viktor Szépe <viktor@szepe.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

use ReflectionClass;
use ArgumentCountError;
use ErrorException;

use function add_filter;

/**
 * Hook class constructor on to a specific action.
 *
 * Example call with priority zero.
 *
 *     HookConstructorTo::init(MyClass::class, 0);
 */
class HookConstructorTo
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
     */
    // phpcs:ignore NeutronStandard.MagicMethods.RiskyMagicMethod.RiskyMagicMethod
    public static function __callStatic(string $actionTag, array $arguments): void
    {
        if ($arguments === []) {
            throw new ArgumentCountError('Class name must be supplied.');
        }

        $class = $arguments[0];

        $constructor = (new ReflectionClass($class))->getConstructor();
        if ($constructor === null) {
            throw new ErrorException('The class must have a constructor defined.');
        }

        // Hook the constructor.
        add_filter(
            $actionTag,
            function () use ($class) {
                // Pass hook parameters to constructor.
                $args = func_get_args();
                // phpcs:ignore NeutronStandard.Functions.VariableFunctions.VariableFunction
                new $class(...$args);
            },
            $arguments[1] ?? self::DEFAULT_PRIORITY,
            $constructor->getNumberOfParameters()
        );
    }
}
