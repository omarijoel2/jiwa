<?php
require_once __DIR__ . '/../Config/autoload.php';

header("Content-Type: application/json");
$QueueTimeOutURLCallbackResponse = file_get_contents('php://input');
$logFile = "/var/www/jiwa/logs/b2cbalance.json";

// Open the file safely
$log = fopen($logFile, "a");
if ($log === false) {
    error_log("Failed to open log file: $logFile");
    http_response_code(500);
    echo json_encode(["error" => "Unable to write to log file"]);
    exit;
}

fwrite($log, $QueueTimeOutURLCallbackResponse . PHP_EOL);
fclose($log);
