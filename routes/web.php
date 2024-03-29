<?php

use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Barryvdh\Debugbar\Twig\Extension\Debug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/fastUsers' , function(){
    //Debugbar::info('info');
    $users = DB::table('user')->limit(50)->get();
    // $users = User::limit(2)->get();
    //return $users;
    return view('welcome');
});

Route::get('/mod' , function(){
    $i = 3 % 4;
    return $i;
});


Route::get('/admin' , [\App\Http\Controllers\admin\Clients::class , 'list'])->name('client.list');
Route::get('/admin/clients' , [\App\Http\Controllers\admin\Clients::class , 'list'])->name('client.list');
Route::get('/admin/drivers' , [\App\Http\Controllers\admin\Drivers::class , 'list'])->name('drivers.list');
Route::get('/admin/drivers/suatus/{driver_id}/{status}' , [\App\Http\Controllers\admin\Drivers::class , 'status'])->name('drivers.update.status');
Route::get('/admin/orders'  , [\App\Http\Controllers\admin\Orders::class  , 'list'])->name('orders.list');
Route::get('/admin/driver/details/{driver_id?}'  , [\App\Http\Controllers\admin\Drivers::class  , 'details'])->name('driver.details');



Route::get('viewImage/{file}' , [\App\Http\Controllers\Files::class , 'displayImage'])->name('view_img');

Route::get('/fmc/{firebaseToken?}/{title?}/{body?}', [\App\Http\Controllers\user\Orders::class , 'sendNotification']);