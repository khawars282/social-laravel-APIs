<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;


Route::group(['middleware' => ['verification']], function() {

        // //post
        // Route::post('/create', [PostController::class, 'store']);
        // Route::put('/update/{id}',  [PostController::class, 'update']);
        // Route::delete('/delete/{id}',  [PostController::class, 'destroy']);
        // Route::get('/posts/{title}', [PostController::class, 'show']);
        // //comment
        // Route::get('/showcomment', [CommentController::class, 'showComments']);
        // Route::post('/post/{id}/createComment', [CommentController::class, 'create']);
        // Route::put('/updateComment/{id}',  [CommentController::class, 'update']);
        // Route::delete('/deleteComment/{id}',  [CommentController::class, 'delete']);
    });
    