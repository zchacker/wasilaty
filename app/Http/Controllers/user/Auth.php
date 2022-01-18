<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Symfony\Component\HttpFoundation\Response;

class Auth extends Controller
{
    //

    public function Login(Request $request)
    {
        $phone = $request->input('phone');
        $user  = User::where('phone' , $phone)->first();
        $otp   = $this->generateOPT();

        if($user == NULL){

            try{

                $user = User::create(
                    [                        
                        "phone" => $phone,
                        "one_time_password" => $otp,
                        "otp_requested_time" => date('Y-m-d H:i:s') ,                    
                    ]
                );
                
                $data = new \stdClass();
                $data->message = "get otp";
                $json = $this->generateJSON(TRUE , 200, "", $data);

                return $json;

            }catch(\Illuminate\Database\QueryException $ex){
                //dd($ex->getMessage());

                $data = new \stdClass();
                $data->message = "";
                $json = $this->generateJSON(FALSE , Response::HTTP_NOT_FOUND , "worng json", $data);
                return $json;
            }

        }else{

            User::where('phone', $phone)            
            ->update(['one_time_password' => $otp , "otp_requested_time" => date('Y-m-d H:i:s')]);

            $data = new \stdClass();
                $data->message = "get otp";
                $json = $this->generateJSON(TRUE , Response::HTTP_OK , "", $data);

            return $json;

        }        
    }

    public function verfyOTP( Request $request )
    {
        
        $phone  = $request->input('phone');
        $otp    = $request->input('otp');
        $user   = User::where(['phone' => $phone , "one_time_password" => $otp ])->first();

        if($user == NULL)
        {
            $data = new \stdClass();
            $data->message = "";
            $json = $this->generateJSON( FALSE , Response::HTTP_UNAUTHORIZED , "user not found or wrong otp" , $data);
            return $json;
        }
        else
        {
            // generate token
            
            //$ourUser = FacadesAuth::user();

            $token = $user->createToken('token')->plainTextToken;
            
            // if( !FacadesAuth::attempt($request->only('phone')) )
            // {
            //     return $this->generateJSON( TRUE , Response::HTTP_UNAUTHORIZED , "worng data" , "");
            // }

            $data = new \stdClass();
            $data->token = $token;
            $json = $this->generateJSON( TRUE , Response::HTTP_OK , "" , $data);
            return $json;

        }

    }

    public function test( Request $request )
    {           
        $id = $request->user()->id;   
        return "Hello User: $id";
    }


    private function generateJSON($success , $status , $error , $data)
    {
        $myObj = new \stdClass();
        $myObj->success = $success;
        $myObj->status  = $status;
        $myObj->error   = $error;
        $myObj->data    = $data;

        $json = json_encode($myObj);
        $response = response($json, $status);
        return $response;
    }

    private function generateOPT()
    {
        $digits = 4;
        return str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
    }
}
