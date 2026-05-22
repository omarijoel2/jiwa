<?php
/**
 * Classes/Session.php
 * 
 * Author: Timon
 * Date: 09/02/2025
 * 
 */
namespace App\Classes;

class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        self::start();
        session_unset();
        session_destroy();
    }


    public static function setLastPage()
    {
        self::start();
        $current_page = (isset($_SERVER['REQUEST_URI'])) ? basename($_SERVER['REQUEST_URI']) : basename($_SERVER['PHP_SELF']);
        $_SESSION['last_page'] = ($current_page != '/win/login' || $current_page != 'login' ) ? $current_page : 'index.php';
    }

    public static function getLastPage()
    {
        self::start();
        $last_page = (isset($_SESSION['last_page'])) ? $_SESSION['last_page'] : 'index.php';
        return $last_page;
    }
}
?>
