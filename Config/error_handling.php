<?php
/**
 * Config/error_handling.php
 * 
 * Author: Timon
 * Date: 08/02/2025
 * Description:
 * This file handles error logging and email notifications for critical errors.
 * It also implements a rate limit for email notifications to prevent spam.
 * 
 * Usage:
 * 1. Include this file in your application's main entry point.
 * 2. Configure the error logging and email settings in the config file.
 * 3. Call the shutdown function to catch fatal errors.
 * 
 * Note: The config file should be located in the same directory as this file.
 * 
 * Resources:
 * - https://www.php.net/manual/en/function.set-error-handler.php
 * - https://www.php.net/manual/en/function.set-exception-handler.php
 */

ini_set('display_errors', 'Off');
$config = require __DIR__ . '/logger.php';

ini_set('log_errors', 'On');
define('ERROR_LOG_PATH', $config['logFile']);
define('ROTATED_LOGS_PATH', $config['rotatedDir']);
define('MAX_LOG_SIZE', $config['maxFileSize']); // 5 MB max log size
define('ERROR_EMAIL', $config['email']);
define('RATE_LIMIT_INTERVAL', $config['rateLimitInterval']); // 5 minutes in seconds
define('RATE_LIMIT_FILE', $config['rateLimitFile']);
date_default_timezone_set('Africa/Nairobi');

if (!is_dir(dirname(ERROR_LOG_PATH))) mkdir(dirname(ERROR_LOG_PATH), 0775, true);
if (!file_exists(ERROR_LOG_PATH)) touch(ERROR_LOG_PATH);
if (!file_exists(RATE_LIMIT_FILE)) file_put_contents(RATE_LIMIT_FILE, json_encode(['last_sent' => 0]));

function rotateLogs() {
    if (file_exists(ERROR_LOG_PATH) && filesize(ERROR_LOG_PATH) > MAX_LOG_SIZE) {
        if (!is_dir(ROTATED_LOGS_PATH)) {
            mkdir(ROTATED_LOGS_PATH, 0775, true);
        }
        $rotatedFile = ROTATED_LOGS_PATH . 'app_log_' . date('Y-m-d_H_i_a') . '.json';
        rename(ERROR_LOG_PATH, $rotatedFile);
        touch(ERROR_LOG_PATH);
    }
}

function errorLevel($errno) {
    switch ($errno) {
        case E_ERROR: return 'ERROR';
        case E_WARNING: return 'WARNING';
        case E_NOTICE: return 'NOTICE';
        case E_USER_ERROR: return 'CRITICAL';
        case E_USER_WARNING: return 'USER_WARNING';
        case E_USER_NOTICE: return 'USER_NOTICE';
        default: return 'UNKNOWN';
    }
}

function rateLimitedEmail($logEntry) {
    $rateLimitData = json_decode(file_get_contents(RATE_LIMIT_FILE), true);
    $currentTime = time();

    if ($currentTime - $rateLimitData['last_sent'] >= RATE_LIMIT_INTERVAL) {
        $subject = "Critical Error on Your Website";
        $message = "<html><body>";
        $message .= "<h2 style='color: red;'>Critical Error Occurred</h2>";
        $message .= "<p><strong>Timestamp:</strong> {$logEntry['timestamp']}</p>";
        $message .= "<p><strong>Message:</strong> {$logEntry['message']}</p>";
        $message .= "<p><strong>File:</strong> {$logEntry['file']}</p>";
        $message .= "<p><strong>Line:</strong> {$logEntry['line']}</p>";
        $message .= (isset($logEntry['error_code'])) ?  "<p><strong>Error Code:</strong> {$logEntry['error_code']}</p>" : '';
        $message .= "</body></html>";

        // mail(ERROR_EMAIL, $subject, $message, [
        //     'Content-Type: text/html; charset=UTF-8',
        // ]);

        $rateLimitData['last_sent'] = $currentTime;
        file_put_contents(RATE_LIMIT_FILE, json_encode($rateLimitData));
    }
}

function sendErrorEmail($error) {
    $subject = "Critical Error on Your Website";
    $message = "<html><body>";
    $message .= "<h2 style='color: red;'>Critical Error Occurred</h2>";
    $message .= "<p><strong>Timestamp:</strong> {$error['timestamp']}</p>";
    $message .= "<p><strong>Message:</strong> {$error['message']}</p>";
    $message .= "<p><strong>File:</strong> {$error['file']}</p>";
    $message .= "<p><strong>Line:</strong> {$error['line']}</p>";
    $message .= "<p><strong>Error Code:</strong> {$error['error_code']}</p>";
    $message .= "</body></html>";

    // mail(ERROR_EMAIL, $subject, $message, [
    //     'Content-Type: text/html; charset=UTF-8',
    // ]);
}

function customExceptionHandler($exception) {
    rotateLogs();

    $error = [
        'timestamp' => date('Y-m-d\TH:i:sP'),
        'level'     => 'EXCEPTION',
        'message'   => $exception->getMessage(),
        'file'      => $exception->getFile(),
        'line'      => $exception->getLine(),
        'trace'     => $exception->getTraceAsString(),
    ];

    file_put_contents(ERROR_LOG_PATH, json_encode($error, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);

    if (php_sapi_name() !== 'cli') {
        echo "<h2>Oops! Something went wrong.</h2>";
        echo "<p>We are working on fixing the issue. Please try again later.</p>";
    }

    rateLimitedEmail($error);
}

function shutdownFunction() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        customErrorHandler($error['type'], $error['message'], $error['file'], $error['line']);

         if (php_sapi_name() !== 'cli') {
            echo "<h2>Oops! Something went wrong.</h2>";
            echo "<p>We are working on fixing the issue. Please try again later.</p>";
        }
    }
}

function customErrorHandler($errno, $errstr, $errfile, $errline) {
    rotateLogs(); // Rotate logs if necessary

    $error = [
        'timestamp' => date('Y-m-d\TH:i:sP'),
        'level'     => errorLevel($errno),
        'message'   => $errstr,
        'file'      => $errfile,
        'line'      => $errline,
        'error_code' => $errno,
    ];

    file_put_contents(ERROR_LOG_PATH, json_encode($error, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);

    // Send email for critical errors
    // if ($errno === E_USER_ERROR) {
    //     sendErrorEmail($error);
    // }

    if ($errno === E_USER_ERROR) {
        $this->rateLimitedEmail($logEntry);
    }

    return true; // Prevent the default PHP error handler from running
}

set_error_handler("customErrorHandler");
set_exception_handler("customExceptionHandler");
register_shutdown_function("shutdownFunction");