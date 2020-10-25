<?php // phpcs:disable NeutronStandard.Functions.TypeHint.NoReturnType

/**
 * This is a gift for Phil.
 *
 * @author  Viktor Szépe <viktor@szepe.net>
 * @license https://opensource.org/licenses/MIT MIT
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

// phpcs:disable NeutronStandard.MagicMethods.DisallowMagicGet.MagicGet,NeutronStandard.MagicMethods.DisallowMagicSet.MagicSet,NeutronStandard.MagicMethods.RiskyMagicMethod.RiskyMagicMethod

/**
 * Connect to global $wpdb instance from OOP code.
 *
 * Usage example.
 *
 *     $db = new \Toolkit4WP\Db(); $db->prepare('...');
 *
 * @see https://www.php.net/manual/en/language.oop5.magic.php
 */
class Db
{
    /**
     * Get a property.
     *
     * @see https://codex.wordpress.org/Class_Reference/wpdb#Class_Variables
     * @param string $name
     * @return mixed
     *
     * phpcs:disable NeutronStandard.Functions.TypeHint.NoReturnType
     */
    public function __get(string $name)
    {
        global $wpdb;

        return $wpdb->$name;
    }
    // phpcs:enable NeutronStandard.Functions.TypeHint.NoReturnType

    /**
     * Noop on set.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     *
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __set(string $name, $value): void
    {
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Execute a method.
     *
     * @see https://www.php.net/manual/en/language.oop5.overloading.php#object.call
     * @param string $name
     * @param array<mixed> $arguments
     * @return mixed
     *
     * phpcs:disable NeutronStandard.Functions.TypeHint.NoReturnType
     */
    public function __call(string $name, array $arguments)
    {
        global $wpdb;

        $callback = [$wpdb, $name];
        if (! \is_callable($callback)) {
            throw new \InvalidArgumentException('Unknown wpdb method: ' . $name);
        }

        // phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
        return \call_user_func_array($callback, $arguments);
    }
    // phpcs:enable NeutronStandard.Functions.TypeHint.NoReturnType
}
