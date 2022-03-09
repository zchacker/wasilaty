<?php

namespace App\Http\Controllers\driver;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\Driver;
use App\Models\Orders as ModelsOrders;
use App\Models\OrdersAssignedToDrivers;
use App\Models\Trips;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator as FacadesValidator;

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

        $orders = ModelsOrders::where(['status' => 1])
        ->orderBy('created_at')
        ->first(['start_lat' , 'start_lng' , 'end_lat' , 'end_lng' , 'passengers']);

        
        //$orders = $request->user()->tokenCan('driver');
        return Utils::generateJSON(TRUE, Response::HTTP_OK , "" , $orders);
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
     *              "vehicle_id": 1,
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
        $lang = $request->header('Accept-Language' , 'en');
        $driverId = $request->user()->id;

        $orders = Trips::where(['driver_id' => $driverId])
        ->orderBy('created_at')
        ->first(['start_time' , 'end_time' , 'passengers' , 'vehicle_id' , 'start_lat' , 'start_lng' , 'end_lat' , 'end_lng' ]);

        
        //$orders = $request->user()->tokenCan('driver');
        return Utils::generateJSON(TRUE, Response::HTTP_OK , "" , $orders);
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
            'passengers' => 'required',
            'vehicle_id' => 'required',
            'start_lat' => 'required',
            'start_lng' => 'required',
            'end_lat' => 'required',
            'end_lng' => 'required',            
            // 'driver_id' => 'required'                                      
        );

        $messages = [
            'start_time.required' => 'الرجاء إرفاق الصورة الشخصية',
            'end_time.required' => 'الرجاء كتابة الاسم الاول',
            'passengers.required' => 'الرجاء كتابة الاسم الاخير',
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
                        'vehicle_id' => $request->input('vehicle_id'),
                        'start_lat' => $request->input('start_lat'),
                        'start_lng' => $request->input('start_lng'),
                        'end_lat' => $request->input('end_lat'),
                        'end_lng' => $request->input('end_lng'),
                        'driver_id' => $driverId,
                    ]
                );
                
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

}

?>
