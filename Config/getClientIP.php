<?php
/**
 * Config/getClientIP.php
 * 
 * Author: Timon
 * Date: 08/02/2025
 * Description:
 * This function returns the IP address of the client making the request.
 * 
 * Usage:
 * 1. Include this file in your application's main entry point.
 * 2. Call the getClientIP function to get the client's IP address.
 * 
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]; 
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}