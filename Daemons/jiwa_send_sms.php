<?php
require __DIR__ . "/../Config/autoload.php";
date_default_timezone_set('Africa/Nairobi');

use App\Classes\Logger;
$loopDelay = 10;

$logger = new Logger("daemon");

//DB
$config = require_once __DIR__ . '/../Config/db.php';
$database = $config['database'];
$host = $config[$database]['host'];
$username = $config[$database]['username'];
$password = $config[$database]['password'];
$database_name = $config[$database]['database_name'];

$mysqli = new mysqli("p:" . $host, $username, $password, $database_name);

if ($mysqli->connect_error) {
    $logger->error("DB Connection failed: " . $mysqli->connect_error, ["module" => "jiwa_send_sms"]);
    exit(1);
}

while (true) try {
    $query = "SELECT * FROM winners_selection.unhash_queue WHERE is_sms_decode = 1 AND is_sent_sms = 0 and (keyword like '%jiwa%' or keyword like '%test%')  LIMIT 10";
    $result = $mysqli->query($query);

    if (!$result) {
        $logger->error("Query failed: " . $mysqli->error, ["module" => "jiwa_send_sms"]);
        sleep($loopDelay);
        continue;
    }

    if ($result->num_rows > 0) {
        $logger->debug('Message(s) found, processing...', ["module" => "jiwa_send_sms"]);

        foreach ($result as $row) {
            $id = $row['id'];
            $msisdn = $row['unhashed_msisdn'];
            $message = $row['sms'];
            $is_winner = $row['is_winner'];

            $url = 'https://developer.taifamobile.co.ke/auth/token';
            $username = "dimacsint@gmail.com";
            $password = "FgI1xwWuBnwbAwG*";
            $data = array(
                'username' => $username,
                'password' => $password
            );
            $options = array(
                'http' => array(
                    'header'  => "Content-Type: application/json\r\n",
                    'method'  => 'POST',
                    'content' => json_encode($data)
                )
            );
            $context  = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === FALSE) 
            {
                $logger->error("Error occurred while fetching token data from server ", ["module" => "jiwa_send_sms"]);
                sleep($loopDelay);
                continue;
            }

            $tken = json_decode($response, true);
            $token = $tken['token'];
            $local_time = date('Y-m-d H:i:s');
            $datetime = new DateTime($local_time, new DateTimeZone(date_default_timezone_get()));
            $datetime->setTimezone(new DateTimeZone("UTC"));

            $sms_Url = "https://developer.taifamobile.co.ke/api/sms/bulk";
 
            $sms_data = array(
                'title' => 'Jiwa SMS',
                'content' => $message,
                'addresses' => [$msisdn],
                'sender' => 'DIMACS INT',
                'sendTime' => $datetime->format("Y-m-d\TH:i:s.u\Z"),
                'dnd' => 0,
                "isDeliveryReport"=> false,
                "callbackUrl"=> "https://test.com"
            
            );


            // send sms if not message body
            if(!empty($message)){
                $payload = json_encode($sms_data);

                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: ' . $token
                );

                $ch = curl_init($sms_Url);

                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $sms_response = curl_exec($ch);
                curl_close($ch);

                $response_data = json_decode($sms_response, true);
                if (isset($response_data['statusCode']) && $response_data['statusCode'] === 0) {
                    $logger->debug("SMS sent successfully. Request ID: " . $response_data['requestId'], ["module" => "jiwa_send_sms", 'data' => $sms_data]);

                    if($is_winner == 1 || $is_winner == '1'){
                        $stmt3 = $mysqli->prepare("UPDATE winners_selection.unhash_queue SET is_sent_sms = 1 WHERE id = ?");
                        $stmt3->bind_param("i", $id);
                        $stmt3->execute();
                        $stmt3->close();
                    }else{
                        $stmt3 = $mysqli->prepare("DELETE FROM winners_selection.unhash_queue WHERE id = ?");
                        $stmt3->bind_param("i", $id);
                        $stmt3->execute();
                        $stmt3->close();
                    }
                    

                }

            }
            
        }
    }

    $logger->debug('No Message(s) found, sleeping for '.$loopDelay.' seconds...', ["module" => "jiwa_send_sms"]);
    sleep($loopDelay);
}catch (Exception $e) {
    $logger->error("An error occurred: " . $e->getMessage());
}
 