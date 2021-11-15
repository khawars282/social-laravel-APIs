<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

//user
Route::get("/login",[UserController::class,'authenticate']);
Route::post("/sign_up",[UserController::class,'register']);
Route::get('/EmailConfirmation/{email}', [UserController::class, 'confirmEmail']);

Route::group(['middleware' => ['verification']], function() {

        Route::get('/logout', [UserController::class, 'logout']);
        Route::get('/get_user/{token}', [UserController::class, 'get_user']);
        //post
        Route::post('/create', [PostController::class, 'store']);
        Route::put('/update/{id}',  [PostController::class, 'update']);
        Route::delete('/delete/{id}',  [PostController::class, 'destroy']);
        Route::get('/posts/{title}', [PostController::class, 'show']);
        //comment
        Route::get('/showcomment', [CommentController::class, 'showComments']);
        Route::post('/post/{id}/createComment', [CommentController::class, 'create']);
        Route::put('/updateComment/{id}',  [CommentController::class, 'update']);
        Route::delete('/deleteComment/{id}',  [CommentController::class, 'delete']);
        //frind
        Route::post('/sendRequest/{id}', [FriendController::class, 'sendRequest']);
        Route::get('/showRequests', [FriendController::class, 'showRequests']);
        Route::get('/acceptRequest/{id}', [FriendController::class, 'acceptRequest']);
        Route::get('/deleteRequest/{id}', [FriendController::class, 'deleteRequest']);
        Route::get('/removeFriend/{id}', [FriendController::class, 'removeFriend']);
        //profile
        Route::get('/showprofile/{id}', [UserController::class, 'showProfile']);
        Route::put('/update/{id}', [UserController::class, 'update']);
        Route::delete('/delete/{id}', [UserController::class, 'delete']);
        Route::post('/search/{name}', [UserController::class, 'search']);
    });
    