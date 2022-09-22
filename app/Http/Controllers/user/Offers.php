<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class Offers extends Controller
{

    /**
     * @OA\Get(
     * path="/api/user/get/offer/{order_id}",
     * security={{ "apiAuth": {} }},
     * summary="جلب عروض الاسعار",
     * description="",
     * operationId="offer/get",
     * tags={"Offer User"},     
     * @OA\Parameter(
     *    description="ID of order",
     *    in="path",
     *    name="order_id",
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
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="TRUE"),
     *       @OA\Property(property="status", type="int", example=200),
     *       @OA\Property(property="error", type="string", example=""),
     *       @OA\Property(property="data", type="string", example={"message":"offer accepted"} ),
     *      )
     *    ),
     * )
     */
    public function getOrderOffers(Request $request, $order_id)
    {       

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(            
            'order_id' => 'required|numeric'                                    
        );

        $messages = [            
            'order_id.required' => 'رقم الطلب مطلوب',
            'order_id.numeric' => 'رقم العرض يجب ان يكون رقم فقط',                  
        ];

        if ($lang == 'en')
        {
            $messages = [
                'order_id.required' => 'please send order id',
                'order_id.numeric' => 'offer id onley number',
            ];
        }

        $validator = Validator::make( [$order_id] , $rules, $messages); //Validator::make($request->all() , $rules);

        //if($validator->fails())
        if( empty($order_id) )
        {

            $error = $validator->errors();
            $allErrors = array();

            if ($lang == 'en')
            {
                array_push($allErrors , 'رقم الطلب مطلوب');
            }else{
                array_push($allErrors , 'please send order id');
            }
            
            /*foreach($error->all() as $err){                
                array_push($allErrors , $err);
            }*/

            $data = new \stdClass();
            $data->message = $allErrors;
            $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , $data, "" );
            return $json;

        }else{

            $order_id = $request->order_id;

            $offer = DB::table('offers')
            ->where(['order_id' => $order_id ])
            ->get(['id AS offer_id' ,'driver_id' , 'order_id' , 'amount', 'status']);

            $data = new \stdClass();
            $data->message = [];
            $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , $data, $offer );
            return $json;            

        }
       
        
    }
    
    /**
     * @OA\Post(
     * path="/api/user/offer/accept",
     * security={{ "apiAuth": {} }},
     * summary=" إضافة عرض سعر ",
     * description="",
     * operationId="offer/accept",
     * tags={"Offer User"},     
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"offer_id"},         
     *       @OA\Property(property="offer_id", type="int", example="1"),       
     *      ),     
     * ), 
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="TRUE"),
     *       @OA\Property(property="status", type="int", example=200),
     *       @OA\Property(property="error", type="string", example=""),
     *       @OA\Property(property="data", type="string", example={"message":"offer accepted"} ),
     *      )
     *    ),
     * )
     */
    public function acceptOffer(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(            
            'offer_id' => 'required|numeric'                                    
        );

        $messages = [            
            'offer_id.required' => 'رقم ألعرض مطلوب',
            'offer_id.numeric' => 'رقم العرض يجب ان يكون رقم فقط',                  
        ];

        if ($lang == 'en')
        {
            $messages = [
                'offer_id.required' => 'please send offer id',
                'offer_id.numeric' => 'offer id onley number',
            ];
        }

        $validator = Validator::make($request->all() , $rules, $messages); //Validator::make($request->all() , $rules);

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

                $offer = DB::table('offers')
                ->where([ 'id' => $request->offer_id ])
                ->first();

                // get offer data
                $driverId   = $offer->driver_id;
                $orderId    = $offer->order_id;
                $amount     = $offer->amount;

                $current = Carbon::now();

                // assign order to driver
                DB::table('multi_path_orders_assigned_to_driver')
                ->insert([
                    'order_id'   => $orderId,
                    'driver_id'  => $driverId,
                    'created_at' => $current,
                    'updated_at' => $current,
                ]);

                // update price
                DB::table('order_multi_path')
                ->where( 'id', $orderId )
                ->update([
                    'price' => $amount,
                    'status' => 2
                ]);

                // update other offers
                DB::table('offers')
                ->where( 'order_id', $orderId )
                ->update([                    
                    'status' => 3 // canceled
                ]);

                // update offer
                DB::table('offers')
                ->where( 'id', $request->offer_id )
                ->update([                    
                    'status' => 2
                ]);


                $data = new \stdClass();
                $data->message = "Offer accepted successfuly";
                if($lang == 'ar')
                {
                    $data->message = "تم قبول الطلب بنجاح";
                }
                $json = Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);
    
                return $json;
    
            }catch(\Illuminate\Database\QueryException $ex){
                dd($ex->getMessage());
    
                $data = new \stdClass();
                $data->message = "error while accept offer";
                $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request", $data);
                return $json;

            }
            
        }
        
    }

}
