<?php
require __DIR__ . "/../Config/autoload.php";
date_default_timezone_set('Africa/Nairobi');

use App\Classes\Logger;

$loopDelay = 10;

$logger = new Logger("daemon");

$config = require_once __DIR__ . '/../Config/db.php';
$database = $config['database'];
$host = $config[$database]['host'];
$username = $config[$database]['username'];
$password = $config[$database]['password'];
$database_name = $config[$database]['database_name'];

$mysqli = new mysqli("p:" . $host, $username, $password, $database_name);

if ($mysqli->connect_error) {
    $logger->error("DB Connection failed: " . $mysqli->connect_error, ["module" => "check_db_phone_number_hash"]);
    exit(1);
}

while (true) {
    try {
        $logger->debug("Checking for decoded phone numbers to process...", ["module" => "check_db_phone_number_hash"]);
        $query = "SELECT * FROM winners_selection.unhash_queue_tasks WHERE requires_processing = 1 LIMIT 1";
        $result = $mysqli->query($query);

        if (!$result) {
            $logger->error("Query failed: " . $mysqli->error, ["module" => "check_db_phone_number_hash"]);
            sleep($loopDelay);
            continue;
        }

        if ($result->num_rows === 0) {
            $logger->debug('No decoded phone numbers to process', ["module" => "check_db_phone_number_hash"]);
        } else {
            $logger->debug('Decoded phone number(s) found, processing...', ["module" => "check_db_phone_number_hash"]);

            $stmt = $mysqli->prepare("CALL winners_selection.ProcessUnhashTasks()");
            if (!$stmt) {
                $logger->error("Failed to prepare statement: " . $mysqli->error, ["module" => "check_db_phone_number_hash"]);
                sleep($loopDelay);
                continue;
            }

            if ($stmt->execute()) {
                $logger->debug('Processing complete', ["module" => "check_db_phone_number_hash"]);
            } else {
                $logger->error("Failed to execute procedure: " . $stmt->error, ["module" => "check_db_phone_number_hash"]);
            }

            $stmt->close();
        }

        $result->close();
    } catch (Exception $e) {
        $logger->error("An error occurred: " . $e->getMessage(), ["module" => "check_db_phone_number_hash"]);
    }

    // Sleep before the next iteration
    $logger->debug("Sleeping for $loopDelay seconds...", ["module" => "check_db_phone_number_hash"]);
    sleep($loopDelay);
}