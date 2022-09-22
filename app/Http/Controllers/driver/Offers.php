<?php

namespace App\Http\Controllers\driver;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\OffersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class Offers extends Controller
{
    

    /**
     * @OA\Post(
     * path="/api/driver/offer/add",
     * security={{ "apiAuth": {} }},
     * summary=" إضافة عرض سعر ",
     * description="",
     * operationId="offer/add",
     * tags={"OfferDriver"},     
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"order_id","amount"},         
     *       @OA\Property(property="order_id", type="int", example="1"),
     *       @OA\Property(property="amount", type="int", example="25"),     
     *      ),     
     * ), 
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="TRUE"),
     *       @OA\Property(property="status", type="int", example=200),
     *       @OA\Property(property="error", type="string", example=""),
     *       @OA\Property(property="data", type="string", example={"message":"Created"} ),
     *      )
     *    ),
     * )
     */
    public function add_offer(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            //'driver_id' => 'required|numeric',
            'order_id' => 'required|numeric',
            'amount' => 'required|numeric',            
            //'status' => 'required|numeric',                                      
        );

        $messages = [            
            'order_id.required' => 'رقم الطلب مطلوب',
            'amount.required' => 'مبلغ العرض مطلوب',                  
        ];

        if ($lang == 'en')
        {
            $messages = [
                'order_id.required' => 'please send order id',
                'amount.required' => 'please send offer amount',
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

                $driverId = $request->user()->id;
                $offer = OffersModel::create([
                    'driver_id' => $driverId ,
                    'order_id' => $request->input('order_id'),
                    'amount' => $request->input('amount'),
                    'status' => 1,
                ]);
                                
                
                $data = new \stdClass();
                $data->message = "Offer sent successfuly";
                if($lang == 'ar')
                {
                    $data->message = "تم إرسال الطلب بنجاح";
                }
                $json = Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);
    
                return $json;
    
            }catch(\Illuminate\Database\QueryException $ex){
                dd($ex->getMessage());
    
                $data = new \stdClass();
                $data->message = "error while adding offer";
                $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request", $data);
                return $json;

            }
            
        }

    }

    


}
