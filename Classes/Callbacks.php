<?php
namespace App\Classes;

use App\Classes\Logger;
use App\Classes\Query;
use DateTime;

class Callbacks {

    public static function C2BRequestValidation()
    {
        $logger = new Logger('mpesa');

        // Read JSON input from request body
        $callbackJSONData = file_get_contents('php://input');

        $ip = self::getClientIP();
        $isAllowed = self::checkIP($ip);

        // FIXME: Enable this when you get all safaricom IPs
        // if ($isAllowed) {

            $callbackData = json_decode($callbackJSONData);

            if (!$callbackData) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid JSON payload"]);
                return;
            }

            // Extract transaction details
            $transactionType    = $callbackData->TransactionType ?? '';
            $transID            = $callbackData->TransID ?? '';
            $transTime          = $callbackData->TransTime ?? '';
            $transAmount        = $callbackData->TransAmount ?? '';
            $businessShortCode  = $callbackData->BusinessShortCode ?? '';
            $billRefNumber      = $callbackData->BillRefNumber ?? '';
            $invoiceNumber      = $callbackData->InvoiceNumber ?? '';
            $orgAccountBalance  = $callbackData->OrgAccountBalance ?? '';
            $thirdPartyTransID  = $callbackData->ThirdPartyTransID ?? '';
            $MSISDN             = $callbackData->MSISDN ?? '';
            $firstName          = $callbackData->FirstName ?? '';
            $middleName         = $callbackData->MiddleName ?? '';
            $lastName           = $callbackData->LastName ?? '';

            // Log request
            $logger->info("Received C2B validation request for MSISDN: " .$MSISDN, json_encode($callbackData) /* json_encode($callbackData, JSON_PRETTY_PRINT)*/ );

            // Prepare response data
            $result = [
                "transTime"         => $transTime,
                "transAmount"       => $transAmount,
                "businessShortCode" => $businessShortCode,
                "billRefNumber"     => $billRefNumber,
                "invoiceNumber"     => $invoiceNumber,
                "orgAccountBalance" => $orgAccountBalance,
                "thirdPartyTransID" => $thirdPartyTransID,
                "MSISDN"            => $MSISDN,
                "firstName"         => $firstName,
                "lastName"          => $lastName,
                "middleName"        => $middleName,
                "transID"           => $transID,
                "transactionType"   => $transactionType
            ];
        // }
        // Send response as JSON
        // header('Content-Type: application/json');
        // echo json_encode($result);
    }

    public static function processC2BRequestConfirmation()
    {
        $logger = new Logger('mpesa');

        $callbackJSONData 	=	file_get_contents('php://input');

        $ip = self::getClientIP();
        $isAllowed = self::checkIP($ip);

        // FIXME: Enable this when you get all safaricom IPs
        // if ($isAllowed) {
            $callbackData 		=	json_decode($callbackJSONData);

            if (!$callbackData) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid JSON payload"]);
                return;
            }

            // Extract transaction details
            $transactionType 	=	$callbackData->TransactionType;
            $transID 			= 	$callbackData->TransID;
            $transTime 			=	$callbackData->TransTime;
            $transAmount 		=	$callbackData->TransAmount;
            $businessShortCode 	=	$callbackData->BusinessShortCode;
            $billRefNumber 		=	$callbackData->BillRefNumber;
            $invoiceNumber 		=	$callbackData->InvoiceNumber;
            $orgAccountBalance 	=	$callbackData->OrgAccountBalance;
            $thirdPartyTransID 	=	$callbackData->ThirdPartyTransID;
            $MSISDN 			=	$callbackData->MSISDN;
            $firstName 			=	$callbackData->FirstName;
            $middleName 		= 	$callbackData->MiddleName??'';
            $lastName 			=	$callbackData->LastName??'';

            // TODO: send notification
            $notifi = [
                "transid"       =>  $transID,
                'msisdn'        =>  $MSISDN,
                'ref'           =>  $transID,
                'amount'        =>  $transAmount,
                'account'       =>  $billRefNumber,
                'customer_name' =>  ucwords($firstName.' '.$middleName.' '.$lastName),
                'shortcode'     =>  $businessShortCode,
                'trans_type'    =>  $transactionType,
                'trans_time'    =>  date('Y-m-d H:i:s',strtotime($transTime)),
                'firstname'     =>  $firstName,
                "middlename"    =>  $middleName,
                "lastname"      =>  $lastName
            ];

            // Log request
            $logger->info("Received C2B Confirmation request for MSISDN: " .$MSISDN, json_encode($callbackData) /* json_encode($callbackData, JSON_PRETTY_PRINT)*/ );
        // }
    }

    public static function processC2B_EXPRESS_ONLINE_RequestCallback()
    {
        $logger = new Logger('mpesa');

        $callbackJSONData   =   file_get_contents('php://input');

        // Log request
        // $logger->info("Received C2B STK /EXPRESS ONLINE request for MSISDN: " .$phoneNumber, $callbackJSONData /* json_encode($callbackData, JSON_PRETTY_PRINT)*/ );

        $ip = self::getClientIP();
        $isAllowed = self::checkIP($ip);

        // FIXME: Enable this when you get all safaricom IPs
        // if ($isAllowed) {

            $callbackData       =   json_decode($callbackJSONData)->Body;
            if (!$callbackData) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid JSON payload"]);
                return;
            }
            $resultCode         =   $callbackData->stkCallback->ResultCode;
            
            // success
            if($resultCode == 0 || $resultCode == '0'){
                $resultDesc         =   $callbackData->stkCallback->ResultDesc;
                $merchantRequestID  =   $callbackData->stkCallback->MerchantRequestID;
                $checkoutRequestID  =   $callbackData->stkCallback->CheckoutRequestID;
                $amount             =   $callbackData->stkCallback->CallbackMetadata->Item[0]->Value;
                $mpesaReceiptNumber =   $callbackData->stkCallback->CallbackMetadata->Item[1]->Value;
                $transactionDate    =   $callbackData->stkCallback->CallbackMetadata->Item[2]->Value;
                $phoneNumber        =   $callbackData->stkCallback->CallbackMetadata->Item[3]->Value;

                $result = array(
                                    "resultDesc"            =>  $resultDesc,
                                    "resultCode"            =>  $resultCode,
                                    "merchantRequestID"     =>  $merchantRequestID,
                                    "checkoutRequestID"     =>  $checkoutRequestID,
                                    "amount"                =>  $amount,
                                    "mpesaReceiptNumber"    =>  $mpesaReceiptNumber,
                                    "transactionDate"       =>  $transactionDate,
                                    "phoneNumber"           =>  $phoneNumber
                                );

            }else{
                $resultDesc         =   $callbackData->stkCallback->ResultDesc;
                $merchantRequestID  =   $callbackData->stkCallback->MerchantRequestID;
                $checkoutRequestID  =   $callbackData->stkCallback->CheckoutRequestID;
            }

            
           
            
            // Log request
            $logger->info("Received C2B STK /EXPRESS ONLINE request for MSISDN: " .$phoneNumber, json_encode($callbackData) /* json_encode($callbackData, JSON_PRETTY_PRINT)*/ );

        // }
    }

    public static function processB2C_RequestCallback()
    {
        $logger = new Logger('mpesa');

        $callbackJSONData	 				=	file_get_contents('php://input');

        $ip = self::getClientIP();
        $isAllowed = self::checkIP($ip);

        // FIXME: Enable this when you get all safaricom IPs
        // if ($isAllowed) {
        $callbackData 						= 	json_decode($callbackJSONData);
        if (!$callbackData) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON payload"]);
            return;
        }
        $resultCode 						=  	$callbackData->Result->ResultCode;

        // success
        if($resultCode == 0 || $resultCode == '0'){
            $resultDesc 						=	$callbackData->Result->ResultDesc;
            $originatorConversationID 			= 	$callbackData->Result->OriginatorConversationID;
            $conversationID 					=	$callbackData->Result->ConversationID;
            $transactionID 						=	$callbackData->Result->TransactionID;
            // $amount 							=	$callbackData->Result->ResultParameters->ResultParameter[0]->Value;
            $transCompletedTime 				=	date('Y-m-d H:i:s');

            // $transCompletedTime 				=	$callbackData->Result->ResultParameters->ResultParameter[5]->Value;
            // $debitPartyCharges 					= 	$callbackData->Result->ResultParameters->ResultParameter[5]->Value;
            // $receiverPartyPublicName 			= 	$callbackData->Result->ResultParameters->ResultParameter[6]->Value;
            // $currency							=	$callbackData->Result->ResultParameters->ResultParameter[7]->Value;

            // if (preg_match('/^(\d+)\s*-\s*(.+)$/', $receiverPartyPublicName, $matches)) {
            //     $msisdn = $matches[1];
            //     $customer_name = $matches[2];

            // }

            // $result=array(
            //     "resultCode"						=>	$resultCode,
            //     "resultDesc"						=>	$resultDesc,
            //     "originatorConversationID"			=>	$originatorConversationID,
            //     "conversationID"					=>	$conversationID,
            //     "transactionID"						=>	$transactionID,
            //     "initiatorAccountCurrentBalance"	=>	$initiatorAccountCurrentBalance,
            //     "debitAccountCurrentBalance"		=>	$debitAccountCurrentBalance,
            //     "amount"							=>	$amount,
            //     "debitPartyAffectedAccountBalance"	=>	$debitPartyAffectedAccountBalance,
            //     "transCompletedTime"				=>	date("Y-m-d H:i:s"),
            //     "debitPartyCharges"					=>	$debitPartyCharges,
            //     "receiverPartyPublicName"			=>	$receiverPartyPublicName,
            //     "currency"							=>	$currency
            // );

            $trans_queue = Query::fetchOne("SELECT * FROM winners_selection.transactions_queue WHERE conversationID = :conversationID", ['conversationID' => $conversationID]);
            $logger->debug("From Transactions Queue", ["module" => "jiwab2c", "trans_queue" => $trans_queue]);
            $shortcode_id = $trans_queue['shortcode_id'];
            $keyword = $trans_queue['keyword'];
            $amount_user_transacted = $trans_queue['amount_user_transacted'];
            $amount_transacted_code = $trans_queue['amount_transacted_code'];
            $amount_transacted_time = $trans_queue['amount_transacted_time'];
            $msisdn = $trans_queue['msisdn'];
            $customer_name = $trans_queue['customer_name'];
            $amount = $trans_queue['amount_won'];
            // $fomtd_amount_transacted_time = new DateTime($amount_transacted_time);

            // $fomtd_transCompletedTime = DateTime::createFromFormat('d.m.Y H:i:s', $transCompletedTime);

            // insert into transactions table
            /**
             * create table transactions
             * (
             * id               bigint unsigned auto_increment primary key,
             * conversionID varchar(500) default null,
             * keyword          varchar(255)                        not null,
             * shortcode_id     int unsigned                        not null,
             * amount_user_transacted decimal(8,2) not null,
             * amount_transacted_code varchar(500) not null,
             * amount_transacted_time timestamp null,   
             * transaction_code varchar(255)                        not null,
             * msisdn           varchar(255)                        not null,
             * customer_name    varchar(120)                        null,
             * trans_time       timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
             * amount           decimal(8, 2)                       not null,
             * status           varchar(255)                        not null,
             * statusDescription varchar(500)                        not null
             * );
             */
            
            $lastInsertId = Query::insert("INSERT INTO winners_selection.transactions (
                                            conversionID,
                                            keyword,
                                            shortcode_id, 
                                            amount_user_transacted,
                                            amount_transacted_code,
                                            amount_transacted_time,
                                            transaction_code,
                                            msisdn,
                                            customer_name,
                                            trans_time,
                                            amount,
                                            `status`,
                                            statusDescription) VALUES (:conversionID,
                                                            :keyword,
                                                            :shortcode_id,
                                                            :amount_user_transacted,
                                                            :amount_transacted_code,
                                                            :amount_transacted_time,
                                                            :transaction_code,
                                                            :msisdn,
                                                            :customer_name,
                                                            :trans_time,
                                                            :amount,
                                                            :status,
                                                            :statusDescription)", [
                'conversionID' => $conversationID,
                'keyword' => $keyword,
                'shortcode_id' => $shortcode_id,
                'amount_user_transacted' => $amount_user_transacted,
                'amount_transacted_code' => $amount_transacted_code,
                'amount_transacted_time' => $amount_transacted_time,
                'transaction_code' => $transactionID,
                'msisdn' => $msisdn,
                'customer_name' => $customer_name,
                'trans_time' => $transCompletedTime,
                'amount' => $amount,
                'status' => '1',
                'statusDescription' => 'success'
            ]);

            if($lastInsertId){
                $logger->debug("Last Insert Id", ["module" => "jiwab2c", "lastInsertId" => $lastInsertId]);
                // delete from transactions queue
                $affectedRows = Query::updateDelete("DELETE FROM winners_selection.transactions_queue WHERE conversationID = :conversationID", [
                 'conversationID' => 'conversationID'
                 ]);
            }

        }else{
            $resultDesc 						=	$callbackData->Result->ResultDesc;
            $originatorConversationID 			= 	$callbackData->Result->OriginatorConversationID;
            $conversationID 					=	$callbackData->Result->ConversationID;
            $transactionID 						=	$callbackData->Result->TransactionID;

            $trans_queue = Query::fetchOne("SELECT * FROM winners_selection.transactions_queue WHERE conversationID = :conversationID", ['conversationID' => $conversationID]);
            $shortcode_id = $trans_queue['shortcode_id'];
            $keyword = $trans_queue['keyword'];
            $amount_user_transacted = $trans_queue['amount_user_transacted'];
            $amount_transacted_code = $trans_queue['amount_transacted_code'];
            $amount_transacted_time = $trans_queue['amount_transacted_time'];
            // $fomtd_amount_transacted_time = new DateTime($amount_transacted_time);
            $customer_name = $trans_queue['customer_name'];
            $msisdn = $trans_queue['msisdn'];
            $amount = $trans_queue['amount_won'];

            $transCompletedTime = date("Y-m-d H:i:s"); 

            $lastInsertId = Query::insert("INSERT INTO winners_selection.transactions (
                                conversionID,
                                keyword,
                                shortcode_id, 
                                amount_user_transacted,
                                amount_transacted_code,
                                amount_transacted_time,
                                transaction_code,
                                msisdn,
                                customer_name,
                                trans_time,
                                amount,
                                status,
                                statusDescription) VALUES (:conversionID,
                                                :keyword,
                                                :shortcode_id,
                                                :amount_user_transacted,
                                                :amount_transacted_code,
                                                :amount_transacted_time,
                                                :transaction_code,
                                                :msisdn,
                                                :customer_name,
                                                :trans_time,
                                                :amount,
                                                :status,
                                                :statusDescription)", [
                'conversionID' => $conversationID,
                'keyword' => $keyword,
                'shortcode_id' => $shortcode_id,
                'amount_user_transacted' => $amount_user_transacted,
                'amount_transacted_code' => $amount_transacted_code,
                'amount_transacted_time' => $amount_transacted_time,
                'transaction_code' => $transactionID,
                'msisdn' => $msisdn,
                'customer_name' => $customer_name,
                'trans_time' => $transCompletedTime,
                'amount' => $amount,
                'status' => '0',
                'statusDescription' => $resultDesc
                ]);

                if($lastInsertId){
                    // delete from transactions queue
                    $affectedRows = Query::updateDelete("DELETE FROM winners_selection.transactions_queue WHERE conversationID = :conversationID", [
                        'conversationID' => 'conversationID'
                    ]);
                }


        }
        // Log request
        $logger->info("Received B2C callback : ", json_encode($callbackData) /* json_encode($callbackData, JSON_PRETTY_PRINT)*/ );

        // }

    }


    public static function checkIP($ip): bool
    {
        $logger = new Logger('mpesa');
        // TODO: whitelist more safaricom IPs
        $allowedIps = [     
            '127.0.0.1',
            '198.211.113.248',
            '196.201.214.200',
            '196.201.214.206',
            '196.201.213.114',
            '196.201.214.207',
            '196.201.214.208',
            '196.201.213.44',
            '196.201.212.127',
            '196.201.212.128',
            '196.201.212.129',
            '196.201.212.132',
            '196.201.212.136',
            '196.201.212.138',
            '196.201.212.74',
            '10.184.20.31',
            '10.197.136.63',
            // '41.90.172.167', // timon IP
        ];
        
        if (!in_array($ip, $allowedIps)) {
            $logger->info('Blocked IP ADDRESS : ' . $ip);
            return false; // IP is not allowed
        }
        
        return true; // IP is allowed
    }

    // FIXME: Remove this test code
    // public static function testIP()
    // {
    //     $ip = self::getClientIP();
    //     $isAllowed = self::checkIP($ip);

    //     if ($isAllowed) {
    //         echo "Test Passed: IP $ip is allowed.\n";
    //     } else {
    //         echo "Test Failed: IP $ip is blocked.\n";
    //     }
    // }

    private static function getClientIP(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    // 
    public function notification($detail)
    {
        
                $moneyfromnumber    =   $detail['msisdn'];
                $ref                =   $detail['ref'];
                $amount             =   $detail['amount'];
                $account            =   $detail['account'];
                $channel            =   $detail['trans_type'];
                
    }

}
