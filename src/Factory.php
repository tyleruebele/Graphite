<?php
/**
 * Factory
 *
 * PHP version 7.0
 *
 * @package  Stationer\Graphite
 * @author   Cris Bettis
 * @license  MIT https://github.com/stationer/Graphite/blob/master/LICENSE
 * @link     https://github.com/stationer/Graphite
 */

namespace Stationer\Graphite;

/**
 * Class Factory
 *
 * This instantiates Objects
 *
 * @package  Stationer\Graphite
 * @author   Cris Bettis
 * @license  MIT https://github.com/stationer/Graphite/blob/master/LICENSE
 * @link     https://github.com/stationer/Graphite
 */
class Factory {

    /**
     * Instantiates a new object.
     * Takes a className followed by parameters needed for the constructor of
     * that object
     *
     * @param string $className Name of Class to include
     * @param array  $args      Constructor arguments
     *
     * @return mixed
     */
    public function build($className, ...$args) {
        if (is_string($className)) {
            // Use Reflection to pass dynamic constructor arguments
            foreach (G::$G['namespaces'] as $namespace) {
                if (class_exists($namespace.$className)) {
                    $reflection = new \ReflectionClass($namespace.$className);

                    return $reflection->newInstanceArgs($args);
                }
            }
        }

        return null;
    }
}
