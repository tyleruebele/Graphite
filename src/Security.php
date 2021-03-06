<?php
/**
 * Security - core Security/Session manager
 * File : /src/Security.php
 *
 * PHP version 7.0
 *
 * @package  Stationer\Graphite
 * @author   LoneFry <dev@lonefry.com>
 * @license  MIT https://github.com/stationer/Graphite/blob/master/LICENSE
 * @link     https://github.com/stationer/Graphite
 */

namespace Stationer\Graphite;

use Stationer\Graphite\models\Login;
use Stationer\Graphite\models\LoginLog;
use Stationer\Graphite\data\DataBroker;

/**
 * Security class - for authenticating and managing current user.
 *
 * @package  Stationer\Graphite
 * @author   LoneFry <dev@lonefry.com>
 * @license  MIT https://github.com/stationer/Graphite/blob/master/LICENSE
 * @link     https://github.com/stationer/Graphite
 * @see      /src/models/Login.php
 *
 * @property-read Login $Login
 */
class Security {
    /** @var bool|Login Login object for current user */
    protected $Login = false;
    /** @var string IP address of current client */
    protected $ip;
    /** @var string User-agent data */
    protected $ua;
    /** @var string Hash of user-agent data */
    protected $UA;
    /** @var Session Session wrapping object */
    protected $Session;

    /**
     * Security constructor
     */
    public function __construct() {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->ua = strtolower(''
            . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']      : '')
            . (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '')
            . (isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '')
            . (isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET']  : '')
            );
        $this->UA = sha1($this->ua);

        ini_set('session.use_only_cookies', 1);
        $this->Session = G::build(Session::class);
        $this->Session->start();
        if (!isset($_SESSION['ua'])) {
            $_SESSION['ua'] = '';
        }
        if (!isset($_SESSION['ip'])) {
            $_SESSION['ip'] = '';
        }

        if (isset($_SESSION['login_id']) && is_numeric($_SESSION['login_id'])
            && 0 < $_SESSION['login_id']
        ) {
            $Login = G::build(DataBroker::class)->byPK(Login::class, $_SESSION['login_id']);
            if (false === $Login) {
                G::msg(Localizer::translate('security.error.loginloadfail'), 'error');
                unset($Login);

            // if login disabled, fail
            } elseif ($Login->disabled == 1) {
                G::msg(Localizer::translate('security.error.accountdisabled'), 'error');
                unset($Login);

            // if we got here, we should have a valid login, update usage data
            } elseif (false !== $Login && Login::class == get_class($Login)) {
                $Login->active_uts = NOW;
                $_SESSION['ua'] = $Login->UA = $this->UA;
                $_SESSION['ip'] = $Login->lastIP = $this->ip;
                // move to $this->close()// $Login->save();

                $this->Login = $Login;

                $this->_enforceReadOnly();
            }
        }

        if (false === $this->Login) {
            $_SESSION['login_id'] = 0;
        }
        $this->Session->write_close();
    }

    /**
     * Test login credentials
     *
     * @param string $loginname Loginname attempting to login
     * @param string $password  Provided password
     *
     * @return bool true on success, false on failure
     */
    public function authenticate($loginname, $password) {
        $Login = new Login(array('loginname' => $loginname));
        if (false === G::build(DataBroker::class)->fill($Login)) {
            return false;
        }

        if ($Login->disabled) {
            G::msg(Localizer::translate('security.error.disabledaccount'), 'error');
            return false;
        }

        if (!$Login->test_password($password)) {
            return false;
        }

        $this->Session->start();
        $Login->login_uts = NOW;
        $Login->active_uts = NOW;
        $_SESSION['ua'] = $Login->UA = $this->UA;
        $_SESSION['ip'] = $Login->lastIP = $this->ip;
        // move to $this->close() $Login->save();

        $_SESSION['login_id'] = $Login->login_id;
        $_SESSION['loginname'] = $Login->loginname;

        $this->Login = $Login;

        $this->Session->regenerate_id();
        $this->Session->write_close();

        $LL = new LoginLog(array('login_id' => $Login->login_id, 'ua' => $this->ua), true);
        if ($Login->login_id != 4) {
            G::build(DataBroker::class)->save($LL);
        }

        $this->_enforceReadOnly();

        return true;
    }

    /**
     * Log current user out
     *
     * @return void
     */
    public function deauthenticate() {
        if (false !== $this->Login && Login::class == get_class($this->Login)) {
            $this->Login->logout_uts = NOW;
            G::build(DataBroker::class)->save($this->Login);
            $this->Login = false;
            $this->Session->start();
            $_SESSION = array();

            // Be thorough, also delete the session cookie
            if (ini_get("session.use_cookies") && !headers_sent()) {
                $params = $this->Session->get_cookie_params();
                if (is_array($params)) {
                    setcookie($this->Session->name(), '', NOW - 86400, $params["path"],
                        $params["domain"], $params["secure"], $params["httponly"]);
                } // else we don't have a cookie to clear
            }
            $this->Session->destroy();
        }
    }

    /**
     * Close session for current request
     *
     * @return void
     */
    public function close() {
        if (null != $this->Session) {
            $this->Session->write_close();
            $this->Session = null;
        }
        if ($this->Login) {
            G::build(DataBroker::class)->save($this->Login);
        }
    }

    /**
     * Test if current logged in user has Role by passing test to Login
     *
     * @param string $role role name
     *
     * @return bool true if current Login has role, false otherwise
     */
    public function roleTest($role) {
        if (false === $role) {
            return true;
        }
        if (false !== $this->Login && Login::class == get_class($this->Login)) {
            return $this->Login->roleTest($role);
        }
        return false;
    }

    /**
     * Test whether the current user is a member of the Read_Only role
     * IF so, disable
     *
     * @return void
     */
    protected function _enforceReadOnly() {
        if ($this->roleTest('Read_Only')) {
            // Save Login before revoking write access
            G::build(DataBroker::class)->save($this->Login);

            // If the two connections match, There must not be a read only
            if (G::$M === G::$m || null == G::$m) {
                G::msg(Localizer::translate('security.login.noreadonly'), 'error');
                G::$G['CON']['path'] = G::$G['CON']['controller500'].'/500';
                G::$M->close();
                G::$m->close();
                G::$M = G::$m = null;
            } else {
                // Overwrite r/w connection with r/o connection
                G::msg(Localizer::translate('security.login.readonly'));
                G::$M = G::$m;
            }
        }
    }

    /**
     * __get magic method
     *
     * @param string $k Property to get
     *
     * @return mixed Requested value if found, null on failure
     */
    public function __get($k) {
        switch ($k) {
            case 'Login':
                return $this->Login;
            case 'ip':
                return $this->ip;
            case 'ua':
                return $this->ua;
            case 'UA':
                return $this->UA;
            default:
                $trace = debug_backtrace();
                trigger_error('Undefined property via __get(): '.$k.' in '
                              .$trace[0]['file'].' on line '.$trace[0]['line'],
                              E_USER_NOTICE);
                return null;
        }
    }

    /**
     * Ensure session is closed properly
     *
     * @return void
     */
    function __destruct() {
        $this->close();
    }

    /**
     * Test password against policies
     *
     * @param string $password Password to test
     *
     * @return bool|string true if passed|error text if failed
     */
    public static function validate_password($password) {
        // if there are no policies, everything passes!
        if (!isset(G::$G['SEC']['passwords'])) {
            return true;
        }

        extract(G::$G['SEC']['passwords']);

        // test what a password must be
        if (isset($require) && is_array($require)) {
            foreach ($require as $v) {
                if (!preg_match($v[0], $password)) {
                    return $v[1];
                }
            }
        }

        // test what a password must not be
        if (isset($deny) && is_array($deny)) {
            foreach ($deny as $v) {
                $matches = array();
                if (preg_match($v[0], $password, $matches)) {
                    return vsprintf($v[1], $matches);
                }
            }
        }

        return true;
    }
}
