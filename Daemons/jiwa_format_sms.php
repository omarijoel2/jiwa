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
    $logger->error("DB Connection failed: " . $mysqli->connect_error, ["module" => "jiwa_format_sms"]);
    exit(1);
}

while (true){
    try{
        $query = "SELECT unhash_queue.id, 
                unhash_queue.sms, 
                unhash_queue.customer_name, 
                unhash_queue.amount_transacted,
                unhash_queue.amount_won,
                unhash_queue.keyword,
                shortcodes.shortcode FROM winners_selection.unhash_queue LEFT JOIN shortcodes ON unhash_queue.shortcode = shortcodes.id WHERE unhash_queue.is_checked_in_db = 1 and unhash_queue.is_unhashed = 1 and unhash_queue.is_sms_decode= 0 and (unhash_queue.keyword like '%test%' or unhash_queue.keyword like '%jiwa%') LIMIT 10";
        $result = $mysqli->query($query);

        
        if (!$result) {
            $logger->error("Query failed: " . $mysqli->error, ["module" => "jiwa_format_sms"]);
            sleep($loopDelay);
            continue;
        }

        if ($result->num_rows > 0) {
            $logger->debug('Message(s) found, processing...', ["module" => "jiwa_format_sms"]);

            foreach ($result as $row) {
                $id = $row['id'];
                $sms = $row['sms'];

                $customer_name = $row['customer_name'];
                $amount_transacted = $row['amount_transacted'];
                $amount_won = $row['amount_won'];
                $keyword = 'jiwa';
                $shortcode = $row['shortcode'];
                

                $message = str_replace(['{username}', '{sent_amount}','{winning_amount}', '{keyword}', '{paybill}'], 
                            [$customer_name, $amount_transacted, $amount_won, $keyword, $shortcode], 
                            $sms);

                echo $message;

                $query2 = "UPDATE winners_selection.unhash_queue SET sms = ? ,is_sms_decode=1 where id=?";
                $stmt2 = $mysqli->prepare($query2);
                if (!$stmt2) {
                    $logger->error("Failed to prepare statement for unhash_queue update: " . $mysqli->error, ["module" => "jiwa_format_sms"]);
                    continue; 
                }
                $stmt2->bind_param("si", $message, $id);
                $stmt2->execute();
                $stmt2->close();                

            }
            $result->close();
        }
        $logger->debug('Sleeping for '.$loopDelay.' seconds...', ["module" => "jiwa_format_sms"]);
        sleep($loopDelay);
    }catch(Exception $e){
        $logger->error("An error occurred: " . $e->getMessage(), ["module" => "jiwa_format_sms"]);
        sleep($loopDelay);
    }
}