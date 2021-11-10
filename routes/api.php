<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\My2ndController;

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
// Route::group(['middleware' => 'auth:sanctum'], function(){
//     //All secure URL's

//     });

// Route::middleware('auth:jwt')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::get("login",[My2ndController::class,'authenticate']);
Route::post("add",[My2ndController::class,'register']);
Route::get('logout', [My2ndController::class, 'logout']);
Route::get('get_user', [My2ndController::class, 'get_user']);

// Route::group(['middleware' => 'auth:api'], function(){
  
//     Route::get('logout', 'My2ndController@logout')->name('api.jwt.logout');

// });

// Route::group(['middleware' => ['jwt.verify']], function() {
//         Route::post('logout', [My2ndController::class, 'logout']);
//         Route::get('get_user', [My2ndController::class, 'get_user']);
//         Route::get('posts', [ProductController::class, 'index']);
//         Route::get('posts/{id}', [ProductController::class, 'show']);
//         Route::post('create', [ProductController::class, 'store']);
//         Route::put('update/{posts}',  [ProductController::class, 'update']);
//         Route::delete('delete/{posts}',  [ProductController::class, 'destroy']);
//     });
