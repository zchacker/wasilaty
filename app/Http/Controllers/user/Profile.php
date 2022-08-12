<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shared\Utils;
use App\Models\User;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator as FacadesValidator;
use Symfony\Component\HttpFoundation\Response;

class Profile extends Controller
{
    

    /**
     * @OA\Get(
     * path="/api/user/profile/getMyProfile",
     * security={{ "apiAuth": {} }},
     * summary="جلب ملفي الشخصي",
     * description="تقوم بجلب الملف الشخصي",
     * operationId="profile/getMyProfile",
     * tags={"UserProfile"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example={
     *               "id": 2,
     *               "name": "Ahmed Nagem",
     *               "phone": "966536301031",
     *               "created_at": "2022-01-17T23:03:14.000000Z",
     *               "updated_at": "2022-06-20T07:45:15.000000Z",     
     *           } ),     
     *     )
     * )
     */
    public function getMyProfile(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $user_id = $request->user()->id;
        $user    = User::where(['id' => $user_id]);

        if($user == null){
            //return response()->json(['status' => 'error' , 'message' => 'user not found']);
            if($lang == 'ar'){
                return Utils::generateJSON(FALSE , Response::HTTP_NOT_FOUND , "المستخدم غير موجود", []);    
            }
            return Utils::generateJSON(FALSE , Response::HTTP_NOT_FOUND , "user not found", []);
        }
        
        return  $user->limit(1)->get()[0];
                
    }

    /**
     * @OA\Post(
     * path="/api/user/profile/updateMyProfile",
     * security={{ "apiAuth": {} }},
     * summary="تحديث ملفي الشخصي",
     * description="تقوم بتحديث الملف الشخصي",
     * operationId="profile/updateMyProfile",
     * tags={"UserProfile"},
     * @OA\RequestBody(
     *    required=true,
     *    description="جميع الخيارات إلزامية",
     *    @OA\JsonContent(
     *       required={"name","phone"},
     *       @OA\Property(property="name", type="string", format="string", example="Ahmed Nagem"),
     *       @OA\Property(property="phone", type="string", format="string", example="966536301031"),
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
    public function updateMyProfile(Request $request)
    {
        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        try{

            $user = User::find($request->user()->id);
            
            if($user->phone != $request->phone){
                
                $db_user = User::where('phone', $request->phone)->first();

                if(!$db_user){

                    $user->name  = $request->name;
                    $user->phone = $request->phone;
                    $user->save();

                    if($lang == 'ar'){
                        return Utils::generateJSON(TRUE , Response::HTTP_OK , "", "تم التحديث بنجاح");
                    }           
                    return Utils::generateJSON(TRUE , Response::HTTP_OK , "", "Updated Successfuly");
    
                }else{

                    if($lang == 'ar'){
                        return Utils::generateJSON(FALSE , Response::HTTP_NOT_ACCEPTABLE , "رقم الهاتف مستخدم من مستخدم آخر", []);
                    }           
                    
                    return Utils::generateJSON(FALSE , Response::HTTP_NOT_ACCEPTABLE , "Phone number used by other user" , []);            
        
                    
                }

            }else{
                
                $user->name  = $request->name;
                $user->phone = $request->phone;
                $user->save();

                if($lang == 'ar'){
                    return Utils::generateJSON(TRUE , Response::HTTP_BAD_REQUEST , "", "تم التحديث بنجاح");
                }           
                return Utils::generateJSON(TRUE , Response::HTTP_BAD_REQUEST , "", "Updated Successfuly");

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
     * path="/api/user/update_firebase_token",
     * security={{ "apiAuth": {} }},
     * summary="تحديث ملفي الشخصي",
     * description="تقوم بتحديث الملف الشخصي",
     * operationId="user/update_firebase_token",
     * tags={"UserProfile"},
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

            $token = $request->firebase_token;        
            $user  = User::find($request->user()->id);

            $rules = array(
                'firebase_token' => 'required',
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

                $user->firebase_token  = $token;
                
                $user->save();

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


}
