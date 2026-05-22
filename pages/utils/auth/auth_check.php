<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Users;
use App\Classes\Session;
use App\Classes\Logger;

$logger = new Logger();

Session::start();

$lastpage = Session::getLastPage();

$logger->debug("Last Page: ", $lastpage);




// If the current page is login.php, do nothing
$current_page = (isset($_SERVER['REQUEST_URI'])) ? basename($_SERVER['REQUEST_URI']) : basename($_SERVER['PHP_SELF']);
if ($current_page === "login") {
    
    if(Session::has('user_id')) {
        if(trim($lastpage) == '/win/login' || trim($lastpage) == 'login'){
            Session::remove("last_visited");
            header("Location: winners.php");
            exit;
        }
    } 

    // Check for active session or remember me token
    if (!Session::has('user_id') && isset($_COOKIE['remember_me'])) {
        $user = Users::getUserByRememberToken($_COOKIE['remember_me']);
        if ($user) {
            Session::set('user_id', $user['id']);
            Session::set('role_id', $user['role_id']);

            if($lastpage == '/win/login' || $lastpage == 'login'){
                Session::remove("last_visited");
                header("Location: winners.php");
                exit;
            }else{
                header("Location: winners.php");
                exit;
            }
        }
    }

    if (!Session::has('user_id')) {
        // Session::setLastPage();
        return;
    }

    
}else{
    // if(Session::has('user_id')) {
    //     if(trim($lastpage) == '/win/login' || trim($lastpage) == 'login'){
    //         Session::remove(trim($lastpage));
    //         header("Location: dashboard");
    //         exit;
    //     }else{
    //         header("Location: $lastpage");
    //         exit;
    //     }
    // } 

    // Check for active session or remember me token
    // if (!Session::has('user_id') && isset($_COOKIE['remember_me'])) {
    //     $user = Users::getUserByRememberToken($_COOKIE['remember_me']);
    //     if ($user) {
    //         Session::set('user_id', $user['id']);
    //         Session::set('role_id', $user['role_id']);

    //         if($lastpage == '/win/login' || $lastpage == 'login'){
    //             Session::remove($lastpage);
    //             header("Location: dashboard");
    //             exit;
    //         }else{
    //             header("Location: $lastpage");
    //             exit;
    //         }
    //     }
    // }

    // Session::setLastPage();

    // If session still not set, redirect to login and save last page
    if (!Session::has('user_id')) {
        
        header("Location: login");
        exit;
    }
}




 







?>
