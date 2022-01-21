<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Symfony\Component\HttpFoundation\Response;

class Auth extends Controller
{
    //

    public function Login(Request $request)
    {
        $phone = $request->input('phone');
        $user  = User::where('phone' , $phone)->first();
        $otp   = 1111;// $this->generateOPT();

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
                $json = $this->generateJSON(TRUE , Response::HTTP_OK, "", $data);

                return $json;

            }catch(\Illuminate\Database\QueryException $ex){
                //dd($ex->getMessage());

                $data = new \stdClass();
                $data->message = "";
                $json = $this->generateJSON(FALSE , Response::HTTP_NOT_FOUND , "worng json", $data);
                return $json;
            }

        }else{

            User::where('phone', $phone)            
            ->update(['one_time_password' => $otp , "otp_requested_time" => date('Y-m-d H:i:s')]);

            $data = new \stdClass();
                $data->message = "get otp";
                $json = $this->generateJSON(TRUE , Response::HTTP_OK , "", $data);

            return $json;

        }        
    }

    public function verfyOTP( Request $request )
    {
        
        $phone  = $request->input('phone');
        $otp    = $request->input('otp');
        $user   = User::where(['phone' => $phone , "one_time_password" => $otp ])->first();

        if($user == NULL)
        {
            $data = new \stdClass();
            $data->message = "";
            $json = $this->generateJSON( FALSE , Response::HTTP_UNAUTHORIZED , "user not found or wrong otp" , "");
            return $json;
        }
        else
        {
            // generate token
            
            //$ourUser = FacadesAuth::user();

            $token = $user->createToken('token')->plainTextToken;
            
            // if( !FacadesAuth::attempt($request->only('phone')) )
            // {
            //     return $this->generateJSON( TRUE , Response::HTTP_UNAUTHORIZED , "worng data" , "");
            // }

            $data = new \stdClass();
            $data->token = $token;
            $json = $this->generateJSON( TRUE , Response::HTTP_OK , "" , $data);
            return $json;

        }

    }

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
        $data->token = $imageName;
        return $this->generateJSON( TRUE , Response::HTTP_OK , "" , $data);

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
