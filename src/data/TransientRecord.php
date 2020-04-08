<?php
/**
 * TransientRecord - core database record class
 * File : /src/data/TransientRecord.php
 *
 * PHP version 7.3
 *
 * @package  Stationer\Graphite
 * @author   Tyler Uebele
 * @license  MIT https://github.com/stationer/Graphite/blob/master/LICENSE
 * @link     https://github.com/stationer/Graphite
 */

namespace Stationer\Graphite\data;

/**
 * TransientRecord class - used as a base class for Record Model classes
 *  for use with a DataProvider
 *
 * @package  Stationer\Graphite
 * @author   Tyler Uebele
 * @license  MIT https://github.com/stationer/Graphite/blob/master/LICENSE
 * @link     https://github.com/stationer/Graphite
 * @staticvar array $vars
 */
abstract class TransientRecord extends DataModel {
    // Should be defined in subclasses
    // protected static $vars = array();// record definition

    /**
     * Constructor accepts four prototypes:
     *  Record(true) will create an instance with default values
     *  Record(int) will create an instance with pkey set to int
     *  Record(array()) will create an instance with supplied values
     *  record(array(),true) will create a record with supplied values
     *
     * @param bool|int|array $a pkey value|set defaults|set values
     * @param bool           $b set defaults
     *
     * @throws \Exception
     */
    public function __construct($a = null, $b = null) {
        // initialize the values arrays with null values as some tests depend
        foreach (static::$vars as $k => $v) {
            $this->vals[$k] = null;
        }

        // This fakes constructor overriding
        if (true === $a) {
            $this->defaults();
        } else {
            if (true === $b) {
                $this->defaults();
            }
            if (is_array($a)) {
                $this->setAll($a);
            }
        }
    }
}
