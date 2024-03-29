<?php

namespace App\Http\Controllers\driver;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\Driver;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use PhpParser\Node\Expr\FuncCall;
use Symfony\Component\HttpFoundation\Response;

class Auth extends Controller
{
    //

    /**
     * @OA\Post(
     * path="/api/driver/auth/register",
     * summary="إرسال الطلب",
     * description="order_type [1 = bus, 2=taxi], driver_gender[ 1=male, 2=female] ",
     * operationId="driver/registerDriver",
     * tags={"Driver Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"avatar","first_name", "last_name" , "email" , "birth_date", "id_photo", 
     *          "driver_license_front","driver_license_back" ,
     *          "vehicle_type" , "vehicle_color" , "vehicle_model", "vehicle_made_year",
     *          "vehicle_passengers" , "vehicle_license_front" , "vehicle_license_back" , "phone_numeber"},
     * 
     *       @OA\Property(property="avatar", type="string", format="float", example="image url"),
     *       @OA\Property(property="first_name", type="string", format="float", example="frinst name"),
     *       @OA\Property(property="last_name", type="string", format="float", example="last name"),
     *       @OA\Property(property="email", type="string", format="float", example="example@mail.com"),     
     *       @OA\Property(property="birth_date", type="string", example="1992-02-02"),
     *       @OA\Property(property="id_photo", type="string", example="string"),
     *       @OA\Property(property="driver_license_front", type="string", example="string"),
     *       @OA\Property(property="driver_license_back", type="string", example="string"),
     *       @OA\Property(property="military_service_certificate", type="string", example="string"),
     *       @OA\Property(property="vehicle_model", type="string", example="string"),
     *       @OA\Property(property="vehicle_type", type="string", example="string"),
     *       @OA\Property(property="vehicle_color", type="string", example="string"),
     *       @OA\Property(property="vehicle_made_year", type="int", example="2018"),
     *       @OA\Property(property="vehicle_passengers", type="int", example=5),
     *       @OA\Property(property="vehicle_license_front", type="string", example="string"),
     *       @OA\Property(property="vehicle_license_back", type="string", example="string"),
     *       @OA\Property(property="phone_numeber", type="string", example="966536301031")
     *    ),
     * ),
     * @OA\Response(
     *    response=502,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="FALSE"),
     *       @OA\Property(property="status", type="int", example=502),
     *       @OA\Property(property="error", type="string", example={"message":{"error message"}}),
     *       @OA\Property(property="data", type="string", example="" ),
     *        )
     *     )
     * )
     */
    public function registerDriver(Request $request)
    {                           

        $lang = $request->header('Accept-Language' , 'en');

        $rules = array(
            'avatar' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'birth_date' => 'required',
            'id_photo' => 'required',
            //'driver_license_front' => 'required',
            //'driver_license_back' => 'required',            
            // 'vehicle_type' => 'required',
            // 'vehicle_model' => 'required',
            // "vehicle_color" => 'required',
            // 'vehicle_made_year' => 'required',
            // 'vehicle_passengers' => 'required',
            // 'vehicle_license_front' => 'required',
            // 'vehicle_license_back' => 'required',               
            'phone_numeber' => 'required|unique:driver,phone_numeber'                           
        );

        $messages = [
            'avatar.required' => 'الرجاء إرفاق الصورة الشخصية',
            'first_name.required' => 'الرجاء كتابة الاسم الاول',
            'last_name.required' => 'الرجاء كتابة الاسم الاخير',
            'email.required' => 'الرجاء كتابة البريد الالكتروني',
            'birth_date.required' => 'تاريخ الميلاد مطلوب',
            'id_photo.required' => 'صورة الهوية مطلوبة',
            'driver_license_back.required' => 'صورة الرخصة من خلف مطلوبة',
            'driver_license_front.required' => 'صورة الهوية من أمام مطلوبة',                
            'vehicle_type.required' => 'نوع السيارة مطلوب',
            'vehicle_model.accepted' => 'موديل السيارة مطلوب',
            'vehicle_color.accepted' => 'لون السيارة مطلوب',
            'vehicle_made_year.accepted' => 'سنة الصنع مطلوبة',
            'vehicle_passengers.accepted' => 'عدد الركاب مطلوب',
            'vehicle_license_front.accepted' => 'صورة رخصة القيادة من أمام مطلوبة',
            'vehicle_license_back.accepted' => 'صورة رخصة القيادة من خلف مطلوبة',
            'phone_numeber.accepted' => 'رقم الهاتف مطلوب',
            'phone_numeber.unique' => 'الرقم موجود مسبقاً',
        ];

        if ($lang == 'ar')
        {
            $messages = [
                'avatar.required' => 'البريد الالكتروني مطلوب',
                'first_name.required' => 'البريد الالكتروني مطلوب',
                'last_name.required' => 'هذا الايميل مستخدم مسبقاً',
                'email.required' => 'اسم المستخدم مطلوب',
                'birth_date.required' => 'اسم المسخدم محجوز مسبقا',
                'id_photo.required' => 'حدد نوع المدرسة ينين / بنات',
                'driver_license_back.required' => 'رقم الهاتف مطلوب',
                'driver_license_front.required' => 'الرجاء الموافقة على الشروط والاحكام',                
                'vehicle_type.required' => 'كلمة السر مطلوبة',
                'vehicle_model.accepted' => 'الرجاء قبول اتفاقية الاستخدام',
                'vehicle_color.accepted' => 'الرجاء قبول اتفاقية الاستخدام',
                'vehicle_made_year.accepted' => 'الرجاء قبول اتفاقية الاستخدام',
                'vehicle_passengers.accepted' => 'الرجاء قبول اتفاقية الاستخدام',
                'vehicle_license_front.accepted' => 'الرجاء قبول اتفاقية الاستخدام',
                'vehicle_license_back.accepted' => 'الرجاء قبول اتفاقية الاستخدام',
                'phone_numeber.accepted' => 'الرجاء قبول اتفاقية الاستخدام',
                'phone_numeber.unique' => 'الرقم موجود مسبقاً',
            ];
        }


        $validator = FacadesValidator::make($request->all() , $rules, $messages); //Validator::make($request->all() , $rules);

        if($validator->fails()){

            $error = $validator->errors();
            $allErrors = array();

            foreach($error->all() as $err){                
                array_push($allErrors , $err."1");
            }

            $data = new \stdClass();
            $data->message = $allErrors;
            $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , $data, "" );
            return $json;

        }else{

            $driver  = Driver::where('phone_numeber' , $request->input('phone_numeber'))->first();

            if($driver != NULL){
                             
                array_push($allErrors , "phone number used");
                    
                $data = new \stdClass();
                $data->message = $allErrors;
                $data = new \stdClass();
                $data->message = $allErrors;

                $json = Utils::generateJSON(FALSE , Response::HTTP_UNPROCESSABLE_ENTITY , $data , "");
                return $json;// stop here
            }


            try{
        
                $otp   = Utils::generateOPT();
                $numbsr = $request->input('phone_numeber');
                $msg = "OTP: #$otp";                

                $driver = Driver::create(
                    [                        
                        'avatar' => $request->input('avatar'),
                        'first_name' => $request->input('first_name'),
                        'last_name' => $request->input('last_name'),
                        'email' => $request->input('email'),
                        'birth_date' => $request->input('birth_date'),
                        'id_photo' => $request->input('id_photo'),
                        'driver_license_front' => $request->input('driver_license_front'),
                        'driver_license_back' => $request->input('driver_license_back'),
                        'military_service_certificate' => $request->input('military_service_certificate'),
                        'vehicle_type' => $request->input('vehicle_type'),
                        'vehicle_model' => $request->input('vehicle_model'),
                        'vehicle_color' => $request->input('vehicle_color'),
                        'vehicle_made_year' => $request->input('vehicle_made_year'),
                        'vehicle_passengers' => $request->input('vehicle_passengers'),
                        'vehicle_license_front' => $request->input('vehicle_license_front'),
                        'vehicle_license_back' => $request->input('vehicle_license_back'),
                        'phone_numeber' => $request->input('phone_numeber'),
                        'one_time_password' => $otp ,
                        'otp_requested_time' => date('Y-m-d H:i:s')
                    ]
                );
                
                $data = new \stdClass();
                $data->message = "Driver Created";
                $json = Utils::generateJSON(TRUE , Response::HTTP_OK, "", $data);
    
                Utils::sendSMS($msg , $numbsr);
                return $json;
    
            }catch(\Illuminate\Database\QueryException $ex){
                dd($ex->getMessage());
    
                $data = new \stdClass();
                $data->message = "";
                $json = Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request", $data);
                return $json;
            }
            
        }

        
    }


    /**
     * @OA\Post(
     * path="/api/driver/auth/activeDriver",
     * summary="تأكيد رقم الهاتف",
     * description="order_type [1 = bus, 2=taxi], driver_gender[ 1=male, 2=female] ",
     * operationId="activateDriver",
     * tags={"Driver Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"phone","otp"},
     *       @OA\Property(property="phone", type="String", format="966536301031", example="966536301031"),
     *       @OA\Property(property="otp", type="float", format="1111", example="1111"),
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
    public function activateDriver(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $phone  = $request->input('phone');
        $otp    = $request->input('otp');
        $driver   = Driver::where(['phone_numeber' => $phone , "one_time_password" => $otp ])->first();

        if($driver == NULL)
        {
            $data = new \stdClass();
            $data->message = ["Wrong verfy code"];

            if ($lang == 'ar')
            {
                $data->message = ["الرمز خاطئ"];
            }

            $json = Utils::generateJSON( FALSE , Response::HTTP_NOT_FOUND , $data , "");
            return $json;
        }
        else
        {
            // generate token
            
            //$ourdriver = FacadesAuth::user();

            //$token = $driver->createToken('token')->plainTextToken;
            
            // if( !FacadesAuth::attempt($request->only('phone')) )
            // {
            //     return Utils::generateJSON( TRUE , Response::HTTP_UNAUTHORIZED , "worng data" , "");
            // }

            $data = new \stdClass();
            $data->active = "Your Number verfied, please wait the approved message, thanks for joining to our team";
            if ($lang == 'ar')
            {
                $data->active = "تم تأكيد رقم هاتفك, الرجاء انتظار رسالة التفعيل, شكرا لإنضمامك لفريقنا";
            }
            
            $json = Utils::generateJSON( TRUE , Response::HTTP_OK , "" , $data);
            return $json;

        }

    }

    /**
     * @OA\Post(
     * path="/api/driver/auth/login",
     * summary="الدخول للنظام",
     * description="",
     * operationId="driver/Login",
     * tags={"Driver Auth"},
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
        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $phone   = $request->input('phone');
        $driver  = Driver::where('phone_numeber' , $phone)->first();
        $otp     = Utils::generateOPT();

        // TODO: this is test account
        if( $phone == '966536301031'){
            $otp = 1111;
        }

        if( $phone == '966500000000'){
            $otp = 1111;
        }

        if($driver == NULL){

            $data = new \stdClass();
            //$data->message = "The number not in system, please register to login";
            
            $message = "The number not in system, please register to login";

            if ($lang == 'ar')
            {
                $message = "رقم الهاتف غير مسجل في النظام, الرجاء التسجيل أولاً";
            }

            $json = Utils::generateJSON(TRUE , Response::HTTP_UNAUTHORIZED , $message , "" );

            return $json;

            

        }else{

            try{

                Driver::where('phone_numeber' , $phone)
                ->update([
                    'one_time_password' => $otp ,
                    'otp_requested_time' => date('Y-m-d H:i:s') ,                    
                ]);
                
                // $driver = Driver::create(
                //     [                        
                //         "phone" => $phone,
                //         "one_time_password" => $otp ,
                //         "otp_requested_time" => date('Y-m-d H:i:s') ,                    
                //     ]
                // );
                
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
                $data->message = $ex->getMessage();
                $json = Utils::generateJSON(FALSE , Response::HTTP_NOT_FOUND , "worng json", $data);
                return $json;

            }

        }        
    }


    /**
     * @OA\Post(
     * path="/api/driver/auth/verfyOTP",
     * summary="تحقق من الرقم",
     * description="",
     * operationId="driver/verfyOTP",
     * tags={"Driver Auth"},
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
        
        // get languae 
        $lang = $request->header('Accept-Language' , 'en');


        $phone  = $request->input('phone');
        $otp    = $request->input('otp');
        $driver = Driver::where(['phone_numeber' => $phone , "one_time_password" => $otp ])->first();

        if( $driver == NULL )
        {
            $data = new \stdClass();            
            $data->message = "";

            $message = "phone not found or wrong otp";
            if($lang == 'ar')
            {
                $message = "رقم الهاتف أو رمز التحقق خاطئ";
            }

            $json = Utils::generateJSON( FALSE , Response::HTTP_UNAUTHORIZED , $message , "");
            return $json;
        }
        else
        {
            // generate token            
            $token = $driver->createToken('token')->plainTextToken;            

            $data = new \stdClass();
            $data->token = $token;
            $json = Utils::generateJSON( TRUE , Response::HTTP_OK , "" , $data);
            return $json;

        }

    }

    /** 
     * @OA\Get(
     * security={{ "apiAuth": {} }},
     * path="/api/driver/getMyProfile",
     * summary="جلب ملف السائق ومعلوماته",
     * description="",
     * operationId="driver/getMyProfileDriver",     
     * tags={"Driver Profile"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="true"),
     *       @OA\Property(property="status", type="int", example=200),
     *       @OA\Property(property="error", type="string", example=""),
     *       @OA\Property(property="data", type="string", example={{"driverData": {
                    "id": 1,
                    "avatar": "name.png",
                    "first_name": "ahmed",
                    "last_name": "ahmed",
                    "email": "ahmed@gmail.com",
                    "birth_date": "1999-01-04",
                    "id_photo": "name.png",
                    "driver_license_front": "name.png",
                    "driver_license_back": "name.png",
                    "military_service_certificate": "name.png",
                    "vehicle_type": "name.png",
                    "vehicle_model": "name.png",
                    "vehicle_made_year": 2014,
                    "vehicle_passengers": 5,
                    "vehicle_color": "black",
                    "vehicle_license_front": "name.png",
                    "vehicle_license_back": "name.png",
                    "phone_numeber": "966536301031"
                }
            }} ),
     *        )
     *     )
     * )
     */
    public function getMyProfileDriver(Request $request)
    {

        $driverId = $request->user()->id;
        $driver = Driver::where(['id' => $driverId])->first();
        $data = new \stdClass();
        $data->driverData = $driver;

        $json = Utils::generateJSON( TRUE , Response::HTTP_OK , "" , $data);
        return $json;

    }


    /**
     * @OA\Post(
     * path="/api/driver/updateProfile",
     * security={{ "apiAuth": {} }},
     * summary="تحديث بيانات السائق",
     * description="",
     * operationId="driver/updateDriverProfile",
     * tags={"Driver Profile"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"phone"},
     *       @OA\Property(property="phone_numeber", type="String", format="966536301031", example="966536301031"),     
     *       @OA\Property(property="email", type="String", format="email@gmail.com", example="1111"),     
     *       @OA\Property(property="first_name", type="String", format="ahmed", example="1111"),     
     *       @OA\Property(property="last_name", type="String", format="adm", example="1111"),     
     *       @OA\Property(property="birth_date", type="String", format="2021/01/01", example="1111"),     
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
    public function updateDriverProfile(Request $request)
    {
        // get languae 
        $lang = $request->header('Accept-Language' , 'en');
        
        $driverId = $request->user()->id;
        $driver = Driver::where(['id' => $driverId])->first();

        // check if phone was saved
        $phone  = $request->phone_numeber;
        $email  = $request->email;
        $errors = array();

        if($driver != null){
            
            if($driver->phone_numeber != $phone){
                $driver_phone = Driver::where(['phone_numeber' => $phone])->first();
                
                if($driver_phone != null){
                    if($driver_phone->count() > 0){                        
                        if($lang == "ar")
                            array_push($errors , "رقم الهاتف مستخدم في حساب آخر");
                        else
                            array_push($errors , "phone number used with other account");                        
                    }
                }

            }


            if($driver->email != $email){
                $driver_email = Driver::where(['email' => $email])->first();

                if($driver_email != null){
                    if($driver_email->count() > 0){                        
                        if($lang == "ar")
                            array_push($errors , "البريد الالكتروني مستخدم في حساب آخر");
                        else
                            array_push($errors , "email used with other account");                       
                    }
                }
                
            }

        }
                                

        if(count($errors) > 0 ){
            $data = new \stdClass();
            $data->errors = $errors;
    
           return Utils::generateJSON( FALSE , Response::HTTP_BAD_REQUEST , $data , "");
            
        }

        // if everything is ok, update profile     
        $affectedRows = Driver::where(['id' => $driverId])->update($request->all([
            'first_name' ,
            'last_name' , 
            'email' ,
            'birth_date',
            'phone_numeber'
        ]));
        
        $data = new \stdClass();
        $data->errors = $errors;

        if($affectedRows == 1)
        {
            return Utils::generateJSON( TRUE , Response::HTTP_OK , "" , "");
        }
        
    }

    /**
     * @OA\Post(
     * path="/api/driver/update_firebase_token",
     * security={{ "apiAuth": {} }},
     * summary="تحديث توكن الاشعارات",
     * description="الرجاء ارسال توكن صالح من Firebase حتى تتلقى الاشعارات من النظام",
     * operationId="driver/update_firebase_token",
     * tags={"Driver Profile"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"firebase_token"},
     *       @OA\Property(property="firebase_token", type="string", format="string", example="asdSjfHE54jSFXjhdfdfwerweSHHDkjk5443"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="TRUE"),
     *       @OA\Property(property="status", type="int", example=200),
     *       @OA\Property(property="error", type="string", example=""),
     *       @OA\Property(property="data", type="string", example={"message":"Updated Successfuly"} ),
     *        )
     *     )
     * )
     */
    public function update_firebase_token(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');
    
        try{

            $token   = $request->firebase_token;        
            $driver  = Driver::find($request->user()->id);

            $rules = array(
                //'firebase_token' => 'required',
            );
    
            $messages = [
                'firebase_token.required' => "firebase_token مطلوب",
            ];
    
            if($lang == 'en'){
    
                $messages = [
                    'firebase_token.required' => "firebase_token required",
                ];
    
            }
    
            $validator = FacadesValidator::make($request->all() , $rules , $messages);
    
            if($validator->fails() == false){

                if($token == NULL)
                    $token = '';
                    
                $driver->firebase_token  = $token;
                
                $driver->save();

                if($lang == 'ar'){
                    return Utils::generateJSON(TRUE , Response::HTTP_OK , "", "تم التحديث بنجاح");
                }

                return Utils::generateJSON(TRUE , Response::HTTP_OK , "", "Updated Successfuly");
                
            }else{

                $error     = $validator->errors();
                $allErrors = "";

                foreach($error->all() as $err){                
                    $allErrors .= $err . " <br/>";
                }
                

                return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST, $allErrors , "" );

            }

            
        }catch(\Exception $e){
            
            // do task when error
            
            if($lang == 'ar'){
                return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "خطأ غير متوقع: " . $e->getMessage(), []);
            }           
            
            return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request: " . $e->getMessage(), []);            
        
        }

    }


    /**
     * @OA\Post(
     * path="/api/driver/request_account_deletion",
     * security={{ "apiAuth": {} }},
     * summary="طلب حذف حساب",
     * description="",
     * operationId="driver/request_account_deletion",
     * tags={"Driver Profile"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"reason"},
     *       @OA\Property(property="reason", type="string", format="string", example="i want to stop working"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example="TRUE"),
     *       @OA\Property(property="status", type="int", example=200),
     *       @OA\Property(property="error", type="string", example=""),
     *       @OA\Property(property="data", type="string", example={"message":"done Successfuly"} ),
     *        )
     *     )
     * )
     */
    public function request_account_deletion(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');
    
        try{

            $token   = $request->reason;        
            $driver  = Driver::find($request->user()->id);

            $rules = array(
                'reason' => 'required',
            );
    
            $messages = [
                'reason.required' => "reason مطلوب",
            ];
    
            if($lang == 'en'){
    
                $messages = [
                    'reason.required' => "reason required",
                ];
    
            }
    
            $validator = FacadesValidator::make($request->all() , $rules , $messages);
    
            if($validator->fails() == false){

                

                if($lang == 'ar'){
                    return Utils::generateJSON(TRUE , Response::HTTP_OK , "", "تم الطلب بنجاح سيتم حذف حسابك خلال 14 يوم");
                }

                return Utils::generateJSON(TRUE , Response::HTTP_OK , "", "Request completed Successfuly, your account will deleted in 14 days");
                
            }else{

                $error     = $validator->errors();
                $allErrors = "";

                foreach($error->all() as $err){                
                    $allErrors .= $err . " <br/>";
                }
                

                return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST, $allErrors , "" );

            }

            
        }catch(\Exception $e){
            
            // do task when error
            
            if($lang == 'ar'){
                return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "خطأ غير متوقع: " . $e->getMessage(), []);
            }           
            
            return Utils::generateJSON(FALSE , Response::HTTP_BAD_REQUEST , "bad request: " . $e->getMessage(), []);            
        
        }

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
