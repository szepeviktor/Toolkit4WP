<?php

/**
 * This is a gift for Phil.
 *
 * Usage: $db = new \Toolkit4WP\Db(); $db->prepare('...');
 *
 * @package Toolkit4WP
 * @author  Viktor Szépe <viktor@szepe.net>
 * @link    https://github.com/szepeviktor/toolkit4wp
 */

declare(strict_types=1);

namespace Toolkit4WP;

/**
 * Connect to global $wpdb instance from OOP code.
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
     */
    public function __get($name)
    {
        global $wpdb;

        return $wpdb->$name;
    }

    /**
     * Noop on set.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
    }

    /**
     * Execute a method.
     *
     * @see https://www.php.net/manual/en/language.oop5.overloading.php#object.call
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        global $wpdb;

        $callback = [$wpdb, $name];
        if (! \is_callable($callback)) {
            throw new \InvalidArgumentException('Unknown wpdb method: ' . $name);
        }

        return \call_user_func_array($callback, $arguments);
    }
}
