<?php // phpcs:disable SlevomatCodingStandard.Namespaces.FullyQualifiedClassNameInAnnotation.NonFullyQualifiedClassName

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
 * Implement hooking in method annotation.
 *
 * Format: @hook hook_name 10
 *         @hook hook_name first
 *         @hook hook_name last
 *
 * mindplay/annotations may be a better solution.
 *
 * @see https://github.com/szepeviktor/debian-server-tools/blob/master/webserver/wordpress/WordPress-hooks.md
 */
trait HookAnnotation
{
    protected function hookMethods(int $defaultPriority = 10): void
    {
        $classReflection = new ReflectionClass(self::class);
        // Look for hook tag in all public methods.
        foreach ($classReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Do not hook constructor, use HookConstructorTo.
            if ($method->isConstructor()) {
                continue;
            }
            $hookDetails = $this->getMetadata((string)$method->getDocComment(), $defaultPriority);
            if ($hookDetails === null) {
                continue;
            }

            add_filter(
                $hookDetails['name'],
                [$this, $method->name],
                $hookDetails['priority'],
                $method->getNumberOfParameters()
            );
        }
    }

    /**
     * Read hook tag from docblock.
     *
     * @return array{name: string, priority: int}|null
     */
    protected function getMetadata(string $docComment, int $defaultPriority): ?array
    {
        $matches = [];
        if (
            \preg_match(
                '/^\s+\*\s+@hook\s+([\w\/_=-]+)(?:\s+(\d+|first|last))?\s*$/m',
                $docComment,
                $matches
            ) !== 1
        ) {
            return null;
        }

        if (! isset($matches[2])) {
            return [
                'name' => $matches[1],
                'priority' => $defaultPriority,
            ];
        }

        switch ($matches[2]) {
            case 'first':
                $priority = PHP_INT_MIN;
                break;
            case 'last':
                $priority = PHP_INT_MAX;
                break;
            default:
                $priority = \intval($matches[2]);
                break;
        }

        return [
            'name' => $matches[1],
            'priority' => $priority,
        ];
    }
}
