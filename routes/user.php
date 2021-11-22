<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

//user
Route::get("/login",[UserController::class,'authenticate']);
Route::post("/sign_up",[UserController::class,'register']);
Route::get('/EmailConfirmation/{email}', [UserController::class, 'confirmEmail']);

Route::group(['middleware' => ['verification']], function() {

        Route::get('/logout', [UserController::class, 'logout']);
        Route::get('/get_user/{token}', [UserController::class, 'get_user']);
        //profile
        Route::get('/showprofile/{id}', [UserController::class, 'showProfile']);
        Route::put('/update/{id}', [UserController::class, 'update']);
        Route::delete('/delete/{id}', [UserController::class, 'delete']);
        Route::post('/search/{name}', [UserController::class, 'search']);
    });
    