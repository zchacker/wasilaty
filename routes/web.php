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

Route::get('viewImage/{file}' , [\App\Http\Controllers\user\Auth::class , 'viewImage']);