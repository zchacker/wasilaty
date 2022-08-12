<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Symfony\Component\HttpFoundation\Response;

class Auth extends Controller
{
    //

    /**
     * @OA\Post(
     * path="/api/user/auth/login",
     * summary="الدخول للنظام",
     * description="",
     * operationId="Login",
     * tags={"User Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"phone"},
     *       @OA\Property(property="phone", type="String", format="966536301031", example="966536301031"),         
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="FALSE"),
     *       @OA\Property(property="status", type="int", example=200),
     *       @OA\Property(property="error", type="string", example=""),
     *       @OA\Property(property="data", type="string", example={"message":"OK"} ),
     *        )
     *     )
     * )
     */
    public function Login(Request $request)
    {
        $phone = $request->input('phone');
        $user  = User::where('phone' , $phone)->first();
        $otp   = Utils::generateOPT();

        // TODO: this is test account
        if( $phone == '966536301031'){
            $otp = 1111;
        }
        
        if($user == NULL){

            try{

                $user = User::create(
                    [                        
                        "phone" => $phone,
                        "one_time_password" => $otp,
                        "otp_requested_time" => date('Y-m-d H:i:s') ,                    
                    ]
                );
                
                $data = new \stdClass();
                $data->message = "get otp";
                $json = Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);

                $numbsr = $phone;
                $msg = "OTP: #$otp"; 
                Utils::sendSMS($msg , $numbsr);

                return $json;

            }catch(\Illuminate\Database\QueryException $ex){
                //dd($ex->getMessage());

                $data = new \stdClass();
                $data->message = "";
                $json = Utils::generateJSON(FALSE , Response::HTTP_NOT_FOUND , "worng json", $data);
                return $json;
            }

        }else{

            User::where('phone', $phone)            
            ->update(['one_time_password' => $otp , "otp_requested_time" => date('Y-m-d H:i:s')]);

            $data = new \stdClass();
                $data->message = "OK";
                $json = Utils::generateJSON(TRUE , Response::HTTP_OK , "", $data);

            $numbsr = $phone;
            $msg = "OTP: #$otp"; 
            Utils::sendSMS($msg , $numbsr);

            return $json;

        }        
    }


    /**
     * @OA\Post(
     * path="/api/user/auth/verfyOTP",
     * summary="تحقق من الرقم",
     * description="",
     * operationId="OTP",
     * tags={"User Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"phone"},
     *       @OA\Property(property="phone", type="String", format="966536301031", example="966536301031"),     
     *       @OA\Property(property="otp", type="String", format="1111", example="1111"),     
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
    public function verfyOTP( Request $request )
    {
        
        $phone  = $request->input('phone');
        $otp    = $request->input('otp');
        $user   = User::where(['phone' => $phone , "one_time_password" => $otp ])->first();

        if($user == NULL)
        {
            $data = new \stdClass();
            $data->message = "";
            $json = Utils::generateJSON( FALSE , Response::HTTP_UNAUTHORIZED , "user not found or wrong otp" , "");
            return $json;
        }
        else
        {
            // generate token
            
            //$ourUser = FacadesAuth::user();

            $token = $user->createToken('token')->plainTextToken;
            
            // if( !FacadesAuth::attempt($request->only('phone')) )
            // {
            //     return Utils::generateJSON( TRUE , Response::HTTP_UNAUTHORIZED , "worng data" , "");
            // }

            $data = new \stdClass();
            $data->token = $token;
            $json = Utils::generateJSON( TRUE , Response::HTTP_OK , "" , $data);
            return $json;

        }

    }

    

    /**
     * @OA\Post(
     * path="/api/uploadImage",
     * summary="رفع صورة مستندات للسائق",
     * description="",
     * operationId="imageUploadPost",
     * tags={"Driver Auth"},
     * @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="file to upload",
     *                     property="image",
     *                     type="file",
     *                ),
     *                 required={"file"}
     *             )
     *         )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="FALSE"),
     *       @OA\Property(property="status", type="int", example=502),
     *       @OA\Property(property="error", type="string", example=""),
     *       @OA\Property(property="data", type="string", example={"imageName":"image.png"} ),
     *        )
     *     )
     * )
     */
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
     // https://www.itsolutionstuff.com/post/laravel-8-image-upload-tutorial-exampleexample.html
    
     public function imageUploadPost(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $imageName = time().'.'.$request->image->extension();  
     
        //$request->image->move(public_path('images'), $imageName);
        $request->image->storeAs('images', $imageName);// save in private path

        /* Store $imageName name in DATABASE from HERE */
    
        $data = new \stdClass();
        $data->imageName = $imageName;
        return Utils::generateJSON( TRUE , Response::HTTP_OK , "" , $data);

        //return back()
        //    ->with('success','You have successfully upload image.')
        //    ->with('image',$imageName); 
    }

    // https://dev.to/fractalbit/tips-for-working-with-private-files-in-laravel-1g08
    public function viewImage($file)
    {
        return view('image', ['fileName' => $file]);
    }

    public function test( Request $request )
    {           
        $id = $request->user()->id;
        return "Hello User: $id";
    }

    private function generateJSON($success , $status , $error , $data)
    {
        $myObj = new \stdClass();
        $myObj->success = $success;
        $myObj->status  = $status;
        $myObj->error   = $error;
        $myObj->data    = $data;

        $json = json_encode($myObj);
        $response = response($json, $status);
        return $response;
    }

    private function generateOPT()
    {
        $digits = 4;
        return str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
    }


}
