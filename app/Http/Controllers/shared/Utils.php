<?php

namespace App\Http\Controllers\shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Utils extends Controller
{
    //
    public static function generateJSON($success , $status , $error , $data)
    {
        $myObj = new \stdClass();
        $myObj->success = $success;
        $myObj->status  = $status;
        $myObj->error   = $error;
        $myObj->data    = $data;

        $json = json_encode($myObj, JSON_PRETTY_PRINT);
        $response = response($json, $status);
        return $response;
    }

    public static function generateOPT()
    {
        $digits = 4;
        return str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
    }

    public static function sendSMS($message , $number)
    {

        /*
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.msegat.com/gw/sendsms.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        $fields = <<<EOT
        {
            "userName": "1samm",
            "numbers": "$number",
            "userSender": "samm",
            "apiKey": "e8b6742ec184489a78894259443082f1",
            "msg": "$message"
        }
        EOT;

        $fields = json_encode(
			[
				'userName' => "1samm",
                "numbers" => "$number",
                "userSender" => "samm",
                "apiKey" => "e8b6742ec184489a78894259443082f1",
                "msg" => "$message"
			]
		);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        var_dump($info["http_code"]);
        var_dump($response);*/


        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.msegat.com/gw/sendsms.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_VERBOSE => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "userName":'.env('SMS_USERNAME').',
            "numbers":  "'.$number.'",
            "userSender":'.env('SMS_SENDER_NAME').',
            "apiKey":'.env('SMS_API_KEY').',
            "msg":"'.$message.'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Cookie: SERVERID=MBE1'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;


    }

}
