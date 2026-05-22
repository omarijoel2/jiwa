<?php
namespace App\Classes;

use App\Classes\Mpesa;
use App\Classes\Logger;

class Payments{
    private $mpesa;
    private $logger;

    public function __construct(){
        $this->mpesa = new Mpesa();
        $this->logger = new Logger('mpesa');

    }

    public function startnotification($request)
    {

        $start  =   $this->mpesa->C2B_REGISTER(['consumerkey'=>$request['consumerkey'],'consumersecret'=>$request['consumersecret'],'shortcode'=>$request['shortcode']]);
        $data   =   (array)json_decode($start);
        // $this->logger->error($start,$request);
        //var_dump($data);
        if(isset($data["ResponseCode"]))
        {
            if($data["ResponseCode"] == '0')
            {
                $this->logger->info("Shortcode ".$request['shortcode']." Started Notifying.", json_encode($data,JSON_PRETTY_PRINT));
                // TODO: Save the status to database
                // $shortcode = Shortcode::find($request->id);
                // $shortcode->status = 1;
                // return $shortcode->save();
                return true;

            }
            return false;
        }
        return false;
    }

}