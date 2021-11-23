<?php

namespace App\Http\Controllers;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MongoDB\Client as test;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function showProfile(Request $request, $id)
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
        $userExist= $currentUser->findOne([
                '_id' => $user_id->_id,
            ]);
            $uid =new \MongoDB\BSON\ObjectId($request->id);
        if ($userExist == $uid) {

            $getUser  =$collection->findOne([
                '_id'=> $uid
            ]);

            if (isset($getUser)) {
                return $getUser;
            } else {
                return response([
                    'message' => 'No user found'
                ], 404);
            }
        } else {
            return response([
                'message' => 'You are not authorized'
            ], 401);
        }
    }



    
    public function search($name)
    {
        $currentUser =(new test())->social_app->users;
        $user= $currentUser->findOne(['name' => $name]);
        return $user;
    }
}
