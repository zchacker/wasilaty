<?php

namespace App\Http\Controllers\driver;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\Driver;
use App\Models\Orders as ModelsOrders;
use App\Models\OrdersAssignedToDrivers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class Orders extends Controller
{
    
    /**
     * @OA\Get(
     * path="/api/driver/getNewOrders",
     * security={{ "apiAuth": {} }},
     * summary="جلب المركبات من النظام",
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
