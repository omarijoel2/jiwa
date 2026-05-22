<?php
/**
 * Config/autoload.php
 * 
 * Author: Timon
 * Date: 08/02/2025
 * Description:
 * This file sets up the autoloader for the application.
 * It maps class names to their corresponding files in the App\Classes and App\Config directories.
 * 
 * Usage:
 * 1. Include this file in your application's main entry point.
 * 2. Call the spl_autoload_register function to register the autoloader.
 * 
 */
require_once __DIR__ . '/error_handling.php';
require_once __DIR__ . '/getClientIP.php';

define("APP_URL", "https://jiwa.hezsun.com");

spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\Classes') === 0) {
        $classPath = str_replace('App\\Classes\\', '', $class);
        $file = __DIR__ . '/../Classes/' . str_replace('\\', '/', $classPath) . '.php';
    } elseif (strpos($class, 'App\\Config') === 0) {
        $classPath = str_replace('App\\Config\\', '', $class);
        $file = __DIR__ . '/../Config/' . str_replace('\\', '/', $classPath) . '.php';
    } else {
        return;
    }

    if (file_exists($file)) {
        require_once $file;
    } else {

        error_log(json_encode([
            'timestamp' => date('Y-m-d\TH:i:sP'),
            'level'     => 'ERROR',
            'message'   => "Autoloader Error: Class file '{$file}' not found.",
        ], JSON_UNESCAPED_SLASHES) . PHP_EOL, 3, ERROR_LOG_PATH);

        die("An error occurred. Please try again later.");
    }
});


