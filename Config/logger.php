<?php
/**
 * Config/logger.php
 * 
 * Author: Timon
 * Date: 08/02/2025
 * Description:
 * This file contains the configuration for the logger.
 * 
 * Usage:
 * 1. Include this file in error_handling.php & Logger class.
 */
return [
    'logFileName' => 'app',
    'logFile' => __DIR__ . '/../logs/app_log.json',
    'maxFileSize' => 5 * 1024 * 1024, // 5 MB max log size
    'rotatedDir' => __DIR__ . '/../logs/rotated/',
    'rateLimitFile' => __DIR__ . '/../logs/email_rate_limit.json',
    'rateLimitInterval' => 300, // 5 minutes in seconds 
    'email' => 'admin@example.com',
];