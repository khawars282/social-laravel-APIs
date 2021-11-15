<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Token;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;

use Firebase\JWT\Key;

class PostController extends Controller
{
    public function store(Request $request)
    {       
        $token = $request->bearerToken();
        
        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        
        $userId = $decoded->data;
        
        $data = $request->only('title', 'description');
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'description' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new post   
        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
             'user_id' => $userId
        ]);

        //Post created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post
        ], Response::HTTP_OK);
    }

    public function show(Request $request , $id)
    {
        $token = $request->bearerToken();
        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        
        $userId = $decoded->data;
        
        //find post
        $post = Post::where('user_id' , $userId)->where('id', $id)->first();

        //check post
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, post not found.'
            ], 400);
        }
    
        return $post;
    }

    public function update(Request $request, $title)
    {
        $token = $request->bearerToken();
        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        
        $userId = $decoded->data;

        //Validate data
        $data = $request->only('title', 'description');
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'description' => 'required',
            
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //find post by title
        $post = Post::where('user_id' , $userId)->where('title', $title)->first();

        //Request is valid, update post
        $post = Post::update([
            'title' => $request->title,
            'description' => $request->description
        ]);

        //Post updated, return success response
        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post
        ], Response::HTTP_OK);
    }


    public function destroy(Request $request, $id)
    {
        $token = $request->bearerToken();
        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }
        
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        
        $userId = $decoded->data;

        //find post by id
        $post = Post::where('user_id' , $userId)->where('id', $id)->first();
        
        //delete post
        $post->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ], Response::HTTP_OK);
        
    }
}
