<?php
return [
    'environment' => 'production', // Change to 'production' for live

    // C2B
    'c2b_confirmationUrl'	                => 	APP_URL."/api/c2bconfirmation",
    'c2b_validationUrl'		                => 	APP_URL."/api/c2bvalidation",
    'c2b_express_online_callbackurl'        =>	APP_URL."/api/c2bexpresscallback",

    // B2C
    
    'reversal_resultUrl'		=>	APP_URL."/api/reversalcallback",
    'reversal_timeoutURL'	    =>	APP_URL."/api/reversalcallback",
    'balance_timeoutUrl'		=>	APP_URL."/api/accountbalballback",
    'balance_resultUrl'		    =>	APP_URL."/api/accountbalcallback",
    'transtat_resultURL'		=>	APP_URL."/api/transstatcallback",
    'transtat_timeoutURL'	    =>	APP_URL."/api/transstatcallback",
    'b2b_timeoutURL'			=>	APP_URL."/api/b2bcallback",
    'b2b_resultURL'			    =>	APP_URL."/api/b2bcallback",
    'b2c_timeoutURL'			=>	APP_URL."/api/b2ccallback",
    'b2c_resultURL'			    =>	APP_URL."/api/b2ccallback",
    'development' => [
        'token_link'			            =>	'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
        // C2B
        'c2b_regiterUrl'		            =>  'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl',
        'c2b_express_online_processlink'	=>	'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
        'c2b_transactionUrl'                =>  'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate',

        // B2C
        'b2c_Url'				=>	'https://sandbox.safaricom.co.ke/mpesa/b2c/v3/paymentrequest',


        'checkout_querylink'	=>	'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
        'reversal_link'			=>	'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request',
        'balance_link'			=>	'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query',
        'transtat_link'			=>	'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query',
        'b2b_link'				=> 	'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
        
        'cert'                  =>  __DIR__. '/../Resource/Mpesa_public_sandbox_cert.cer',
    ],
    'production' => [
        'token_link'                        =>  'https://api.safaricom.co.ke/oauth/v1/generate',
        // C2B
        'c2b_regiterUrl'                    =>  'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl',
        'c2b_express_online_processlink'    =>	'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',

        // B2C TODO: Update production url
        'b2c_Url'				    =>	'https://api.safaricom.co.ke/mpesa/b2c/v3/paymentrequest',


        'checkout_querylink'        =>	'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query',
        'reversal_link'			    =>	'https://api.safaricom.co.ke/mpesa/reversal/v1/request',
        'balance_link'			    =>	'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query',
        'transtat_link'			    =>	'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query',
        'b2b_link'				    => 	'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
        'cert'                      =>  __DIR__. '/../Resource/Mpesa_public_cert.cer'

    ],
];
?>
