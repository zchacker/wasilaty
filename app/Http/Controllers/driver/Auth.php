<?php

namespace App\Http\Controllers\driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Symfony\Component\HttpFoundation\Response;

class Auth extends Controller
{
    //

    public function registerDriver(Request $request)
    {
                                           
        $rules = array(
            'avatar' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'birth_date' => 'required',
            'id_photo' => 'required',
            'driver_license_front' => 'required',
            'driver_license_back' => 'required',
            //'military_service_certificate' => 'required',
            'vehicle_type' => 'required',
            'vehicle_model' => 'required',
            "vehicle_color" => 'required',
            'vehicle_made_year' => 'required',
            'vehicle_passengers' => 'required',
            'vehicle_license_front' => 'required',
            'vehicle_license_back' => 'required',               
            'phone_numeber' => 'required'                
        );

        $validator = FacadesValidator::make($request->all() , $rules); //Validator::make($request->all() , $rules);

        if($validator->fails()){

            $error = $validator->errors();
            $allErrors = array();

            foreach($error->all() as $err){                
                array_push($allErrors , $err);
            }

            $data = new \stdClass();
            $data->message = $allErrors;
            $json = $this->generateJSON(FALSE , Response::HTTP_BAD_REQUEST , $data, "" );
            return $json;

        }else{

            $driver  = Driver::where('phone_numeber' , $request->input('phone_numeber'))->first();

            if($driver != NULL){
                $data = new \stdClass();
                $data->message = "";
                $json = $this->generateJSON(FALSE , Response::HTTP_UNPROCESSABLE_ENTITY , "phone number used", $data);
                return $json;// stop here
            }


            try{
        
                $driver = Driver::create(
                    [                        
                        'avatar' => $request->input('avatar'),
                        'first_name' => $request->input('first_name'),
                        'last_name' => $request->input('last_name'),
                        'email' => $request->input('email'),
                        'birth_date' => $request->input('birth_date'),
                        'id_photo' => $request->input('id_photo'),
                        'driver_license_front' => $request->input('driver_license_front'),
                        'driver_license_back' => $request->input('driver_license_back'),
                        'military_service_certificate' => $request->input('military_service_certificate'),
                        'vehicle_type' => $request->input('vehicle_type'),
                        'vehicle_model' => $request->input('vehicle_model'),
                        'vehicle_color' => $request->input('vehicle_color'),
                        'vehicle_made_year' => $request->input('vehicle_made_year'),
                        'vehicle_passengers' => $request->input('vehicle_passengers'),
                        'vehicle_license_front' => $request->input('vehicle_license_front'),
                        'vehicle_license_back' => $request->input('vehicle_license_back'),
                        'phone_numeber' => $request->input('phone_numeber')
                    ]
                );
                
                $data = new \stdClass();
                $data->message = "Driver Created";
                $json = $this->generateJSON(TRUE , Response::HTTP_OK, "", $data);
    
                return $json;
    
            }catch(\Illuminate\Database\QueryException $ex){
                dd($ex->getMessage());
    
                $data = new \stdClass();
                $data->message = "";
                $json = $this->generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request", $data);
                return $json;
            }
            
        }

        
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

}
