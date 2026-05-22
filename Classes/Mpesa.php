<?php
namespace App\Classes;

use App\Classes\Logger;

class Mpesa{
    private $certi;
    private $token_link;
    private $checkout_querylink;
    private $reversal_link;
    private $balance_link;
    private $transtat_link;
    private $b2b_link;
    
    // C2B
    private $c2b_regiterUrl;
    private $c2b_transactionUrl;
    private $c2b_confirmationUrl;
    private $c2b_validationUrl;
    private $c2b_express_online_processlink;
    private $c2b_express_online_callbackurl;

    // B2C
    private $b2c_Url;
    private $b2c_timeoutURL;
    private $b2c_resultURL;

    private $logger;

    public function __construct(){
        $this->logger = new Logger('mpesa');

        $config = require __DIR__."/../Config/mpesa.php";

        $env = $config['environment'];
        // $this->cert = ($config[$env] == "production") ? __DIR__."/../Resource/Mpesa_public_cert.cer" : __DIR__."/../Resource/Mpesa_public_sandbox_cert.cer";

        $this->certi = $config[$env]['cert'];
        $this->token_link = $config[$env]['token_link'];
        $this->checkout_querylink = $config[$env]['checkout_querylink'];
        $this->reversal_link = $config[$env]['reversal_link'];
        $this->balance_link = $config[$env]['balance_link'];
        $this->transtat_link = $config[$env]['transtat_link'];
        $this->b2b_link = $config[$env]['b2b_link'];
        $this->b2c_Url = $config[$env]['b2c_Url'];
        // C2B
        $this->c2b_regiterUrl                   = $config[$env]['c2b_regiterUrl'];
        $this->c2b_transactionUrl               = ($config[$env] == "development") ? $config[$env]['c2b_transactionUrl'] : "";
        $this->c2b_confirmationUrl              = $config['c2b_confirmationUrl'];
        $this->c2b_validationUrl                = $config['c2b_validationUrl'];
        $this->c2b_express_online_processlink   = $config[$env]['c2b_express_online_processlink'];
        $this->c2b_express_online_callbackurl   = $config['c2b_express_online_callbackurl'];

        // B2C
        $this->b2c_timeoutURL                   = $config["b2c_timeoutURL"];
        $this->b2c_resultURL                    = $config["b2c_resultURL"];


    }

    // FIXME: Remove this code
    public function test($request){
        // return $this->c2b_confirmationUrl;

        return $this->cert($request['credential']);
    }
    public function readFile($file)
    {
        $fp         =   fopen($file,"r");
        $privFile    =   fread($fp,filesize($file));
        fclose($fp);
        return $privFile;
    }

    public function cert($plaintext)
    {
        $publicKey  =   $this->readFile($this->certi);
        openssl_get_publickey($publicKey);
        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        return  base64_encode($encrypted);
    }

    public function generatetoken($request)
    {
        if (!empty($request)) {
            $url = $this->token_link;
            $credentials = base64_encode($request['consumerkey'] . ':' . $request['consumersecret']);

            $attempt = 0;
            $maxAttempts = 3;

            while ($attempt < $maxAttempts) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $curl_response = curl_exec($curl);
                curl_close($curl);

                $decoded_response = json_decode($curl_response);

                if (isset($decoded_response->access_token) && isset($decoded_response->expires_in)) {
                    return $decoded_response;
                }

                $attempt++;
                $this->logger->warning("Token generation failed, retrying... Attempt $attempt");

                if ($attempt < $maxAttempts) {
                    sleep($attempt); // Exponential backoff
                }
            }

            $this->logger->error("Token generation failed after $maxAttempts attempts.");
        }
        return null;
    }

    public function token($request)
    {
        if (!empty($request)) {
            if (!Cache::has('accessToken')) {
                $token = $this->generatetoken($request);
                
                if ($token) {
                    $this->logger->info('New accessToken generated: ', (array) $token);
                    
                    Cache::add('accessToken', $token->access_token, $token->expires_in);
                } else {
                    $this->logger->error("Failed to generate new accessToken after retries.");
                    return null;
                }
            }

            $this->logger->info("AccessToken from Cache: " . Cache::get('accessToken'));
            return Cache::get('accessToken');
        }
        return null;
    }

    public function generateUuid()
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

    public function C2B_REGISTER($request,$status='Completed')
    {
        $url 	= 	$this->c2b_regiterUrl;
        $curl 	= 	curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->token($request)));
        $curl_post_data = array(
                                    'ShortCode' 		=> $request['shortcode'],
                                    'ResponseType' 		=> $status,
                                    'ConfirmationURL' 	=> $this->c2b_confirmationUrl,
                                    'ValidationURL' 	=> $this->c2b_validationUrl
                                );
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);
        $this->logger->error($curl_response);
        return $curl_response;
    }

    public function C2B($request)
    {
        $url 	= $this->c2b_transactionUrl;
        $curl 	= curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->token($request)));
        $curl_post_data = array(
                                    "ShortCode"		=>	$request['shortcode'],
                                    /**
                                     * commandID from $request
                                     * CustomerPayBillOnline
                                     * CustomerBuyGoodsOnline
                                     * 
                                     */
                                    "CommandID"		=>	$request["commandID"],
                                    "Amount"		=> 	$request['amount'],
                                    "Msisdn"		=>	$request['msisdn'],
                                    "BillRefNumber"	=>	$request['ref']
                                );
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);
        return $curl_response;
    }

    public function C2B_EXPRESSONLINE($request)
    {
        date_default_timezone_set("Africa/Nairobi");
        $url 	= $this->c2b_express_online_processlink;
        $curl 	= curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->token($request)));
        $timestamp 	=	date('YmdHis');
        $password 	=	base64_encode($request['shortcode'].$request['passkey'].$timestamp);
        $curl_post_data = array(
                                    'BusinessShortCode' 	=> $request['shortcode'],
                                    'Password' 				=> $password,
                                    'Timestamp' 			=> $timestamp,
                                    'TransactionType' 		=> 'CustomerPayBillOnline',
                                    'Amount' 				=> $request['amount'],
                                    'PartyA' 				=> $request['msisdn'],
                                    'PartyB' 				=> $request['shortcode'],
                                    'PhoneNumber' 			=> $request['msisdn'],
                                    'CallBackURL' 			=> $this->c2b_express_online_callbackurl,
                                    'AccountReference' 		=> $request['ref'],
                                    'TransactionDesc' 		=> $request['desc']
                                );
        $data_string 	= 	json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response 	= 	curl_exec($curl);
        $data 			=	(array)json_decode($curl_response);
        $data["refno"]	=	$curl_post_data['AccountReference'];
        return $curl_response;

    }


    public function B2C($request)
    {
        $url 	= $this->b2c_Url;
        $curl 	= curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->token($request)));
        $curl_post_data = array(
                                    "OriginatorConversationID"      => $this->generateUuid(),
                                    "InitiatorName"                 => $request["initiator"],
                                    "SecurityCredential"            => $this->cert($request['credential']), // TODO: use this after testing
                                    // "SecurityCredential"            => $request['credential'], // for testing
                                    "CommandID"                     => $request["commandID"],
                                    "Amount"                        => $request["amount"],
                                    "PartyA"                        => $request["shortcode"],
                                    "PartyB"                        => $request["msisdn"],
                                    "Remarks"                       => $request["remarks"],
                                    "QueueTimeOutURL"               => $this->b2c_timeoutURL,
                                    "ResultURL"                     => $this->b2c_resultURL,
                                    "Occasion"                      => "",
                                );



        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);
        print_r($curl_response);

        echo $curl_response;
    }

    

}