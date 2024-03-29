<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\BookingTrip;
use App\Models\Orders as ModelsOrders;
use App\Models\OrdersAssignedToDrivers;
use App\Models\SeatsModel;
use App\Models\Trips;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     *               "seat_name":"A2",
     *               "reserved": TRUE,
     *               "trip_id": 1
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

        /*$trips = BookingTrip::where('user_id', $userId)
        ->join('trips', 'booking_trip.trip_id', '=', 'trips.id')
        ->orderBy('booking_trip.created_at', 'desc')
        ->get(['booking_trip.*', 'trips.start_lat', 'trips.start_lng', 'trips.end_lat', 'trips.end_lng', 'trips.passengers', 'trips.driver_id']);

        return $trips;*/

        $availableTickets    = SeatsModel::where(['seats.user_id' => $userId]) 
        ->where(['seats.reserved' => TRUE])              
        ->get([            
            'seats.id',       
            'seats.name AS seat_name',            
            'seats.trip_id AS trip_id',
        ]);
             
        return $availableTickets;
    }
    

    /**
     * @OA\Post(
     * path="/api/user/orders/cancelOrder1",
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
    /*public function cancelOrder(Request $request)
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
    }*/

    /**
     * @OA\Get(
     * path="/api/user/getAvailableSeatsForTrip/{trip_id}",
     * security={{ "apiAuth": {} }},
     * summary="إحضار مقاعد الرحلات المتاحة",
     * description="تقوم بعرض مقاعد الرحلات المتاحة للحجز حسب المواعيد",
     * operationId="getAvailableSeatsForTrip",
     * tags={"OrderUser"},    
     * @OA\Parameter(
     *    description="ID of trip",
     *    in="path",
     *    name="trip_id",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),  
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example=    
     *           {{
     *               "id" : 1,
     *               "seat_name": "B5",
     *               "reserved": false,
     *           }})
     *       
     *        )
     *     )
     * )
     */
    public function getAvailableSeatsForTrip(Request $request, $trip_id)
    {
        /*$trips = [];

        $all_trips = Trips::
        join('driver', 'driver.id', '=', 'trips.driver_id')
        ->orderBy('trips.created_at', 'desc')
        ->get(['trips.*' , 'driver.first_name' , 'driver.last_name']);

        foreach($all_trips as $trip){

            // checking if the trip have max open trips online

            $trip_id    = $trip->id;
            $passengers = $trip->passengers;
            $booked     = BookingTrip::where(['trip_id' => $trip_id  , "status" => 2 ]);
            
            if($booked->count() < $passengers)
            {
                array_push($trips , $trip);
            }

        }

        return $trips;*/
       
        $availableTickets    = SeatsModel::where(['seats.trip_id' => $trip_id])               
        ->get([            
            'seats.id',       
            'seats.name AS seat_name',
            'seats.reserved AS reserved',
        ]);
             
        return $availableTickets;
        
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
     *           {{
     *               "id" : 1,
     *               "seat_name": "B5",
     *               "reserved": false,
     *           }})
     *       
     *        )
     *     )
     * )
     */
    public function getAvailableTrips(Request $request)
    {

        $trips    = Trips::whereDate('start_time', Carbon::today())->with('driver')->get();
             
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
     *       required={"seat_id" },
     *       @OA\Property(property="seat_id", type="int", format="int", example="1")            
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
            'seat_id' => 'required',            
            // 'lat' => 'required',
            // 'lng' => 'required'
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

            SeatsModel::where(['seats.id' => $request->input('seat_id')])
            ->update([
                'reserved' => TRUE,
                'user_id' => $request->user()->id
            ]);
                        
            /*$order = BookingTrip::create(
                [                        
                    'trip_id' => $request->input('trip_id'),
                    'lat' => $request->input('lat'),
                    'lng' => $request->input('lng'),
                    'status' => 1,
                    'user_id' => $userId ,
                ]
            );*/
                        
            
            $data = new \stdClass();
            $data->message = "Order Created Successfuly";
    
            if ($lang == 'ar')
            {
                $data->message = "تم انشاء الطلب بنجاح";
            }
            
            $seat = SeatsModel::where(['seats.id' => $request->input('seat_id')])->first();
            $firebase_token = $seat->trip->driver->firebase_token;
            $trip_id = $seat->trip->id;
            Utils::sendNotificationDriver($firebase_token , "حجز مقعد جديد" ,"تم حجز مقعد جديد للرحلة رقم ($trip_id) الان");

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
     * @OA\Post(
     * path="/api/user/order/addOrderWithMultiPath",
     * security={{ "apiAuth": {} }},
     * summary="إرسال الطلب متعدد النقاط",
     * description="رجاء اخذ الاعتبار بهذه الفيم driver_gender[ male, female], payment method[1 , 2] حيث 1تعني كاش و 2 تعني بالبطاقة ",
     * operationId="user/addOrderWithMultiPath",
     * tags={"OrderUser"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent( example={
     *          "end_lat" : 12.1,
     *          "end_lng" : 12.2,
     *          "payment_method" : 1,
     *          "passengers" : 4,
     *          "driver_gender" : "male",
     *          "points" : {
     *              {"lat" : 10.1,"lng" : 13.1 , "description": "street name 1"},
     *              {"lat" : 11.0,"lng" : 14.1 , "description": "street name 3"},
     *              {"lat" : 13.4,"lng" : 15.1 , "description": "street name 2"}
     *          }
     *      }       
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="TRUE"),
     *       @OA\Property(property="status", type="int", example=200),
     *       @OA\Property(property="error", type="string", example={"order_id": "8"}),
     *       @OA\Property(property="data", type="string", example="" ),
     *        )
     *     )
     * )
     */
    public function addOrderWithMultiPath(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            'end_lat' => 'required',
            'end_lng' => 'required',
            'payment_method' => 'required',
            'passengers' => 'required',
            'driver_gender' => 'required',
            //'points' => 'required'
        );

        $messages = [
            'end_lat.required' => "احداثيات lat مطلوبة",
            'end_lng.required' => "احداثيات lng مطلوبة",
            'payment_method.unique' =>"طريقة الدفع مطلوبة",
            'passengers.required' => "عدد الركاب مطلوب",
            'driver_gender.required' => "نوع جنس السائق مطلوب",
            'points.required' => 'الرجاء ارفاق نقاط الانطلاق'
        ];

        if($lang == 'en'){

            $messages = [
                'end_lat.required' => "Lattude required",
                'end_lng.required' => "Logntiude required",
                'payment_method.unique' =>"Payment Method required",
                'passengers.required' => "Passenger required",
                'driver_gender.unique' => "Driver Gender required",
                'points.required' => 'Please upload start coordenates'
            ];

        }

        $validator = FacadesValidator::make($request->all() , $rules , $messages);

        if($validator->fails() == false){

            // insert data
            $userId = $request->user()->id;

            $order = DB::table('order_multi_path')->insert([
                'end_lat' => $request->end_lat,
                'end_lng' => $request->end_lng,
                'payment_method' => $request->payment_method,
                'passengers' => $request->passengers,
                'driver_gender' => $request->driver_gender,
                'user_id' => $userId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $order_id = DB::getPdo()->lastInsertId();
            
            $points = $request->points;

            $last_lat = -1;
            $last_lng = -1;
            $total_distance_meters = 0;

            foreach($points AS $point)
            {
                DB::table('order_locations')->insert([
                    'lat' => $point['lat'],
                    'lng' => $point['lng'],
                    'description' => $point['description'],
                    'order_id' => $order_id
                ]);

                if($last_lat != -1 && $last_lng != -1){
                    $distance_meters = $this->get_meters_between_points($point['lat'] , $point['lng'] , $last_lat , $last_lng);
                    $total_distance_meters += $distance_meters;
                }

                $last_lat = $point['lat'];
                $last_lng = $point['lng'];

            }


            $distance_meters = $this->get_meters_between_points($request->end_lat , $request->end_lng , $last_lat , $last_lng);
            $total_distance_meters += $distance_meters;

            DB::table('order_multi_path')
              ->where('id', $order_id)
              ->update(['total_distance' => $total_distance_meters]);

            $data = new stdClass();
            $data->order_id = $order_id;
            
            return Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);

        }else{

            $error     = $validator->errors();
            $allErrors = "";

            foreach($error->all() as $err){                
                $allErrors .= $err . " <br/>";
            }
            

            return Utils::generateJSON(TRUE , Response::HTTP_BAD_REQUEST, $allErrors , "" );

        }

    }


    /**
     * @OA\Get(
     * path="/api/user/orders/getMyMultiPathOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب الطلبات متعددة النقاط قيد التوصيل",
     * description="تقوم بجلب الطلبات ذات النقاط المتعددة الحالية قيد التوصيل",
     * operationId="user/orders/getMyMultiPathOrders",
     * tags={"OrderUser"},   
     * @OA\RequestBody(
     *    required=false,
     *    description="",    
     * ),  
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example={
     *              "id": 1,
     *              "end_lat": 24.480911,
     *              "end_lng": 39.595821,
     *              "location_description": null,
     *              "user_id": 1,
     *              "status": 1,
     *              "payment_method": 1,
     *              "passengers": 4,
     *              "driver_gender": "male",
     *              "price": 0,
     *              "total_distance": 0,
     *              "created_at": null,
     *              "updated_at": null,
     *              "deleted_at": null,
     *              "calculated_distance": 547.0263208294848
     *          } ),     
     *     )
     * )
     */
    public function getMyMultiPathOrders(Request $request)
    {

        // get languae 
        $lang   = $request->header('Accept-Language' , 'en');
        $userId = $request->user()->id;

        $orders = DB::table('order_multi_path')
        ->where('user_id' , $userId)
        ->whereIn('status' , [1,2,3])
        ->orderBy('created_at' , 'desc')
        ->get();
        

        return $orders; 

    }


    /**
     * @OA\Get(
     * path="/api/user/orders/getMyPastMultiPathOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب الطلبات متعددة النقاط منتهية التوصيل",
     * description="تقوم بجلب الطلبات ذات النقاط المتعددة الحالية منتهية التوصيل",
     * operationId="user/orders/getMyPastMultiPathOrders",
     * tags={"OrderUser"},   
     * @OA\RequestBody(
     *    required=false,
     *    description="",    
     * ),  
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example={
     *              "id": 1,
     *              "end_lat": 24.480911,
     *              "end_lng": 39.595821,
     *              "location_description": null,
     *              "user_id": 1,
     *              "status": 1,
     *              "payment_method": 1,
     *              "passengers": 4,
     *              "driver_gender": "male",
     *              "price": 0,
     *              "total_distance": 0,
     *              "created_at": null,
     *              "updated_at": null,
     *              "deleted_at": null,
     *              "calculated_distance": 547.0263208294848
     *          } ),     
     *     )
     * )
     */
    public function getMyPastMultiPathOrders(Request $request)
    {

        // get languae 
        $lang   = $request->header('Accept-Language' , 'en');
        $userId = $request->user()->id;

        $orders = DB::table('order_multi_path')
        ->where('user_id' , $userId)
        ->whereIn('status' , [4,5,6])
        ->orderBy('created_at' , 'desc')
        ->get();
        

        return $orders; 

    }


    /**
     * @OA\Post(
     * path="/api/user/orders/getMultiPathOrdersDetails",
     * security={{ "apiAuth": {} }},
     * summary="جلب الطلبات متعددة النقاط",
     * description="تقوم بجلب الطلبات ذات النقاط المتعددة",
     * operationId="user/getMultiPathOrdersDetails",
     * tags={"OrderUser"},   
     * @OA\RequestBody(
     *       required=true,
     *       description="جميع الخيارات إلزامية",           
     *       @OA\JsonContent(
     *           required={"order_id"},         
     *           @OA\Property(property="order_id", type="int", example="1"),               
     *      ),  
     * ),  
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example={
     *       "order_details": {
     *           "id": 10,
     *           "end_lat": 24.480911,
     *           "end_lng": 39.595821,
     *           "location_description": null,
     *           "user_id": 60013,
      *          "status": 1,
     *           "payment_method": 1,
     *           "passengers": 4,
     *           "driver_gender": "male",
     *           "price": 0,
     *          "total_distance": 1147.1,
     *           "created_at": null,
     *           "updated_at": null,
     *           "deleted_at": null,
     *           "client_phone": "966536301031",
     *           "client_name": null
     *       },
     *       "points": {
     *           {
     *               "id": 7,
     *               "lat": 24.476466,
     *               "lng": 39.594674,
     *               "description": "street name 1",
     *               "order_id": 10,
     *               "created_at": "2022-08-19 07:47:36",
     *               "updated_at": "2022-08-19 07:47:36",
     *               "deleted_at": null
     *           },
     *           {
     *               "id": 8,
     *               "lat": 24.47715,
     *               "lng": 39.587913,
     *               "description": "street name 3",
     *               "order_id": 10,
     *               "created_at": "2022-08-19 07:47:36",
     *               "updated_at": "2022-08-19 07:47:36",
     *               "deleted_at": null
     *           },
     *           {
     *               "id": 9,
     *               "lat": 24.477591,
     *               "lng": 39.587203,
     *               "description": "street name 2",
     *               "order_id": 10,
     *               "created_at": "2022-08-19 07:47:36",
     *                  "updated_at": "2022-08-19 07:47:36",
     *                   "deleted_at": null
     *               }
     *           }
     *      } ),     
     *     )
     * )
     */
    public function getMultiPathOrdersDetails(Request $request)
    {

        // get last driver location
        $userId = $request->user()->id;
        $order_id = $request->order_id;     
              
        // get orders
        $order_details = DB::table('order_multi_path')        
        ->join('multi_path_orders_assigned_to_driver' , 'multi_path_orders_assigned_to_driver.order_id' , '=' , 'order_multi_path.id')
        ->join('driver' , 'multi_path_orders_assigned_to_driver.driver_id' , '=' , 'driver.id')
        ->select('order_multi_path.*' , 'driver.phone_numeber AS driver_phone' , 'driver.first_name AS driver_fname', 'driver.last_name AS driver_lname' )
        ->where('order_multi_path.id' , '=' , $order_id)        
        ->first();


        $points = DB::table('order_locations')
        ->where('order_id' , '=', $order_id)
        ->get();

        $obj = new stdClass();
        $obj->order_details = $order_details;
        $obj->points = $points;

        return $obj;

    }

    /**
     * @OA\Post(
     * path="/api/user/order/cancelMultiPathOrder",
     * security={{ "apiAuth": {} }},
     * summary="إلغاء طلب",
     * description="",
     * operationId="user/order/cancelMultiPathOrder",
     * tags={"User Order Cancel"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"order_id","reason"},
     *       @OA\Property(property="order_id", type="int", format="int", example=11),     
     *       @OA\Property(property="reason", type="int", format="int", example=3),     
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
    public function cancelMultiPathOrder(Request $request)
    {
        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            'order_id' => 'required',                                                                                    
            'reason' => 'required',                                                                                    
        );

        $messages = [
            'order_id.required' => 'رقم الطلب مطلوب',
            'reason.required' => 'سبب الإلغاء مطلوب',
        ];

        if ($lang == 'en')
        {
            $messages = [
                'order_id.required' => 'Order ID is required',
                'reason.required' => 'Canclation Reason is required',
            ];
        }

        $validator = FacadesValidator::make($request->all() , $rules, $messages); //Validator::make($request->all() , $rules);

        if($validator->fails()){

            $error = $validator->errors();
            $allErrors = array();

            foreach($error->all() as $err){                
                array_push($allErrors , $err);
            }

            $data = new \stdClass();
            $data->message = $allErrors;
            $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , $data, "" );
            return $json;

        }else{
           
            try{                        

                $order = DB::table('order_multi_path')
                ->where(['id' => $request->order_id])
                ->update([
                    'status' => 6 ,
                ]);
                                
                
                $data = new \stdClass();
                $data->message = "updated";
                $json = Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);
    
                return $json;
    
            }catch(\Illuminate\Database\QueryException $ex){
                //dd($ex->getMessage());
    
                $data = new \stdClass();
                $data->message = "error while updateing order status";
                $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request", $data);
                return $json;
            }
            
        }
    }

    /**
     * @OA\Post(
     * path="/api/user/order/cancelOrder",
     * security={{ "apiAuth": {} }},
     * summary="إلغاء طلب",
     * description="",
     * operationId="user/order/cancelOrder",
     * tags={"User Order Cancel"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"order_id","reason"},
     *       @OA\Property(property="order_id", type="int", format="int", example=11),     
     *       @OA\Property(property="reason", type="int", format="int", example=3),     
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
    public function cancelOrder(Request $request)
    {
        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            'order_id' => 'required',                                                                                    
            'reason' => 'required',                                                                                    
        );

        $messages = [
            'order_id.required' => 'رقم الطلب مطلوب',
            'reason.required' => 'سبب الإلغاء مطلوب',
        ];

        if ($lang == 'en')
        {
            $messages = [
                'order_id.required' => 'Order ID is required',
                'reason.required' => 'Canclation Reason is required',
            ];
        }

        $validator = FacadesValidator::make($request->all() , $rules, $messages); //Validator::make($request->all() , $rules);

        if($validator->fails()){

            $error = $validator->errors();
            $allErrors = array();

            foreach($error->all() as $err){                
                array_push($allErrors , $err);
            }

            $data = new \stdClass();
            $data->message = $allErrors;
            $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , $data, "" );
            return $json;

        }else{
           
            try{                        

                $order = DB::table('orders')
                ->where(['id' => $request->order_id])
                ->update([
                    'status' => 6 ,
                ]);
                                
                
                $data = new \stdClass();
                $data->message = "updated";
                $json = Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);
    
                return $json;
    
            }catch(\Illuminate\Database\QueryException $ex){
                //dd($ex->getMessage());
    
                $data = new \stdClass();
                $data->message = "error while updateing order status";
                $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request", $data);
                return $json;
            }
            
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