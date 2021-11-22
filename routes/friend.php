<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendController;

Route::group(['middleware' => ['verification']], function() {
    //friend
        Route::post('/sendRequest/{id}', [FriendController::class, 'sendRequest']);
        Route::get('/showRequests', [FriendController::class, 'showRequests']);
        Route::get('/acceptRequest/{id}', [FriendController::class, 'acceptRequest']);
        Route::get('/deleteRequest/{id}', [FriendController::class, 'deleteRequest']);
        Route::get('/removeFriend/{id}', [FriendController::class, 'removeFriend']);
    });
    