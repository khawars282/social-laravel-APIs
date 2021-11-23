<?php

namespace App\Http\Controllers;

use App\Models\Post;
use MongoDB\Client as test;
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
        
        $user = $decoded->data;
        
        $data = $request->only('title', 'description');
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
        $userExist= $currentUser->findOne([
                '_id' => $user_id->_id,
            ]);
            $collection =(new test())->social_app->posts;
        $post = $collection->insertOne([
            'title' => $request->title,
            'description' => $request->description,
             'user' => (string)$userExist['_id'],
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
        
        $user = $decoded->data;
        
        
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        $collection =(new test())->social_app->posts;
        
        $userExist= $currentUser->findOne([
                '_id' => $user_id->_id,
            ]);
        $pid =new \MongoDB\BSON\ObjectId($request->id);
        $post = $collection->findOne([
            
            '_id'=> $pid,
            'user' => (string)$userExist['_id'],
        ]);
        
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
        
        $user = $decoded->data;

        //Validate data
        $data = $request->only('title', 'description');
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'description' => 'required',
            
        ]);

        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        $userExist= $currentUser->findOne([
                '_id' => $user_id->_id,
            ]);
            // dd($userExist);
            $collection =(new test())->social_app->posts;
        $post = $collection->findOne([
             'user' => (string)$userExist['_id'],
             'title' => $request->title,
        ]);
        $post = $collection->updateOne(

            ['title' => $request->title],

            ['$set' => ['description' => $request->description],

        ]);
     
        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post
        ], Response::HTTP_OK);
    }


    public function delete(Request $request, $id)
    {
        $token = $request->bearerToken();
        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }
        
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        
        $user = $decoded->data;

        $usercol = (new test())->social_app->users;
        $postExist= $usercol->findOne([
           'user' => $user,
        ]);
        $collection = (new test())->social_app->posts;
        $pid =new \MongoDB\BSON\ObjectId($request->id);
        $postExist= $collection->findOne([
                '_id' => $pid,
            ]);
            $collection->deleteOne(

                ['_id' => $pid],

               );
               
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ], Response::HTTP_OK);
        
    }
}
