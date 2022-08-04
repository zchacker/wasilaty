<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\BookingTrip;
use App\Models\Orders as ModelsOrders;
use App\Models\OrdersAssignedToDrivers;
use App\Models\Trips;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use stdClass;
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

        $vehicles = Vehicle::all(['id','name_en as name' , 'image']);

        if ($lang == 'ar')
        {            
            $vehicles = Vehicle::all(['id','name_ar as name' , 'image']);
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
     *       @OA\Property(property="start_point_description", type="string", format="string", example="London, Saudi Arabia"),     
     *       @OA\Property(property="end_point_description", type="string", format="string", example="London, Saudi Arabia"),     
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
            'start_point_description' => 'required',
            'end_point_description' => 'required',
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
                    'start_point_description' => $request->input('start_point_description'),
                    'end_point_description' => $request->input('end_point_description'),
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
    

    
     /**
     * @OA\Get(
     * path="/api/user/orders/getMyOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب طلباتي",
     * description="تقوم بجلب طلباتي",
     * operationId="user/getMyOrders",
     * tags={"OrderUser"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example={{
     *              "id": 3,
     *               "start_lat": 33.3637,
     *               "start_lng": 25.3637,
     *               "end_lat": 33.3637,
     *               "end_lng": 25.3637,
     *               "vehicle_type": 1,
     *               "user_id": 2,
     *               "order_type": 1,
     *               "driver_gender": 1,
     *               "status": 1,
     *               "payment_method": 1,
     *               "passengers": 1,
     *               "cobon_id": 0
     *          }} ),     
     *     )
     * )
     */
    public function getMyOrders(Request $request)
    {
        // get languae 
        $lang   = $request->header('Accept-Language' , 'en');
        $userId = $request->user()->id;

        $orders = ModelsOrders::where('user_id', $userId)
        ->whereIn('status' , [1,2,3])
        ->orderBy('created_at', 'desc')
        ->get();

        return $orders;        

    }

    /**
     * @OA\Get(
     * path="/api/user/orders/getMyPastOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب طلباتي",
     * description="تقوم بجلب طلباتي السابقة",
     * operationId="user/getMyPastOrders",
     * tags={"OrderUser"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example=
     *          {{
     *              "id": 3,
     *               "start_lat": 33.3637,
     *               "start_lng": 25.3637,
     *               "end_lat": 33.3637,
     *               "end_lng": 25.3637,
     *               "vehicle_type": 1,
     *               "user_id": 2,
     *               "order_type": 1,
     *               "driver_gender": 1,
     *               "status": 1,
     *               "payment_method": 1,
     *               "passengers": 1,
     *               "cobon_id": 0
     *          }} ),     
     *     )
     * )
     */
    public function getMyPastOrders(Request $request)
    {
        // get languae 
        $lang   = $request->header('Accept-Language' , 'en');
        $userId = $request->user()->id;

        $orders = ModelsOrders::where('user_id', $userId)
        ->whereIn('status' , [4,5,6])
        ->orderBy('created_at', 'desc')
        ->get();

        return $orders;        

    }


    /**
     * @OA\Post(
     * path="/api/user/orders/getOrderDetails",
     * security={{ "apiAuth": {} }},
     * summary="جلب تفاصيل الطلب",
     * description="تقوم بجلب تفاصيل طلب واحد فقط",
     * operationId="user/getOrderDetails",
     * tags={"OrderUser"},   
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"order_id"},
     *       @OA\Property(property="order_id", type="int", format="int", example="3"),
     *	),
     * ),  
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example={
     *              "id": 3,
     *               "start_lat": 33.3637,
     *               "start_lng": 25.3637,
     *               "end_lat": 33.3637,
     *               "end_lng": 25.3637,
     *               "vehicle_type": 1,
     *               "user_id": 2,
     *               "order_type": 1,
     *               "driver_gender": 1,
     *               "status": 1,
     *               "payment_method": 1,
     *               "passengers": 1,
     *               "cobon_id": 0
     *          } ),     
     *     )
     * )
     */
    public function getOrderDetails(Request $request)
    {
        // get languae 
        $lang    = $request->header('Accept-Language' , 'en');
        $userId  = $request->user()->id;
        $orderID = $request->order_id;

        $order = ModelsOrders::where(['user_id' => $userId , 'id' => $orderID]);
        
        if($order == null){

            return [];
        }

        $data  = new stdClass;
        $data->order_details  = $order->first();

        $orderWithDriver = OrdersAssignedToDrivers::where(['order_id' => $orderID])
        ->join('driver' ,  'orders_assigned_to_drivers.driver_id' , '=' , 'driver.id')
        ->first([ 
            'driver.id AS driver_id',           
            'driver.first_name AS driver_first_name' ,
            'driver.last_name AS driver_last_name',
            'driver.phone_numeber AS driver_phone_numeber',
            'avatar'
        ]);
        
        $data->driver_details = $orderWithDriver;
        
        return $data;        

    }

    /**
     * @OA\Get(
     * path="/api/user/orders/getMyBookedTrips",
     * security={{ "apiAuth": {} }},
     * summary="جلب تذاكري المحجوزة",
     * description="تقوم بجلب التذاكر المحجوزة",
     * operationId="getMyBookedTrips",
     * tags={"OrderUser"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example=   
     *           {{
     *               "id": 3,
     *               "user_id": 2,
     *               "trip_id": 2,
     *               "status": 1,
     *               "start_lat": 15.12,
     *               "start_lng": 15.1,
     *               "end_lat": 14.12,
     *               "end_lng": 14.1,
     *               "passengers": 15,
     *               "driver_id": 1
     *           }})     
     *        )
     *     )
     * )
     */
    public function getMyBookedTrips(Request $request)
    {
        // get languae 
        $lang   = $request->header('Accept-Language' , 'en');
        $userId = $request->user()->id;

        $trips = BookingTrip::where('user_id', $userId)
        ->join('trips', 'booking_trip.trip_id', '=', 'trips.id')
        ->orderBy('booking_trip.created_at', 'desc')
        ->get(['booking_trip.*', 'trips.start_lat', 'trips.start_lng', 'trips.end_lat', 'trips.end_lng', 'trips.passengers', 'trips.driver_id']);

        return $trips;
    }
    

    /**
     * @OA\Post(
     * path="/api/user/orders/cancelOrder",
     * security={{ "apiAuth": {} }},
     * summary="إلغاء طلب",
     * description="تقوم بإلغاء طلب",
     * operationId="user/orders/cancelOrder",
     * tags={"OrderUser"},    
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"order_id"},
     *       @OA\Property(property="order_id", type="int", format="int", example="2"),
     *    ),
     * ), 
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example=    
     *           {
     *                "success": true,
     *               "status": 400,
     *               "error": "",
     *               "data": "Order Canceled Successfuly"
     *           })     
     *        )
     *     )
     * )
     */    
    public function cancelOrder(Request $request)
    {
        // get languae 
        $lang   = $request->header('Accept-Language' , 'en');
        $orderId = $request->input('order_id');

        try{
            $order = ModelsOrders::find($orderId);
            $order -> status = 6;
            
            if($order -> save()){

                if($lang == 'ar'){
                    return Utils::generateJSON(TRUE , Response::HTTP_BAD_REQUEST , "", "تم الإلغاء بنجاح");
                }           
                return Utils::generateJSON(TRUE , Response::HTTP_BAD_REQUEST , "", "Order Canceled Successfuly");

            }else{

                if($lang == 'ar'){
                    return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "لم يتم الإلغاء ",[]);
                }           
                return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "Cancel Order Not Succesed", []);
            
            }
        }
        catch(\Exception $e){
            // do task when error
            if($lang == 'ar'){
                return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "خطأ غير متوقع: " . $e->getMessage(), []);
            }           
            return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request: " . $e->getMessage(), []);
            // return response()->json(['error' => $e->getMessage()],
            // Response::HTTP_BAD_REQUEST
            // ); 
        }
    }

    /**
     * @OA\Get(
     * path="/api/user/getAvailableTrips",
     * security={{ "apiAuth": {} }},
     * summary="إحضار الرحلات المتاحة",
     * description="تقوم بعرض الرحلات المتاحة للحجز حسب المواعيد",
     * operationId="getAvailableTrips",
     * tags={"OrderUser"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example=    
     *           {
     *               "id": 1,
     *               "start_time": "17:44:16",
     *               "end_time": "03:14:41",
     *               "passengers": 25,
     *               "vehicle_id": 1,
     *               "start_lat": 34.26,
     *               "start_lng": 26.2,
     *               "end_lat": 34.26,
     *               "end_lng": 26.2,
     *               "driver_id": 1,
     *               "first_name": "Ahmed",
     *               "last_name": "Nagem"
     *           })
     *       
     *        )
     *     )
     * )
     */
    public function getAvailableTrips(Request $request)
    {
        $trips = [];

        $all_trips = Trips::
        join('driver', 'driver.id', '=', 'trips.driver_id')
        ->orderBy('trips.created_at', 'desc')
        ->get(['trips.*' , 'driver.first_name' , 'driver.last_name']);

        foreach($all_trips as $trip){
            // checking if the trip have max open trips online

            $trip_id = $trip->id;
            $passengers = $trip->passengers;
            $booked = BookingTrip::where(['trip_id' => $trip_id  , "status" => 2 ]);
            
            if($booked->count() < $passengers)
            {
                array_push($trips , $trip);
            }

        }

        return $trips;
    }
       
    /**
     * @OA\POST(
     * path="/api/user/bookingTrip",
     * security={{ "apiAuth": {} }},
     * summary="حجز رحلة",
     * description="القيام باختيار رحلة ثم حجزها",
     * operationId="bookingTrip",
     * tags={"OrderUser"},    
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"trip_id"},
     *       @OA\Property(property="trip_id", type="int", format="int", example="1"),        
     *    ),
     * ), 
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example=    
     *           {
     *               "success": true,
     *               "status": 200,
     *               "error": "",
     *              "data": {
     *                   "message": "Order Created Successfuly"
     *               }
     *           })
     *       
     *        )
     *     )
     * )
     */
    public function bookingTrip(Request $request)
    {
        // get languae 
        $lang = $request->header('Accept-Language' , 'en');
        $userId = $request->user()->id;

        $rules = array(            
            'trip_id' => 'required'                     
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
                        
            $order = BookingTrip::create(
                [                        
                    'trip_id' => $request->input('trip_id'),                    
                    'status' => 1,                    
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