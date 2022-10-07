<?php

namespace App\Http\Controllers\driver;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class Data extends Controller
{

    /**
     * @OA\Post(
     * path="/api/driver/updateLocation",
     * security={{ "apiAuth": {} }},
     * summary="تحديث احداثيات السائق",
     * description="",
     * operationId="driverLocation/updateLocation",
     * tags={"driverLocation"},     
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"latitude","longitude"},
     *       @OA\Property(property="latitude", type="string", example="34.26" ),
     *       @OA\Property(property="longitude", type="string", example="26.20" ),    
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
    public function updateLocation(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            'latitude' => 'required',
            'longitude' => 'required',                                      
        );

        $messages = [
            'latitude.required' => 'الرجاء ارسال خط الطول latitude',
            'longitude.required' => 'الرجاء ارسال خط العرض longitude',            
        ];

        if ($lang == 'en')
        {
            $messages = [
                'latitude.required' => 'Please send latitude',
                'longitude.required' => 'Please send longitude',
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
                
                $driver = Driver::where(['id' => $driverId])->first();
                $driver->latitude = $request->latitude;
                $driver->longitude = $request->longitude;

                $driver->update();
                
                $data = new \stdClass();
                $data->message = "Updated: $driverId";
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
     * path="/api/user/getDriverLocation",
     * security={{ "apiAuth": {} }},
     * summary="احصل على احداثيات السائق",
     * description="",
     * operationId="driverLocation/getDriverLocation",
     * tags={"driverLocation"},     
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"driverId"},
     *       @OA\Property(property="driverId", type="int", example=1 ),        
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
    public function getDriverLocation(Request $request)
    {
        $driverId = $request->driverId;
        $driver = Driver::where(['id' => $driverId])->first();

        $obj = new stdClass;
        
        if($driver != NULL)
        {
            $obj->latitude = $driver->latitude;
            $obj->longitude = $driver->longitude;
            return $obj;
        }

        return response('', Response::HTTP_NOT_FOUND);                        

    }


}
