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

// this is Auth system
Route::post('/user/auth/login' , [\App\Http\Controllers\user\Auth::class , 'login']);
Route::post('/user/auth/verfyOTP' , [\App\Http\Controllers\user\Auth::class , 'verfyOTP']);
Route::post('/user/auth/verfyOTP' , [\App\Http\Controllers\user\Auth::class , 'verfyOTP']);
Route::post('/uploadImage' , [\App\Http\Controllers\user\Auth::class , 'imageUploadPost']);

Route::post('/driver/auth/register' , [\App\Http\Controllers\driver\Auth::class , 'registerDriver']);
Route::post('/driver/auth/activeDriver' , [\App\Http\Controllers\driver\Auth::class , 'activateDriver']);

Route::post('/driver/auth/login' , [\App\Http\Controllers\driver\Auth::class , 'Login']);
Route::post('/driver/auth/verfyOTP' , [\App\Http\Controllers\driver\Auth::class , 'verfyOTP']);


Route::get('/user/getVehicles' , [\App\Http\Controllers\user\Orders::class , 'getVehicles']);




// just authrized users will access this 
//Route::group(['middleware' => ['auth:sanctum']] , function(){
Route::group(['middleware' => ['auth:users']] , function(){
            
    Route::post('user/AddOrder' , [\App\Http\Controllers\user\Orders::class , 'addOrder']);    
    Route::get('/user/getAvailableTrips' , [\App\Http\Controllers\user\Orders::class , 'getAvailableTrips']);
    Route::post('/user/bookingTrip' , [\App\Http\Controllers\user\Orders::class , 'bookingTrip']);
    Route::get('/user/orders/getMyOrders' , [\App\Http\Controllers\user\Orders::class , 'getMyOrders']);
    Route::get('/user/orders/getMyPastOrders' , [\App\Http\Controllers\user\Orders::class , 'getMyPastOrders']);
    Route::post('/user/orders/getOrderDetails' , [\App\Http\Controllers\user\Orders::class , 'getOrderDetails']);
    Route::get('/user/orders/getMyBookedTrips' , [\App\Http\Controllers\user\Orders::class , 'getMyBookedTrips']);
    Route::post('/user/orders/cancelOrder' , [\App\Http\Controllers\user\Orders::class , 'cancelOrder']);
    Route::get('/user/profile/getMyProfile' , [\App\Http\Controllers\user\Profile::class , 'getMyProfile']);
    Route::post('/user/profile/updateMyProfile' , [\App\Http\Controllers\user\Profile::class , 'updateMyProfile']);
    Route::get('test' , [\App\Http\Controllers\user\Auth::class, 'test']);
    
});


Route::group(['middleware' => ['auth:drivers']] , function(){
    Route::get('/driver/getNewOrders' , [\App\Http\Controllers\driver\Orders::class , 'getNewOrders']);
    Route::post('/driver/addTrip' , [\App\Http\Controllers\driver\Orders::class , 'addTrip']);
    Route::get('/driver/getMyAddedTrips' , [\App\Http\Controllers\driver\Orders::class , 'getMyAddedTrips']);
    Route::get('/driver/getMyOrders' , [\App\Http\Controllers\driver\Orders::class , 'getMyOrders']);
    Route::post('/driver/orders/getOrderDetails' , [\App\Http\Controllers\driver\Orders::class , 'getOrderDetails']);
    Route::post('driver/acceptOrder' , [\App\Http\Controllers\driver\Orders::class , 'acceptOrder']);
    Route::get('/driver/getMyProfile' , [\App\Http\Controllers\driver\Auth::class , 'getMyProfileDriver']);
    Route::put('/driver/updateProfile' , [\App\Http\Controllers\driver\Auth::class , 'updateDriverProfile']);
});