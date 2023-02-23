<?php

namespace App\Http\Controllers\driver;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\Driver;
use App\Models\MultiPathOrdersAssignedToDriver;
use App\Models\Orders as ModelsOrders;
use App\Models\OrdersAssignedToDrivers;
use App\Models\SeatsModel;
use App\Models\Trips;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use ReflectionFunctionAbstract;
use stdClass;

class Orders extends Controller
{
    
    /**
     * @OA\Get(
     * path="/api/driver/getNewOrders",
     * security={{ "apiAuth": {} }},
     * summary="استلام الطلبات الجديدة",
     * description="استلام الطلبات الجديدة والموافقة عليها ",
     * operationId="driver/getNewOrders",
     * tags={"OrderDriver"},     
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
    public function getNewOrders(Request $request)
    {
        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $orders = ModelsOrders::join('user' , 'user.id' , '=' , 'orders.user_id')
        ->where(['status' => 1])
        ->orderByDesc('orders.created_at')
        ->get(['orders.id', 'user.name' ,'user.phone' ,'orders.start_lat' , 'orders.start_lng' , 'orders.end_lat' , 'orders.end_lng' , 'orders.start_point_description' , 'orders.end_point_description', 'orders.price' , 'orders.payment_method' , 'orders.passengers']);        
        
        //$orders = $request->user()->tokenCan('driver');
        return $orders;// Utils::generateJSON(TRUE, Response::HTTP_OK , "" , $orders);
    }

    /**
     * @OA\Get(
     * path="/api/driver/getMyAddedTrips",
     * security={{ "apiAuth": {} }},
     * summary=" جلب الرحلات المضافة في الحساب ",
     * description=" ",
     * operationId="driver/getMyAddedTrips",
     * tags={"OrderDriver"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent(
     *          @OA\Property(property="success", type="boolean", example="true"),
     *          @OA\Property(property="status", type="int", example=200),
     *          @OA\Property(property="error", type="string", example=""),
     *          @OA\Property(property="data", type="string", example={
     *              "start_time": "2022-03-09 03:14:41",
     *              "end_time": "2022-03-09 03:14:41",
     *              "passengers": 25,     
     *              "start_lat": 34.26,
     *              "start_lng": 26.2,
     *              "end_lat": 34.26,
     *              "end_lng": 26.2
     *          } ),
     *        )
     *     )
     * )
     */
    public function getMyAddedTrips(Request $request)
    {

        // get languae 
        $lang     = $request->header('Accept-Language' , 'en');
        $driverId = $request->user()->id;

        $orders = Trips::where(['driver_id' => $driverId])
        ->orderBy('created_at' , 'desc')
        ->get([
            'id',
            'start_time' ,
            'end_time' ,
            'passengers',
            'start_lat' ,
            'start_lng' ,
            'end_lat' ,
            'end_lng'
        ]);

        
        //$orders = $request->user()->tokenCan('driver');
        return Utils::generateJSON(TRUE, Response::HTTP_OK , "" , $orders);

    }


    /**
     * @OA\Get(
     * path="/api/driver/bookedTripUsers/{trip_id}",
     * security={{ "apiAuth": {} }},
     * summary="جلب الاشخاص الذين حجزو التذكرة",
     * description="",
     * operationId="driver/bookedTripUsers",
     * tags={"OrderDriver"},     
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
     *    description="success response",
     *      @OA\JsonContent( example=    
     *           {{
     *               "customerName": "Ahmed Moahmmed",
     *               "customerPhone": "966536301031",
     *               "seat_name": "A1",
     *               "reserved": true
     *           }})     
     *        )
     *     )
     * )
     */
    public function bookedTripUsers(Request $request, $trip_id)
    {

        // get languae 
        $lang     = $request->header('Accept-Language' , 'en');
        $driverId = $request->user()->id;
        //$trip_id  = $request->trip_id;

        $tickets = SeatsModel::
          where(['seats.trip_id' => $trip_id])
        ->where(['seats.reserved' => TRUE])
        ->join('user' , 'user.id' , '=' , 'seats.user_id')
        // ->join('seats' , 'seats.trip_id' , '=' , 'booking_trip.trip_id')
        ->get([
            // 'booking_trip.id'  ,
            // 'booking_trip.created_at',
            // 'booking_trip.lat' ,
            // 'booking_trip.lng' ,
            // 'booking_trip.status',
            'user.name AS customerName',
            'user.phone AS customerPhone',            
            'seats.name AS seat_name',
            'seats.reserved AS reserved',
        ]);
             
        return $tickets;
        //return Utils::generateJSON(TRUE, Response::HTTP_OK , "" , $tickets);

    }

    /**
     * @OA\Get(
     * path="/api/driver/orders/getMyOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب طلباتي",
     * description="تقوم بجلب طلباتي",
     * operationId="driver/getMyOrders",
     * tags={"OrderDriver"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example={{
     *               "id": 2,
     *               "order_id": 1,
     *               "driver_id": 1,
     *               "start_lat": 1,
     *               "start_lng": 25.3637,
     *               "end_lat": 33.3637,
     *               "end_lng": 25.3637,
     *               "start_point_description": null,
     *               "end_point_description": null,
     *               "vehicle_type": 1,
     *               "user_id": 2,
     *               "order_type": 1,
     *               "driver_gender": 1,
     *               "status": 2,
     *               "payment_method": 1,
     *               "passengers": 1,
     *               "price": 25,
     *               "cobon_id": 0,
     *               "name": "Ahmed M Nagem",
     *               "phone": "966536301031",
     *               "one_time_password": "1111",
     *               "otp_requested_time": "2022-06-20 07:45:15"
     *          }} ),     
     *     )
     * )
     */
    public function getMyOrders(Request $request)
    {
        // get languae 
        $lang   = $request->header('Accept-Language' , 'en');
        $userId = $request->user()->id;

        $orders = OrdersAssignedToDrivers::join('orders' , 'orders.id' , '=' ,'orders_assigned_to_drivers.order_id')
        ->join('user' , 'user.id' , '=' ,'orders.user_id')
        ->where('orders_assigned_to_drivers.driver_id', $userId)
        ->whereIn('orders.status' , [1,2,3])
        ->orderBy('orders.created_at', 'desc')
        ->get(['*']);

        /*$orders = ModelsOrders::where('user_id', $userId)
        ->whereIn('status' , [1,2,3])
        ->orderBy('created_at', 'desc')
        ->get();*/

        return $orders;        

    }

    /**
     * @OA\Get(
     * path="/api/driver/orders/getMyPastOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب طلباتي",
     * description="تقوم بجلب طلباتي السابقة",
     * operationId="driver/getMyPastOrders",
     * tags={"OrderDriver"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example={{
     *               "id": 2,
     *               "order_id": 1,
     *               "driver_id": 1,
     *               "start_lat": 1,
     *               "start_lng": 25.3637,
     *               "end_lat": 33.3637,
     *               "end_lng": 25.3637,
     *               "start_point_description": null,
     *               "end_point_description": null,
     *               "vehicle_type": 1,
     *               "user_id": 2,
     *               "order_type": 1,
     *               "driver_gender": 1,
     *               "status": 2,
     *               "payment_method": 1,
     *               "passengers": 1,
     *               "price": 25,
     *               "cobon_id": 0,
     *               "name": "Ahmed M Nagem",
     *               "phone": "966536301031",
     *               "one_time_password": "1111",
     *               "otp_requested_time": "2022-06-20 07:45:15"
     *          }} ),     
     *     )
     * )
     */
    public function getMyPastOrders(Request $request)
    {
        // get languae 
        $lang   = $request->header('Accept-Language' , 'en');
        $userId = $request->user()->id;

        $orders = OrdersAssignedToDrivers::join('orders' , 'orders.id' , '=' ,'orders_assigned_to_drivers.order_id')
        ->join('user' , 'user.id' , '=' ,'orders.user_id')
        ->where('orders_assigned_to_drivers.driver_id', $userId)
        ->whereIn('orders.status' , [4,5,6])
        ->orderBy('orders.created_at', 'desc')
        ->get(['*']);

        /*$orders = ModelsOrders::where('user_id', $userId)
        ->whereIn('status' , [1,2,3])
        ->orderBy('created_at', 'desc')
        ->get();*/

        return $orders;        

    }

    /**
     * @OA\Post(
     * path="/api/driver/orders/getOrderDetails",
     * security={{ "apiAuth": {} }},
     * summary="جلب تفاصيل الطلب",
     * description="تقوم بجلب تفاصيل طلب واحد فقط",
     * operationId="driver/getOrderDetails",
     * tags={"OrderDriver"},   
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
        //$userId  = $request->user()->id;
        $orderID = $request->order_id;

        $order = ModelsOrders::where([ 'id' => $orderID ]);
        
        if($order == null){

            return [];
        }

        $data  = new stdClass;
        $data->order_details  = $order->first();

        $orderWithDriver = OrdersAssignedToDrivers::where(['order_id' => $orderID])
        ->join('driver' ,  'orders_assigned_to_drivers.driver_id' , '=' , 'driver.id')
        ->first([            
            'driver.first_name AS driver_first_name' ,
            'driver.last_name AS driver_last_name',
            'driver.phone_numeber AS driver_phone_numeber',
            'avatar'
        ]);
        
        $data->driver_details = $orderWithDriver;
        
        return $data;        

    }

    /**
     * @OA\Post(
     * path="/api/driver/addTrip",
     * security={{ "apiAuth": {} }},
     * summary="إضافة معلومات رحلة",
     * description="",
     * operationId="driver/addTrip",
     * tags={"OrderDriver"},     
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"start_time","end_time", "passengers" , "vehicle_id" , "start_lat", "start_lng","end_lat","end_lng"},    
     *       @OA\Property(property="start_time", type="string", example="2022-03-09 03:14:41"),
     *       @OA\Property(property="end_time", type="string", example="2022-03-09 03:14:41"),
     *       @OA\Property(property="passengers", type="int", example="25"),
     *       @OA\Property(property="vehicle_id", type="int", example="1" ),
     *       @OA\Property(property="start_lat", type="string", example="34.26" ),
     *       @OA\Property(property="start_lng", type="string", example="26.20" ),
     *       @OA\Property(property="end_lat", type="string", example="34.26" ),
     *       @OA\Property(property="end_lng", type="string", example="26.20" ),     
     *       @OA\Property(property="start_location_description", type="string", example="Start Street Name" ),     
     *       @OA\Property(property="end_location_description", type="string", example="End Street Name" ),     
     *      ),     
     * ), 
     * @OA\Response(
     *    response=502,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="FALSE"),
     *       @OA\Property(property="status", type="int", example=502),
     *       @OA\Property(property="error", type="string", example={"message":"error message"}),
     *       @OA\Property(property="data", type="string", example="" ),
     *      )
     *    ),
     * )
     */
    public function addTrip(Request $request)
    {
        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            'start_time' => 'required',
            'end_time' => 'required',
            'passengers' => 'required|integer',
            //'vehicle_id' => 'required',
            'start_lat' => 'required',
            'start_lng' => 'required',
            'end_lat' => 'required',
            'end_lng' => 'required',            
            // 'driver_id' => 'required'                                      
        );

        $messages = [
            'start_time.required' => 'الرجاء إرفاق الصورة الشخصية',
            'end_time.required' => 'الرجاء كتابة الاسم الاول',
            'passengers.required' => 'الرجاء كتابة عدد الركاب',
            'vehicle_id.required' => 'الرجاء اختيار نوع المركبة',
            'start_lat.required' => 'الرجاء كتابة البريد الالكتروني',
            'start_lng.required' => 'تاريخ الميلاد مطلوب',
            'end_lat.required' => 'صورة الهوية مطلوبة',
            'driver_id.required' => 'صورة الرخصة من خلف مطلوبة'            
        ];

        if ($lang == 'en')
        {
            $messages = [
                'start_time.required' => 'الرجاء إرفاق الصورة الشخصية',
                'end_time.required' => 'الرجاء كتابة الاسم الاول',
                'passengers.required' => 'الرجاء كتابة الاسم الاخير',
                'vehicle_id.required' => 'الرجاء اختيار نوع المركبة',
                'start_lat.required' => 'الرجاء كتابة البريد الالكتروني',
                'start_lng.required' => 'تاريخ الميلاد مطلوب',
                'end_lat.required' => 'صورة الهوية مطلوبة',
                'driver_id.required' => 'صورة الرخصة من خلف مطلوبة'
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

                $driverId = $request->user()->id;
                $driver = Trips::create(
                    [                        
                        'start_time' => $request->input('start_time'),
                        'end_time' => $request->input('end_time'),
                        'passengers' => $request->input('passengers'),
                        //'vehicle_id' => $request->input('vehicle_id'),
                        'start_lat' => $request->input('start_lat'),
                        'start_lng' => $request->input('start_lng'),
                        'end_lat' => $request->input('end_lat'),
                        'end_lng' => $request->input('end_lng'),
                        "start_location_description" => @$request->input('start_location_description'),
                        "end_location_description" => @$request->input('end_location_description'),
                        'driver_id' => $driverId,
                    ]
                );

                // generate seats for this trip
                $trip_id = $driver->id;
                $passenger = $request->input('passengers');

                $this->genereteSeats($trip_id , $passenger);
                
                $data = new \stdClass();
                $data->message = "Created";
                $json = Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);
    
                return $json;
    
            }catch(\Illuminate\Database\QueryException $ex){
                //dd($ex->getMessage());
    
                $data = new \stdClass();
                $data->message = "error while adding trip";
                $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request", $data);
                return $json;
            }
            
        }
       
    }

    private function genereteSeats($trip_id, $passenger)
    {
        for($i = 1; $i <= $passenger; $i++)
        {
            $mod = $i % 3;
            $seats = [];
            switch($mod)
            {
                case 1:
                    $seats[$i-1] = [
                        'name' => "A$i",
                        'reserved' => FALSE,
                        'user_id' => 0,
                        'trip_id' => $trip_id
                    ];
                    break;
                case 2:
                    $seats[$i-1] = [
                        'name' => "B$i",
                        'reserved' => FALSE,
                        'user_id' => 0,
                        'trip_id' => $trip_id
                    ];
                    break;
                case 3:
                    $seats[$i-1] = [
                        'name' => "C$i",
                        'reserved' => FALSE,
                        'user_id' => 0,
                        'trip_id' => $trip_id
                    ];
                    break;
                case 0:
                    $seats[$i-1] = [
                        'name' => "D$i",
                        'reserved' => FALSE,
                        'user_id' => 0,
                        'trip_id' => $trip_id
                    ];
                    break;
                default:
                    break;
            }

            DB::table('seats')->insert($seats);

        }
    }

    /**
     * @OA\Post(
     * path="/api/driver/acceptOrder",
     * security={{ "apiAuth": {} }},
     * summary="قبول الطلب",
     * description=" ",
     * operationId="driver/acceptOrder",
     * tags={"OrderDriver"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"order_id"},
     *       @OA\Property(property="order_id", type="int", format="int", example="1"),     
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
    public function acceptOrder(Request $request)
    {
                
        // get languae 
        $lang     = $request->header('Accept-Language' , 'en');
        $driverId = $request->user()->id;

        // check the order satus 
        $transaction = DB::transaction(function() use($request,$driverId){
            try{

                $order_id = $request->input('order_id');
                $order = ModelsOrders::where(['id' => $order_id , 'status' => 1])->first();            
                $driverOrder = OrdersAssignedToDrivers::where(['order_id' => $order_id , 'driver_id' => $driverId])->count();            
                
                if($order != NULL && $driverOrder == 0){                                        
                    
                    // add record to database
                    OrdersAssignedToDrivers::create([
                        'order_id' => $order_id ,
                        'driver_id' => $driverId ,
                    ]);

                    // update the order status
                    ModelsOrders::where(['id' => $order_id , 'status' => 1])
                    ->update(['status' => 2]);

                    return TRUE;

                }else{

                    return FALSE;

                }  

            }catch(\Illuminate\Database\QueryException $ex){
                //dd($ex->getMessage());
                //DB::rollBack();
                return FALSE;
            }

        }, 3 );        

        if($transaction)
        {
            $data = new \stdClass();
            $data->message = "Order Accepted";
            if( $lang == 'ar')
            {
                $data->message = "تم قبول الطلب";
            }
            
            try{
                $firebase_token = User::where(['id' => $request->input('order_id')])->first()->firebase_token;
                Utils::sendNotificationClient($firebase_token , "حالة الطلب" , "تم قبول الطلب من السائق");
            }catch(\Illuminate\Database\QueryException $ex){}

            return Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);

        }else{

            $data = new \stdClass();
            $data->message = "Order Assignied to other driver";
            if( $lang == 'ar')
            {
                $data->message = "تم إسناد الطلب لسائق آخر";
            }
            return Utils::generateJSON(FALSE , Response::HTTP_NOT_FOUND, "", $data);
        }        
    }

    /**
     * @OA\Get(
     * path="/api/driver/getNewMultiPathOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب الطلبات متعددة النقاط",
     * description="تقوم بجلب الطلبات ذات النقاط المتعددة",
     * operationId="driver/getMultiPathOrders",
     * tags={"OrderDriver"},   
     * @OA\RequestBody(
     *    required=false,
     *    description="جميع الخيارات إلزامية",    
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
    public function getMultiPathOrders(Request $request)
    {
        /*
            $lat = 41.118491 // user's latitude
            $lng = 25.404509 // user's longitude

            To convert to miles, multiply by 3961.
            To convert to kilometers, multiply by 6373.
            To convert to meters, multiply by 6373000.
            To convert to feet, multiply by (3961 * 5280) 20914080.

            SELECT *, 
            ( 6371 * acos( cos( radians($lat) ) 
            * cos( radians( latitude ) ) 
            * cos( radians( longitude ) - radians($lng) ) + sin( radians($lat) ) 
            * sin( radians( latitude ) ) ) ) 
            AS calculated_distance 
            FROM settings as T 
            HAVING calculated_distance <= (SELECT distance FROM settings WHERE sid=T.sid) 
            ORDER BY distance_calc
        */

        // get last driver location
        $userId = $request->user()->id;
        $driver = DB::table('driver')
        ->where('id', $userId)
        ->first();
        
        $lat  = $driver->latitude;
        $lng  = $driver->longitude;


        $distanceField = "( 6373000 * acos( cos( radians($lat) ) * cos( radians( end_lat ) ) * cos( radians( end_lng ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( end_lat ) ) ) ) AS calculated_distance";
        // $distanceField = "6373000 * 2 * ASIN(SQRT(POWER(SIN(($lat -abs( end_lat )) * pi()/180 / 2), 2)  + COS($lat * pi()/180 ) * COS(abs( end_lat ) * pi()/180) * POWER(SIN(( end_lat ) * pi()/180 / 2), 2) )) as  calculated_distance";


        // get orders
        $orders = DB::table('order_multi_path')
        ->select('order_multi_path.*', DB::raw($distanceField))
        ->where('status' , '=' , 1)
        ->orderBy('order_multi_path.total_distance' , 'asc')
        ->having('calculated_distance' , '<' , DB::raw('(SELECT max_distance FROM settings limit 1)'))
        ->limit(100)
        ->get();

        return $orders;
       
    }

    /**
     * @OA\Post(
     * path="/api/driver/getMultiPathOrdersDetails",
     * security={{ "apiAuth": {} }},
     * summary="جلب الطلبات متعددة النقاط",
     * description="تقوم بجلب الطلبات ذات النقاط المتعددة",
     * operationId="driver/getMultiPathOrdersDetails",
     * tags={"OrderDriver"},   
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
        ->join('user' , 'user.id' , '=' , 'order_multi_path.user_id')
        ->select('order_multi_path.*' , 'user.phone AS client_phone' , 'user.name AS client_name' )
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
     * @OA\Get(
     * path="/api/driver/order/getMyMultiPathOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب الطلبات متعددة النقاط قيد التوصيل",
     * description="تقوم بجلب الطلبات ذات النقاط المتعددة الحالية قيد التوصيل",
     * operationId="driver/order/getMyMultiPathOrders",
     * tags={"OrderDriver"},   
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

        $orders = MultiPathOrdersAssignedToDriver::join('order_multi_path' , 'order_multi_path.id' , '=' ,'multi_path_orders_assigned_to_driver.order_id')
        ->join('user' , 'user.id' , '=' ,'order_multi_path.user_id')
        ->where('multi_path_orders_assigned_to_driver.driver_id', $userId)
        ->whereIn('order_multi_path.status' , [1,2,3])
        ->orderBy('order_multi_path.created_at', 'desc')
        ->get(['*']);        

        return $orders; 

    }


    /**
     * @OA\Get(
     * path="/api/driver/order/getMyPastMultiPathOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب الطلبات متعددة النقاط منتهية او ملغية التوصيل",
     * description="تقوم بجلب الطلبات ذات النقاط المتعددة الحالية منتهية او ملغية التوصيل",
     * operationId="driver/order/getMyPastMultiPathOrders",
     * tags={"OrderDriver"},   
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

        $orders = MultiPathOrdersAssignedToDriver::join('order_multi_path' , 'order_multi_path.id' , '=' ,'multi_path_orders_assigned_to_driver.order_id')
        ->join('user' , 'user.id' , '=' ,'order_multi_path.user_id')
        ->where('multi_path_orders_assigned_to_driver.driver_id', $userId)
        ->whereIn('order_multi_path.status' , [4,5,6])
        ->orderBy('order_multi_path.created_at', 'desc')
        ->get(['*']);        

        return $orders;

    }


    /**
     * @OA\Post(
     * path="/api/driver/order/updateOrderStatus",
     * security={{ "apiAuth": {} }},
     * summary="تحديث حالة الطلب",
     * description="",
     * operationId="driver/order/updateOrderStatus",
     * tags={"Driver Order Update Status"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"order_id","status"},
     *       @OA\Property(property="order_id", type="int", format="int", example=11),     
     *       @OA\Property(property="status", type="int", format="int", example=3),     
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
    public function updateOrderStatus(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            'order_id' => 'required',                                     
            'status' => 'required',                                     
        );

        $messages = [
            'order_id.required' => 'رقم الطلب مطلوب',           
            'status.required' => 'حالة الطلب مطلوبة',           
        ];

        if ($lang == 'en')
        {
            $messages = [
                'order_id.required' => 'Order ID is required',
                'status.required' => 'Status is required',
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
                    'status' => $request->status,
                ]);
                                
                
                $data = new \stdClass();
                $data->message = "updated";
                $json = Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);
    
                try{    
                    $statusMsg = Utils::orderStatusMessage($request->status);                                    
                    $firebase_token = ModelsOrders::where(['id' => $request->order_id])->first()->firebase_token;
                    Utils::sendNotificationClient($firebase_token , "حالة الطلب" , "حالة الطلب تغيرت الى $statusMsg");
                }catch(\Illuminate\Database\QueryException $ex){}
                

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
     * path="/api/driver/order/updateMultipathOrderStatus",
     * security={{ "apiAuth": {} }},
     * summary="تحديث حالة الطلب",
     * description="",
     * operationId="driver/order/updateMultipathOrderStatus",
     * tags={"Driver Order Update Status"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"order_id","status"},
     *       @OA\Property(property="order_id", type="int", format="int", example=11),     
     *       @OA\Property(property="status", type="int", format="int", example=3),     
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
    public function updateMultiPathStatus(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            'order_id' => 'required',                                     
            'status' => 'required',                                     
        );

        $messages = [
            'order_id.required' => 'رقم الطلب مطلوب',           
            'status.required' => 'حالة الطلب مطلوبة',           
        ];

        if ($lang == 'en')
        {
            $messages = [
                'order_id.required' => 'Order ID is required',
                'status.required' => 'Status is required',
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
                    'status' => $request->status,
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


}

?>
