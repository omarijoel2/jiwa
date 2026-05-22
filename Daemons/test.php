<?php
date_default_timezone_set('Africa/Nairobi');
// Database connection
$config = require_once __DIR__ . '/../Config/db.php';
$database = $config['database'];
$host = $config[$database]['host'];
$username = $config[$database]['username'];
$password = $config[$database]['password'];
$database_name = $config[$database]['database_name'];

$mysqli = new mysqli("p:" . $host, $username, $password, $database_name);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


$query1 = "SELECT * FROM winners_selection.unhash_queue WHERE is_checked_in_db = 1 AND is_unhashed = 1 AND is_sent_sms = 1 AND is_winner=1  LIMIT 10";
    $result1 = $mysqli->query($query1);

    if (!$result1) {
        echo "Query failed: " . $mysqli->error;
    }

    if ($result1->num_rows > 0) {
        // $logger->debug('Winner(s) found, processing...', ["module" => "jiwab2c"]);

        foreach ($result1 as $row) {
            $id = $row['id'];
            $msisdn = $row['unhashed_msisdn'];
            $shortcode_id = $row['shortcode'];
            $keyword = $row['keyword'];
            $amount_won = $row['amount_won'];
            $amount_user_transacted  = $row['amount_transacted'];
            $amount_transacted_code = $row['transaction_code'];
            $customer_name = $row['customer_name'];
            $amount_transacted_time = $row['timestamp'];
            $amount_transacted_code = $row['transaction_code'];
            $amount_transacted_time = $row['timestamp'];
            $created_at = date('Y-m-d H:i:s');

            // Prepare the SQL statement
            $sql = "INSERT INTO transactions_queue (
                        conversationID, responseCode, responseDescription, status, 
                        shortcode_id, keyword, msisdn, amount_won, amount_user_transacted, 
                        customer_name, amount_transacted_code, amount_transacted_time, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                die("Prepare failed: " . $mysqli->error);
            }

            // Bind parameters
            $conversationID = 'AG_20250228_20402eb66589643d5e75';
            $responseCode = '0';
            $responseDescription = 'Accept the service request successfully.';
            $status = 1;
            // $shortcode_id = 29;
            // $keyword = 'jiwa';
            // $msisdn = '254710474283';
            // $amount_won = 100.00;
            // $amount_user_transacted = 50.00;
            // $customer_name = 'Timon';
            // $amount_transacted_code = 'TESTCODE';

            // Assign timestamp variables (from your variables)
            // $amount_transacted_time = '2025-02-28 21:37:41'; // Replace with your variable
            // $created_at = '2025-02-28 21:37:41'; // Replace with your variable

            $stmt->bind_param("sssisssddssss", 
                $conversationID, 
                $responseCode, 
                $responseDescription, 
                $status, 
                $shortcode_id, 
                $keyword, 
                $msisdn, 
                $amount_won, 
                $amount_user_transacted, 
                $customer_name, 
                $amount_transacted_code, 
                $amount_transacted_time, 
                $created_at
            );

            // Execute the statement
            if ($stmt->execute()) {
                echo "Record inserted successfully.";
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close statement and connection
            $stmt->close();
            $mysqli->close();


        }
    }
?>
