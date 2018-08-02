<?php
/**
 * Login - Login (user) AR class
 * File : /^/models/Login.php
 *
 * PHP version 7.0
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

namespace Stationer\Graphite\data;

use Stationer\Graphite\G;
use Stationer\Graphite\PasswordHasher;

/**
 * Login class - for managing site users, including current user.
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Record.php
 * @property int    $login_id
 * @property int    $created_uts
 * @property string $updated_dts
 * @property int    $edited_uts
 * @property int    $active_uts
 * @property int    $login_uts
 * @property int    $logout_uts
 * @property string $loginname
 * @property string $password
 * @property string $realname
 * @property string $email
 * @property string $comment
 * @property string $UA
 * @property string $lastIP
 * @property int    $referrer_id
 * @property bool   $disabled
 * @property bool   $flagChangePass
 */
class Login extends PassiveRecord {
    /** @var string Table name, un-prefixed */
    protected static $table = G_DB_TABL.'Login';
    /** @var string Primary Key */
    protected static $pkey = 'login_id';
    /** @var string Select query, without WHERE clause */
    protected static $query = '';
    /** @var array Table definition as collection of fields */
    protected static $vars = [
        'login_id'       => ['type' => 'i', 'min' => 1, 'guard' => true],
        'created_uts'    => ['type' => 'ts', 'min' => 0, 'guard' => true],
        'updated_dts'    => ['type' => 'dt', 'min' => NOW, 'def' => NOW, 'guard' => true],
        'edited_uts'     => ['type' => 'dt', 'min' => NOW, 'def' => NOW, 'guard' => true],
        'active_uts'     => ['type' => 'ts', 'min' => 0],
        'login_uts'      => ['type' => 'ts', 'min' => 0],
        'logout_uts'     => ['type' => 'ts', 'min' => 0],
        'loginname'      => ['type' => 's', 'strict' => true, 'min' => 3, 'max' => 255],
        'password'       => ['type' => 's', 'strict' => true, 'min' => 3, 'max' => 255],
        'realname'       => ['type' => 's', 'max' => 255],
        'email'          => ['type' => 'em', 'max' => 255],
        'comment'        => ['type' => 's', 'max' => 255],
        'UA'             => ['type' => 's', 'min' => 40, 'max' => 40],
        'lastIP'         => ['type' => 'ip'],
        'referrer_id'    => ['type' => 'i', 'strict' => true, 'def' => 0, 'min' => 1],
        'disabled'       => ['type' => 'b', 'def' => 0],
        'flagChangePass' => ['type' => 'b', 'def' => 1],
    ];
    /** @var array List of tables that connect this to another table */
    protected static $joiners = [
        'Role' => G_DB_TABL.'Roles_Logins',
    ];

    /** @var string A regex for determining valid loginnames */
    protected static $labelRE = '^\w[\w\_\-\@\.\d]+$';
    /** @var array Cache the Roles this Login has */
    protected $roles = [];

    /**
     * Wrap the parent constructor and set roles if passed
     *
     * @param bool|int|array $a pkey value|set defaults|set values
     * @param bool           $b Set defaults
     *
     * @throws \Exception
     */
    public function __construct($a = null, $b = null) {
        if ('' == static::$query) {
            $keys          = array_keys(static::$vars);
            static::$query = "
SELECT t.`".join('`, t.`', $keys)."`, GROUP_CONCAT(r.`label`) as `roles`
FROM `".static::$table."` t
    LEFT JOIN `".static::getTable('Role')."` rl ON t.`login_id` = rl.`login_id`
    LEFT JOIN `".Role::getTable()."` r ON r.`role_id` = rl.`role_id`
";
        }
        parent::__construct($a, $b);
        if (is_array($a) && isset($a['roles'])) {
            $this->roles = explode(',', $a['roles']);
        }
    }

    /**
     * Process extra values returned by load()'s SELECT query
     *
     * @param array $row Extra values for post processing
     *
     * @return array Remaining unprocesed values
     */
    public function onload(array $row = []) {
        if (isset($row['roles'])) {
            $this->roles = explode(',', $row['roles']);
            unset($row['roles']);
        }

        return $row;
    }

    /**
     * Pre-process Login for INSERT
     * Assure dateCreated and referrer_id have appropriate values
     *
     * @return void
     */
    public function oninsert() {
        $this->__set('created_uts', NOW);
        if ($this->__get('referrer_id') < 1) {
            $this->__set('referrer_id', G::$S->Login->login_id);
        }
    }

    /**
     * Pre-process Login for UPDATE
     * Ensure dateModified is set only for non-meta fields
     *
     * @return void
     */
    public function onupdate() {
        $a = ['login_uts', 'logout_uts', 'active_uts', 'edited_uts', 'created_uts', 'lastIP'];
        foreach (static::$vars as $k => $v) {
            if ($this->vals[$k] != $this->DBvals[$k] && !in_array($k, $a)) {
                $this->__set('edited_uts', NOW);
                break;
            }
        }
    }

    /**
     * Indicate whether login has specified role
     *
     * @param string $role Role to test
     *
     * @return bool true if Login has Role, false otherwise
     */
    public function roleTest($role) {
        return is_array($this->roles) && in_array($role, $this->roles);
    }

    /**
     * Fetch the role array.
     *
     * @return array  Array of rules
     */
    public function getRoles() {
        return $this->roles;
    }

    /**
     * Getter/Setter for loginname value
     *
     * @return string $this->loginname
     */
    public function loginname() {
        if (0 < count($a = func_get_args())) {
            if (strlen($a[0]) >= static::$vars['loginname']['min']
                && preg_match('/'.self::$labelRE.'/', $a[0])
            ) {
                $this->vals['loginname'] = substr($a[0], 0, static::$vars['loginname']['max']);
            }
        }

        return $this->vals['loginname'];
    }

    /**
     * Getter/Setter for password value
     *
     * @return string $this->password
     */
    public function password() {
        if (0 < count($a = func_get_args())) {
            if (PasswordHasher::is_hash($a[0])) {
                $this->vals['password'] = $a[0];
            } elseif (strlen($a[0]) >= static::$vars['password']['min']) {
                $this->vals['password'] = PasswordHasher::hash_password($a[0]);
            }
        }

        return $this->vals['password'];
    }

    /**
     * Verify supplied password using configured PasswordHasher
     *
     * @param string $password The password to verify
     *
     * @return bool True if password verified, false if not
     */
    public function test_password($password) {
        return PasswordHasher::test_password($password, $this->password());
    }

    /**
     * Get referring loginname
     *
     * @return string Loginname of the referring Login
     */
    public function getReferrer() {
        if ($this->__get('referrer_id') > 0) {
            $referrer = new Login($this->__get('referrer_id'));
            G::build(DataBroker::class)->load($referrer);

            return $referrer->loginname;
        }

        return '';
    }

    /**
     * Return number of logins per initial letter
     *
     * @return array Array indexed by letter containing counts per letter
     */
    public static function initials() {
        // get login counts per letter
        $letters = array_fill_keys(range('A', 'Z'), 0);
        $query   = "
SELECT UPPER(LEFT(loginname, 1)), count(loginname)
FROM `".static::$table."`
GROUP BY UPPER(LEFT(loginname, 1))
";

        if (false !== $result = G::$m->query($query)) {
            while ($row = $result->fetch_array()) {
                $letters[$row[0]] = $row[1];
            }
        }

        return $letters;
    }

    /**
     * SELECT all the records from the database using static::$query
     * add all set values to the WHERE clause, returns collection
     *
     * @param string $c Search for logins with loginnames starting with this
     *
     * @return array|bool Collection of Login objects starting with passed string
     */
    public static function forInitial($c = null) {
        if (strlen($c) < 1) {
            return [];
        }
        $query = "
SELECT t.`login_id`, t.`loginname`
FROM `".static::$table."` t
WHERE `loginname` LIKE '".G::$m->escape_string($c)."%'
ORDER BY `loginname`
";
        if (false === $result = G::$m->query($query)) {
            return false;
        }
        if (0 == $result->num_rows) {
            $result->close();

            return [];
        }
        $a = [];
        while ($row = $result->fetch_assoc()) {
            $a[$row[static::$pkey]] = new static($row);
        }
        $result->close();

        return $a;
    }

    /**
     * Searches for actions which use the given email address
     *
     * @param string $email Email address used
     *
     * @return array|bool
     */
    public static function searchEmail($email) {
        // Escapes email
        $email = G::$m->escape_string($email);
        if (trim($email) == '') {
            return false;
        }

        // Selects the identities that use the given email address
        $query  = "
SELECT `login_id`, `loginname`
FROM `".self::$table."`
WHERE `email` = '$email'
ORDER BY `login_id`
";
        $result = G::$m->queryToArray($query, static::$pkey);

        return $result;
    }
}
