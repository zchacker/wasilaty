<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\Orders as ModelsOrders;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Orders extends Controller
{
    //
    public function getVehicles(Request  $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $vehicles = Vehicle::all(['name_en as name' , 'image']);

        if ($lang == 'ar')
        {
            $vehicles = Vehicle::all(['name_ar as name' , 'image']);
        }                

        return Utils::generateJSON(TRUE, Response::HTTP_OK , "",$vehicles );        

    }


    public function addOrder(Request $request)
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

        }

        $order = ModelsOrders::create(
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
                'phone_numeber' => $request->input('phone_numeber'),
                'one_time_password' => $otp ,
                'otp_requested_time' => date('Y-m-d H:i:s') 
            ]
        );
        
        $data = new \stdClass();
        $data->message = "Driver Created";
        $json = $this->generateJSON(TRUE , Response::HTTP_OK, "", $data);

        return $json;
    }

}
