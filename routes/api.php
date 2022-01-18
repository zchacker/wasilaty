<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('user/auth/login' , [\App\Http\Controllers\user\Auth::class , 'login']);
Route::post('user/auth/verfyOTP' , [\App\Http\Controllers\user\Auth::class , 'verfyOTP']);


// just authrized users will access this 
Route::group(['middleware' => ['auth:sanctum']] , function(){
    
    Route::get('test' , [\App\Http\Controllers\user\Auth::class, 'test']);

});
