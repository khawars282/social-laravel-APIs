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

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        // dd($user);
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
        $userExist= $currentUser->findOne([
                '_id' => $user_id->_id,
            ]);
            // dd($userExist);
            $collection =(new test())->social_app->posts;
        //Request is valid, create new post   
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
        // dd('khawar');
        $token = $request->bearerToken();
        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        
        $user = $decoded->data;
        
        // dd($my);
        //find post
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        $collection =(new test())->social_app->posts;
        
        $userExist= $currentUser->findOne([
                '_id' => $user_id->_id,
            ]);
        //Request is valid, create new post 
        $pid =new \MongoDB\BSON\ObjectId($request->id);
        $post = $collection->findOne([
            
            '_id'=> $pid,
            'user' => (string)$userExist['_id'],
        ]);
        
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
        
        $user = $decoded->data;

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
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        $userExist= $currentUser->findOne([
                '_id' => $user_id->_id,
            ]);
            // dd($userExist);
            $collection =(new test())->social_app->posts;
        //Request is valid, create new post   
        $post = $collection->findOne([
             'user' => (string)$userExist['_id'],
             'title' => $request->title,
        ]);
        //Request is valid, update post
        $post = $collection->updateOne(

            ['title' => $request->title],

            ['$set' => ['description' => $request->description],

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
        // dd('jnjb');
        $token = $request->bearerToken();
        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }
        
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        
        $user = $decoded->data;

        // //find post by id
        $usercol = (new test())->social_app->users;
        //Check If Token Exits
        $postExist= $usercol->findOne([
           'user' => $user,
        ]);
        // dd($user);
        $collection = (new test())->social_app->posts;
        $pid =new \MongoDB\BSON\ObjectId($request->id);
        $postExist= $collection->findOne([
                '_id' => $pid,
            ]);
            // dd($postExist);
            $collection->deleteOne(

                ['_id' => $pid],

               );
               
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ], Response::HTTP_OK);
        
    }
}
