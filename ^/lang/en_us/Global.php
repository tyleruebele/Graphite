<?php
/**
 * Global Language file
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis <apt142@gmail.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */


return $_LANG = array(
    'global.dollar' => '$%.2f',


    // Security Object Stuff
    'security.error.loginloadfail' => 'Failed to load login from session,'
        . ' please login again.',
    'security.error.mulibrowser' => 'Your account was authenticated in a'
        . ' different browser, and multiple logins are disabled for your'
        . ' account.',
    'security.error.multicomputer' => 'Your account was authenticated from'
        . ' a different computer/IP-address, and multiple logins are disabled'
        . ' for your account.',
    'security.error.accountdisabled' => 'Your account is currently disabled.',
    'security.error.disabledaccount' => 'Your account is currently disabled.',
);