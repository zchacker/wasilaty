<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

// for test

Route::get('/users' , function(){
    //$users = User::limit(50)->get();
    $users = User::get();
    return $users;
});

Route::get('/fastUsers' , function(){
    $users = DB::table('user')->get();
    return $users;
});


// this is Auth system
Route::post('/user/auth/login' , [\App\Http\Controllers\user\Auth::class , 'login']);
Route::post('/user/auth/verfyOTP' , [\App\Http\Controllers\user\Auth::class , 'verfyOTP']);
Route::post('/uploadImage' , [\App\Http\Controllers\user\Auth::class , 'imageUploadPost']);

Route::post('/driver/auth/register' , [\App\Http\Controllers\driver\Auth::class , 'registerDriver']);
Route::post('/driver/auth/activeDriver' , [\App\Http\Controllers\driver\Auth::class , 'activateDriver']);

Route::post('/driver/auth/login' , [\App\Http\Controllers\driver\Auth::class , 'Login']);
Route::post('/driver/auth/verfyOTP' , [\App\Http\Controllers\driver\Auth::class , 'verfyOTP']);


Route::get('/user/getVehicles' , [\App\Http\Controllers\user\Orders::class , 'getVehicles']);

Route::get('/app/settings', [\App\Http\Controllers\shared\Settings::class , 'getAppSettings']);



// just authrized users will access this 
//Route::group(['middleware' => ['auth:sanctum']] , function(){
Route::group(['middleware' => ['auth:users']] , function(){
            
    Route::post('user/AddOrder' , [\App\Http\Controllers\user\Orders::class , 'addOrder']);    
    Route::get('/user/getAvailableSeatsForTrip/{trip_id?}' , [\App\Http\Controllers\user\Orders::class , 'getAvailableSeatsForTrip']);
    Route::get('/user/getAvailableTrips' , [\App\Http\Controllers\user\Orders::class , 'getAvailableTrips']);
    Route::post('/user/bookingTrip' , [\App\Http\Controllers\user\Orders::class , 'bookingTrip']);
    
    Route::get('/user/orders/getMyOrders' , [\App\Http\Controllers\user\Orders::class , 'getMyOrders']);
    Route::get('/user/orders/getMyPastOrders' , [\App\Http\Controllers\user\Orders::class , 'getMyPastOrders']);
    Route::post('/user/orders/getOrderDetails' , [\App\Http\Controllers\user\Orders::class , 'getOrderDetails']);
    Route::get('/user/orders/getMyBookedTrips' , [\App\Http\Controllers\user\Orders::class , 'getMyBookedTrips']);
    
    Route::post('/user/orders/cancelMultiPathOrder' , [\App\Http\Controllers\user\Orders::class , 'cancelMultiPathOrder']);
    Route::post('/user/orders/cancelOrder' , [\App\Http\Controllers\user\Orders::class , 'cancelOrder']);
    
    // multi path orders
    Route::get('/user/orders/getMyMultiPathOrders' , [\App\Http\Controllers\user\Orders::class , 'getMyMultiPathOrders']);
    Route::post('/user/orders/getMultiPathOrdersDetails' , [\App\Http\Controllers\user\Orders::class , 'getMultiPathOrdersDetails']);


    Route::get('/user/get/offer/{order_id?}' , [\App\Http\Controllers\user\Offers::class , 'getOrderOffers']);
    Route::post('/user/offer/accept' , [\App\Http\Controllers\user\Offers::class , 'acceptOffer']);

    Route::get('/user/profile/getMyProfile' , [\App\Http\Controllers\user\Profile::class , 'getMyProfile']);
    Route::post('/user/profile/updateMyProfile' , [\App\Http\Controllers\user\Profile::class , 'updateMyProfile']);
    Route::post('/user/getDriverLocation' , [\App\Http\Controllers\driver\Data::class , 'getDriverLocation']);

    Route::post('/user/order/addOrderWithMultiPath' , [\App\Http\Controllers\user\Orders::class , 'addOrderWithMultiPath']);
    Route::post('/user/update_firebase_token' , [\App\Http\Controllers\user\Profile::class , 'update_firebase_token']);
    
    Route::post('/user/request_account_deletion' , [\App\Http\Controllers\user\Auth::class , 'request_account_deletion']);

    Route::get('test' , [\App\Http\Controllers\user\Auth::class, 'test']);
    
});



Route::group(['middleware' => ['auth:drivers']] , function(){

    Route::get('/driver/getNewOrders' , [\App\Http\Controllers\driver\Orders::class , 'getNewOrders']);
    Route::get('/driver/getNewMultiPathOrders' , [\App\Http\Controllers\driver\Orders::class , 'getMultiPathOrders']);
    Route::post('/driver/getMultiPathOrdersDetails' , [\App\Http\Controllers\driver\Orders::class , 'getMultiPathOrdersDetails']);
    Route::get('/driver/order/getMyMultiPathOrders' , [\App\Http\Controllers\driver\Orders::class , 'getMyMultiPathOrders']);
    Route::get('/driver/order/getMyPastMultiPathOrders' , [\App\Http\Controllers\driver\Orders::class , 'getMyPastMultiPathOrders']);
    
    // controller order
    Route::post('/driver/order/updateMultipathOrderStatus' , [\App\Http\Controllers\driver\Orders::class , 'updateMultiPathStatus']);
    Route::post('/driver/order/updateOrderStatus' , [\App\Http\Controllers\driver\Orders::class , 'updateMultiPathStatus']);

    Route::post('/driver/addTrip' , [\App\Http\Controllers\driver\Orders::class , 'addTrip']);
    Route::get('/driver/getMyAddedTrips' , [\App\Http\Controllers\driver\Orders::class , 'getMyAddedTrips']);
    Route::get('/driver/bookedTripUsers/{trip_id?}' , [\App\Http\Controllers\driver\Orders::class , 'bookedTripUsers']);
    
    Route::get('/driver/orders/getMyOrders' , [\App\Http\Controllers\driver\Orders::class , 'getMyOrders']);
    Route::get('/driver/orders/getMyPastOrders' , [\App\Http\Controllers\driver\Orders::class , 'getMyPastOrders']);
    Route::post('/driver/orders/getOrderDetails' , [\App\Http\Controllers\driver\Orders::class , 'getOrderDetails']);
    
    Route::post('driver/acceptOrder' , [\App\Http\Controllers\driver\Orders::class , 'acceptOrder']);
    Route::get('/driver/getMyProfile' , [\App\Http\Controllers\driver\Auth::class , 'getMyProfileDriver']);    
    Route::post('/driver/updateProfile' , [\App\Http\Controllers\driver\Auth::class , 'updateDriverProfile']);
    
    Route::post('/driver/updateLocation' , [\App\Http\Controllers\driver\Data::class , 'updateLocation']);
    Route::post('/driver/update_firebase_token' , [\App\Http\Controllers\driver\Auth::class , 'update_firebase_token']);
    Route::post('/driver/request_account_deletion' , [\App\Http\Controllers\driver\Auth::class , 'request_account_deletion']);
    
    Route::post('/driver/offer/add' , [\App\Http\Controllers\driver\Offers::class , 'add_offer']);

});