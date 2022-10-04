<?php

namespace App\Http\Controllers\shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Settings extends Controller
{
    

    /**
     * @OA\Get(
     * path="/api/app/settings",    
     * summary="جلب بيانات اعدادات التطبيق",
     * description="",
     * operationId="app/settings",
     * tags={"App Settings"},     
     * @OA\Response(
     *    response=200,
     *    description="Success credentials response",
     *    @OA\JsonContent( example={
     *               "max_distance": 2000,
     *               "start_price": 5,
     *               "killo_price": 12,
     *               "support_phone": "",
     *               "terms": "",     
     *           } ),     
     *     )
     * )
     */
    public function getAppSettings(Request $request)
    {

        // get languae 
        $lang = $request->header('Accept-Language' , 'en');

        $settings = DB::table('settings')
        ->limit(1)
        ->get(['max_distance' , 'start_price' , 'killo_price' , 'support_phone', 'terms']);

        return  $settings;
                
    }

}
