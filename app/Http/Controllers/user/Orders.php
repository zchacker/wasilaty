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
    
    // https://stackoverflow.com/questions/50614594/use-jwt-bearer-token-in-swagger-laravel
    /**
     * @OA\Info(title="This is Wasilny Api Documentation", version="0.1")  
     * docExpansion:"full"   
     */  

    /**
     * @OA\SecurityScheme(
     *     type="http",
     *     description="Login with email and password to get the authentication token",
     *     name="Token based Based",
     *     in="header",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     securityScheme="apiAuth",
     * )
     */

    /**
     * @OA\Get(
     * path="/api/user/getVehicles",
     * summary="جلب المركبات من النظام",
     * description="تحميل صور للمركبات داخل النظام",
     * operationId="getVehicles",
     * tags={"OrderUser"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="true"),
     *       @OA\Property(property="status", type="int", example=200),
     *       @OA\Property(property="error", type="string", example=""),
     *       @OA\Property(property="data", type="string", example={{"name":"string" , "image":"string"}} ),
     *        )
     *     )
     * )
     */
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


    /**
     * @OA\Post(
     * path="/api/user/AddOrder",
     * security={{ "apiAuth": {} }},
     * summary="إرسال الطلب",
     * description="order_type [1 = bus, 2=taxi], driver_gender[ 1=male, 2=female] ",
     * operationId="user/addOrder",
     * tags={"OrderUser"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"start_lat","start_lng", "end_lat" , "end_lng" , "order_type", "vehicle_type"},
     *       @OA\Property(property="start_lat", type="float", format="float", example="36.336363"),
     *       @OA\Property(property="start_lng", type="float", format="float", example="25.363662"),
     *       @OA\Property(property="end_lat", type="float", format="float", example="33.363662"),
     *       @OA\Property(property="end_lng", type="float", format="float", example="25.363662"),     
     *       @OA\Property(property="order_type", type="int", example="1"),
     *       @OA\Property(property="driver_gender", type="int", example="1"),
     *       @OA\Property(property="vehicle_type", type="int", example="1"),
     *       @OA\Property(property="payment_method", type="int", example="1"),
     *       @OA\Property(property="passengers", type="int", example="1"),
     *       @OA\Property(property="cobon_id", type="int", example="0"),
     *    ),
     * ),
     * @OA\Response(
     *    response=502,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="FALSE"),
     *       @OA\Property(property="status", type="int", example=502),
     *       @OA\Property(property="error", type="string", example={"message":"error message"}),
     *       @OA\Property(property="data", type="string", example="" ),
     *        )
     *     )
     * )
     */
    public function addOrder(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            'start_lat' => 'required',
            'start_lng' => 'required',
            'end_lat' => 'required',
            'end_lng' => 'required',
            'order_type' => 'required',
            'driver_gender' => 'required',                          
            'vehicle_type' => 'required', 
            'payment_method' => 'required',
            'passengers' => 'required',                     
            'cobon_id' => 'required',                     
        );

        $validator = FacadesValidator::make($request->all() , $rules); //Validator::make($request->all() , $rules);

        if($validator->fails()){
            $data = new \stdClass();
            $data->message =  "Error accurs and we working to fix it, please try again later";
    
            if ($lang == 'ar')
            {
                $data->message = "حدث خطأ غير متوقع جاري العمل على إصلاحه, حاول مرة أخرى لاحقا";
            }
    
            return Utils::generateJSON(FALSE , Response::HTTP_BAD_GATEWAY, $data ,"" ); 
        }

        try{
            
            $userId = $request->user()->id;
            $order = ModelsOrders::create(
                [                        
                    'start_lat' => $request->input('start_lat'),
                    'start_lng' => $request->input('start_lng'),
                    'end_lat' => $request->input('end_lat'),
                    'end_lng' => $request->input('end_lng'),
                    'order_type' => $request->input('order_type'),
                    'driver_gender' => $request->input('driver_gender'),
                    'vehicle_type' => $request->input('vehicle_type'),
                    'payment_method' => $request->input('payment_method'),
                    'passengers' => $request->input('passengers'),
                    'cobon_id' => $request->input('cobon_id'),
                    'user_id' => $userId ,
                ]
            );
                        
            
            $data = new \stdClass();
            $data->message = "Order Created Successfuly";
    
            if ($lang == 'ar')
            {
                $data->message = "تم انشاء الطلب بنجاح";
            }
    
            return Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data); 

        }catch(\Illuminate\Database\QueryException $ex){

            $data = new \stdClass();
            $data->message =$ex->getMessage();// "Error accurs and we working to fix it, please try again later";
    
            if ($lang == 'ar')
            {
                $data->message = "حدث خطأ غير متوقع جاري العمل على إصلاحه, حاول مرة أخرى لاحقا";
            }
    
            return Utils::generateJSON(FALSE , Response::HTTP_BAD_GATEWAY, "", $data); 

        }
               
    }        
        

    private function get_distance_between_points($latitude1, $longitude1, $latitude2, $longitude2) {
        $meters = $this->get_meters_between_points($latitude1, $longitude1, $latitude2, $longitude2);
        $kilometers = $meters / 1000;
        $miles = $meters / 1609.34;
        $yards = $miles * 1760;
        $feet = $miles * 5280;
        return compact('miles','feet','yards','kilometers','meters');
    }

    private function get_meters_between_points($latitude1, $longitude1, $latitude2, $longitude2) {
        if (($latitude1 == $latitude2) && ($longitude1 == $longitude2)) { return 0; } // distance is zero because they're the same point
        $p1 = deg2rad($latitude1);
        $p2 = deg2rad($latitude2);
        $dp = deg2rad($latitude2 - $latitude1);
        $dl = deg2rad($longitude2 - $longitude1);
        $a = (sin($dp/2) * sin($dp/2)) + (cos($p1) * cos($p2) * sin($dl/2) * sin($dl/2));
        $c = 2 * atan2(sqrt($a),sqrt(1-$a));
        $r = 6371008; // Earth's average radius, in meters
        $d = $r * $c;
        return $d; // distance, in meters
    }

}

?>