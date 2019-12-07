<?php

/**
 * Annotation based hooking for classes.
 *
 * @author  Viktor Szépe <viktor@szepe.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

use ReflectionClass;
use ReflectionMethod;

use function add_filter;

/**
 * @see https://www.php.net/manual/en/class.reflectionclass.php
 */
trait HookAnnotation
{
    protected function hookMethods(): void
    {
        $defaultPriority = 10;

        $classReflection = new ReflectionClass(self::class);
        foreach ($classReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Do not hook constructor or use HookConstructorTo
            if ($method->isConstructor()) {
                continue;
            }

            $matches = [];
            // Parse docblock: /** @hook hook_name 10 */
            if (
                preg_match(
                    '/^\s+\*\s+@hook\s+([a-z_\/-]+)(\s+(\d+))?\s*$/m',
                    $method->getDocComment(),
                    $matches
                ) !== 1
            ) {
                continue;
            }

            add_filter(
                $matches[1], // Hook name.
                [$this, $method->name],
                $matches[3] ?? $defaultPriority,
                $method->getNumberOfParameters()
            );
        }
    }
}
