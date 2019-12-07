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
    protected function hookMethods(int $defaultPriority = 10): void
    {
        $classReflection = new ReflectionClass(self::class);
        foreach ($classReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Do not hook constructor or use HookConstructorTo.
            if ($method->isConstructor()) {
                continue;
            }
            $docComment = $this->parseDocComment((string) $method->getDocComment(), $defaultPriority);
            if ($docComment === null) {
                continue;
            }

            add_filter(
                $docComment['hookName'],
                [$this, $method->name],
                $docComment['priority'],
                $method->getNumberOfParameters()
            );
        }
    }

    /**
     * Parse docblock.
     *
     * mindplay/annotations may be a better solution.
     *
     * Format: @hook hook_name 10
     *
     * @return array<string, string|int>|null
     */
    protected function parseDocComment(string $docComment, int $defaultPriority): ?array
    {
        $matches = [];
        if (
            preg_match(
                '/^\s+\*\s+@hook\s+([\w\/-]+)(?:\s+(\d+))?\s*$/m',
                $docComment,
                $matches
            ) !== 1
        ) {
            return null;
        }

        return ['hookName' => $matches[1], 'priority' => \intval($matches[2] ?? $defaultPriority)];
    }
}
