<?php
require __DIR__ . "/../Config/autoload.php";
date_default_timezone_set('Africa/Nairobi');
use App\Classes\Logger;

$logger = new Logger("daemon");

$maxRetries = 3;
$retryCount = 0;
$loopDelay = 30;

function hashDecode($data)
{
    global $logger;
    $url = "https://transactions.prasams.com/api/v1/decode-hash";
    $headers = [
        "Content-Type: application/json",
        "x-api-key: 6184c7b498807c7a5167e60dde3a79ee58a71ddd3b02635aa9c5228fa08fc99c"
    ];
    $maxRetries = 3;

    $retryCount = 0;
    while ($retryCount < $maxRetries) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);
        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }
        $retryCount++;

        sleep(1); // Optional delay before retrying
    }
    return false;
}


$config = require_once __DIR__ . '/../Config/db.php';
$database = $config['database'];
$host = $config[$database]['host'];
$username = $config[$database]['username'];
$password = $config[$database]['password'];
$database_name = $config[$database]['database_name'];

$mysqli = new mysqli("p:" . $host, $username, $password, $database_name);

if ($mysqli->connect_error) {
    $logger->error("DB Connection failed: " . $mysqli->connect_error, ["module" => "hash_decoder"]);
    exit(1);
}

while (true) try {
    $logger->debug("Checking for phone numbers in queue...", ["module" => "hash_decoder"]);
    $query = "SELECT * FROM winners_selection.unhash_queue WHERE is_checked_in_db = 1 AND is_unhashed = 0 LIMIT 10";
    $result = $mysqli->query($query);

    if (!$result) {
        $logger->error("Query failed: " . $mysqli->error, ["module" => "hash_decoder"]);
        sleep($loopDelay);
        continue;
    }

    if ($result->num_rows > 0) {
        $logger->debug('Phone number(s) found, processing...', ["module" => "hash_decoder"]);

        foreach ($result as $row) {
            $id = $row['id'];
            $hashed_msisdn = $row['hashed_msisdn'];
            $keyword = $row['keyword'];
            $shortcode = $row['shortcode'];
            $customer_name = $row['customer_name'];
            $transaction_code = $row['transaction_code'];

            // Prepare data for hashDecode
            $data = [
                "hash" => $hashed_msisdn
            ];

            $response = hashDecode($data);
            $logger->debug($response, ["module" => "hash_decoder"]);

            if ($response['status'] == 'success') {
                $unhashed_msisdn = $response['phone'];

                // 1. Update unhash_queue
                $query1 = "UPDATE winners_selection.unhash_queue SET unhashed_msisdn = ?, is_unhashed = 1 WHERE id = ?";
                $stmt1 = $mysqli->prepare($query1);
                if (!$stmt1) {
                    $logger->error("Failed to prepare statement for unhash_queue update: " . $mysqli->error, ["module" => "hash_decoder"]);
                    continue; // Skip processing for this row
                }
                $stmt1->bind_param("si", $unhashed_msisdn, $id);
                $stmt1->execute();
                $stmt1->close();

                // 2. Insert into contacts
                $query2 = "INSERT INTO winners_selection.contacts (hashed_msisdn, unhashed_msisdn, shortcode, keyword, customer_name)
                            VALUES (?, ?, ?, ?, ?)";
                $stmt2 = $mysqli->prepare($query2);
                if (!$stmt2) {
                    $logger->error("Failed to prepare statement for contacts insert: " . $mysqli->error, ["module" => "hash_decoder"]);
                    continue; // Skip processing for this row
                }
                $stmt2->bind_param("sssss", $hashed_msisdn, $unhashed_msisdn, $shortcode, $keyword, $customer_name);
                $stmt2->execute();
                $stmt2->close();

                // 3. Update winners_log
                $query3 = "UPDATE winners_selection.winners_log SET msisdn = ? WHERE transaction_code = ?";
                $stmt3 = $mysqli->prepare($query3);
                if (!$stmt3) {
                    $logger->error("Failed to prepare statement for winners_log update: " . $mysqli->error, ["module" => "hash_decoder"]);
                    continue; // Skip processing for this row
                }
                $stmt3->bind_param("ss", $unhashed_msisdn, $transaction_code);
                $stmt3->execute();
                $stmt3->close();

            } else if ($response['status'] == 'error') {
                $logger->error('Error decoding hash: ' . $response['description'], ["module" => "hash_decoder"]);
            }
        }
        $result->close();

    }
    $logger->debug('Sleeping for '.$loopDelay.' seconds...', ["module" => "hash_decoder"]);
    sleep($loopDelay);
}catch (Exception $e) {
    $logger->error("An error occurred: " . $e->getMessage());
}