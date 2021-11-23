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
        Route::get('/showUser', [UserController::class, 'get_user']);
        //post
        Route::post('/create', [PostController::class, 'store']);
        Route::put('/update/{title}',  [PostController::class, 'update']);
        Route::delete('/deletePost/{id}',  [PostController::class, 'delete']);
        Route::get('/posts/{id}', [PostController::class, 'show']);
        //comment
        Route::get('/post/{id}/showComment', [CommentController::class, 'showComments']);
        Route::post('/post/{id}/createComment', [CommentController::class, 'create']);
        Route::put('/post/{id}/updateComment',  [CommentController::class, 'update']);
        Route::delete('/post/{id}/deleteComment',  [CommentController::class, 'delete']);
        
    });
    