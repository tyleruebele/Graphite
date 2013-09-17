<?php
/**
 * ReportDataProvider - Provide report data from MySQL
 * File : /^/lib/ReportMySQLDataProvider.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * ReportDataProvider class - Fetches reports for PassiveReport models
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/mysqli_.php
 * @see      /^/lib/PassiveReport.php
 */
abstract class ReportMySQLDataProvider extends MySQLDataProvider {
    /**
     * Search for records of type $class according to search params $params
     * Order results by $orders and limit results by $count, $start
     *
     * @param string $class  Name of Model to search for
     * @param array  $params Values to search against
     * @param array  $orders Order(s) of results
     * @param int    $count  Number of rows to fetch
     * @param int    $start  Number of rows to skip
     *
     * @return array Found records
     */
    public function fetch($class, array $params, array $orders = array(), $count = null, $start = 0) {
        /** @var PassiveReport $Model */
        $Model = G::build($class);
        if (!is_a($Model, 'PassiveReport')) {
            trigger_error('Supplied class name does not extend PassiveReport', E_USER_ERROR);
        }

        // Sanitize $params through Model
        $Model->setAll($params);
        $params = $Model->getAll();
        $params = array_filter($params, function($val) {
            return !is_null($val);
        });

        $query = array();

        foreach ($params as $key => $val) {
            if ('a' === $val['type']) {
                $arr = unserialize($Model->$key);

                foreach ($arr as $kk => $vv) {
                    $arr[$kk] = G::$m->escape_string($vv);
                }

                $query[] = sprintf($val['sql'], "'".implode("', '", $arr)."'");
            } else {
                $query[] = sprintf($val['sql'], G::$m->escape_string($this->vals[$key]));
            }
        }

        if (count($query) == 0) {
            $query = sprintf($this->getQueryForReport($class), '1');
        } else {
            $query = sprintf($this->getQueryForReport($class), implode(' AND ', $query));
        }

        $query .= $this->_makeOrderBy($Model->getOrders($orders));

        if (null == $count) {
            $count = $Model->_count;
            $start = $Model->_start;
        }
        if (is_numeric($count) && is_numeric($start)) {
            // add limits also
            $query .= ' LIMIT '.$start.', '.$count;
        }


        if (false === $result = G::$m->query($query)) {
            return false;
        }
        while ($row = $result->fetch_assoc()) {
            $this->_data[] = $row;
        }
        $result->close();
        $this->onload();

        return true;
    }

    /**
     * Save data does not apply to reports
     *
     * @param PassiveRecord &$Model Model to save, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function insert(PassiveRecord &$Model) {
        return false;
    }

    /**
     * Save data does not apply to reports
     *
     * @param PassiveRecord &$Model Model to save, passed by reference
     *
     * @return bool false
     */
    public function update(PassiveRecord &$Model) {
        return false;
    }

    /**
     * @param string $class Name of Report
     *
     * @return mixed
     */
    abstract public function getQueryForReport($class);
}
