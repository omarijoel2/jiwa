<?php
include 'jiwa_accessToken.php';
include 'jiwa_securitycredential.php';
$AccountBalanceUrl = 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query';
$InitiatorName = 'qdsolutDevB2c';
$pass = "yEuxNJWYZy52^%2j";
$BusinessShortCode = "3998571";
$request_data = array(
    'Initiator' => $InitiatorName,
    'SecurityCredential' => $SecurityCredential,
    'CommandID' => 'AccountBalance',
    'PartyA' => $BusinessShortCode,
    'IdentifierType' => '4',
    'Remarks' => 'ok',
    'QueueTimeOutURL' => 'https://jiwa.hezsun.com/api/b2cbalanceTimeOut',
    'ResultURL' => 'https://jiwa.hezsun.com/api/b2cbalance',
);
$data_string = json_encode($request_data);
$headers = array(
    'Content-Type: application/json',
    'Authorization:Bearer ' . $access_token
);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $AccountBalanceUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);
echo $response;
?>