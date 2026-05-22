<?php

require_once __DIR__ . '/../Config/autoload.php';
date_default_timezone_set("Africa/Nairobi");
include 'jiwa_securitycredential.php';


use App\Classes\Logger;

$logger = new Logger("daemon");

function generateUuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        random_int(0, 0xffff), random_int(0, 0xffff),
        random_int(0, 0xffff),
        random_int(0, 0x0fff) | 0x4000, // UUID version 4
        random_int(0, 0x3fff) | 0x8000, // UUID variant
        random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
    );
}



//DB
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

$maxRetries = 3;
$retryCount = 0;
$loopDelay = 30;

while (true) try {
    $query = "SELECT * FROM winners_selection.unhash_queue WHERE is_checked_in_db = 1 AND is_unhashed = 1 AND is_sent_sms = 1 AND is_winner=1 and keyword like '%jiwa%'  LIMIT 10";
    $result = $mysqli->query($query);

    if (!$result) {
        $logger->error("Query failed: " . $mysqli->error, ["module" => "jiwab2c"]);
        sleep($loopDelay);
        continue;
    }

    if ($result->num_rows > 0) {
        $logger->debug('Winner(s) found, processing...', ["module" => "jiwab2c"]);

        foreach ($result as $row) {
            include 'jiwa_accessToken.php';
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


            $b2c_url = 'https://api.safaricom.co.ke/mpesa/b2c/v3/paymentrequest';
            $InitiatorName = 'qdsolutDevB2c';
            $pass = "yEuxNJWYZy52^%2j";
            $BusinessShortCode = "3998571";
            // $phone = "254710474283";
            // $amountsend = 10;
            $CommandID = 'PromotionPayment'; // SalaryPayment, BusinessPayment, PromotionPayment
            $Amount = floatval($amount_won);
            $PartyA = $BusinessShortCode;
            $PartyB = $msisdn;
            $Remarks = 'Jiwa Campaign';
            $QueueTimeOutURL = 'https://jiwa.hezsun.com/api/b2ccallback';
            $ResultURL = 'https://jiwa.hezsun.com/api/b2ccallback';
            $Occasion = 'Online Payment';
            $uuid = generateUuid();
            /* Main B2C Request to the API */
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $b2c_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token]);
            $curl_post_data = array(
                "OriginatorConversationID"      => generateUuid(),
                'InitiatorName' => $InitiatorName,
                'SecurityCredential' => $SecurityCredential,
                'CommandID' => $CommandID,
                'Amount' => $Amount,
                'PartyA' => $PartyA,
                'PartyB' => $PartyB,
                'Remarks' => $Remarks,
                'QueueTimeOutURL' => $QueueTimeOutURL,
                'ResultURL' => $ResultURL,
                'Occasion' => $Occasion
            );
            $data_string = json_encode($curl_post_data);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            $curl_response = curl_exec($curl);
            // echo $curl_response;

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            // curl_close($curl);
            $logger->debug("CURL Response: $curl_response", ["module" => "jiwab2c"]);

                $data = json_decode($curl_response, true);

                $logger->debug($data['errorCode'], ["module" => "jiwab2c"]);

                // Extract values
                $conversationID = $data['ConversationID'];
                $originatorConversationID = $data['OriginatorConversationID'];
                $responseCode = $data['ResponseCode'];
                $responseDescription = $data['ResponseDescription'];
                $created_at = date('Y-m-d H:i:s');
                $errorCode = $data['errorCode'];
                $errorMessage = $data['errorMessage'];

                if ($responseCode == 0 || $responseCode == '0') {
                    /**
                     * CREATE TABLE winners_selection.transactions_queue(
                     *      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                     *      conversationID VARCHAR(500) DEFAULT NULL,
                     *      responseCode VARCHAR(255) DEFAULT NULL,
                     *      responseDescription VARCHAR(500) DEFAULT NULL,
                     *      status TINYINT(1) DEFAULT 1, -- 0 = not sent, 1 = sent
                     *      shortcode_id     int unsigned DEFAULT NULL,
                     *      keyword varchar(500) DEFAULT NULL,
                     *      msisdn varchar(255) NOT NULL,
                     *      amount_won decimal(8, 2) not null,
                     *      amount_user_transacted decimal(8,2) not null,
                     *      amount_transacted_code VARCHAR(500) NOT NULL,
                     *      customer_name VARCHAR(500) NOT NULL,
                     *      amount_transacted_time timestamp null,
                     *      created_at timestamp null
                     *
                     * );
                     */
                    $status = 1;
                    // save conversationID to cdr with transaction details
                    // save transaction data with conversation ID to DB (transaction table, mark default transaction_status as processing)
                    //
                    /**
                     * INSERT INTO winners_selection.transactions_queue (
                     * conversationID,
                     * responseCode,
                     * responseDescription,
                     * status,
                     * shortcode_id,
                     * keyword,
                     * msisdn,
                     * amount_won,
                     * amount_user_transacted,
                     * customer_name,
                     * amount_transacted_code,
                     * amount_transacted_time,
                     * created_at) VALUES (
                     * 'AG_20250228_20402eb66589643d5e75',
                     * '0',
                     * 'Accept the service request successfully.',
                     * 1,
                     * '29',
                     * 'jiwa',
                     * '254710474283',
                     * 100,
                     * 50,
                     * 'Timon',
                     * 'TESTCODE',
                     *  date('Y-m-d H:i:s') --'2025-02-28 21:37:41',
                     * date('Y-m-d H:i:s') --'2025-02-28 21:37:41'
                     * );
                     */
                    $query2 = "INSERT INTO winners_selection.transactions_queue (
                        conversationID, responseCode, responseDescription, status, 
                        shortcode_id, keyword, msisdn, amount_won, amount_user_transacted, 
                        customer_name, amount_transacted_code, amount_transacted_time, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt2 = $mysqli->prepare($query2);
                    if (!$stmt2) {
                        $logger->error("Failed to prepare statement for transactions_queue insert: " . $mysqli->error, ["module" => "jiwab2c"]);
                        continue; // Skip processing for this row
                    }
                    $stmt2->bind_param("sssisssddssss", 
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
                    $stmt2->execute();
                    $stmt2->close();

                    $stmt3 = $mysqli->prepare("DELETE FROM  winners_selection.unhash_queue WHERE id = ?");
                    $stmt3->bind_param("i", $id);
                    $stmt3->execute();
                    $stmt3->close();
                }
                else {
                    //{
                    //   "requestId": "11728-2929992-1",
                    //   "errorCode": "401.002.01",
                    //   "errorMessage": "Error Occurred - Invalid Access Token - BJGFGOXv5aZnw90KkA4TDtu4Xdyf"
                    //}

                    // save the failed transaction data without the conversation ID to cdr table DB (mark default transaction_status as failed with reason)
                    //
                    // $data = json_decode($curl_response, true);
                    
                    $status = 0;
                    $created_at = date('Y-m-d H:i:s');

                    $query2 = "INSERT INTO winners_selection.transactions_queue (
                        conversationID,
                         responseCode, responseDescription, status, 
                        shortcode_id, keyword, msisdn, amount_won, amount_user_transacted, 
                        customer_name, amount_transacted_code, amount_transacted_time, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt2 = $mysqli->prepare($query2);
                    if (!$stmt2) {
                        $logger->error("Failed to prepare statement for transactions_queue insert: " . $mysqli->error, ["module" => "jiwab2c"]);
                        continue; // Skip processing for this row
                    }
                    $stmt2->bind_param("sssisssddssss",
                        $uuid, 
                        "error", 
                        $errorMessage, 
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
                    $stmt2->execute();
                    $stmt2->close();

                    $stmt3 = $mysqli->prepare("DELETE FROM  winners_selection.unhash_queue WHERE id = ?");
                    $stmt3->bind_param("i", $id);
                    $stmt3->execute();
                    $stmt3->close();
                }
            

        }

}

    $logger->debug('No winner(s) found, sleeping for '.$loopDelay.' seconds...', ["module" => "jiwab2c"]);
    sleep($loopDelay);
}catch (Exception $e) {
    $logger->error("An error occurred: " . $e->getMessage());
}
