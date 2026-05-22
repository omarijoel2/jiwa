<?php
require __DIR__ . "/../Config/autoload.php";
date_default_timezone_set('Africa/Nairobi');

use App\Classes\Logger;

$filename = "/var/www/html/win/Daemons/token.json";

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

if ($response === FALSE) {
    die('Error occurred while fetching data from server');
}

$result = $response;


// Open or create the file
$file = fopen($filename, "w");

fwrite($file, $result."\n");

// Close the file
fclose($file);


$tken = json_decode($result, true);
$token = $tken['token'];

// echo $token;

$local_time = date('Y-m-d H:i:s');
$datetime = new DateTime($local_time, new DateTimeZone(date_default_timezone_get()));
$datetime->setTimezone(new DateTimeZone("UTC"));

$sms_Url = "https://developer.taifamobile.co.ke/api/sms/bulk";

$sms_data = array(
    'title' => 'Transaction SMS',
    'content' => 'Test Message',
    'addresses' => ['254710474283'],
    'sender' => 'USHINDI-B',
    'sendTime' => $datetime->format("Y-m-d\TH:i:s.u\Z"),
    'dnd' => 0,
    "isDeliveryReport"=> false,
    "callbackUrl"=> "https://test.com"

);

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

echo $sms_response;
