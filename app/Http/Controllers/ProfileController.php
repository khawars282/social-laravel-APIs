<?php

namespace App\Http\Controllers;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /*
        Returns user profile
        parameter: user id
    */
    public function showProfile(Request $request, $id)
    {
        //Get Bearer Token
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        //Get Id
        $userId = $decoded->data;

        if ($userId == $id) {

            $getUser = User::find($id);

            if (isset($getUser)) {
                return $getUser;
            } else {
                return response([
                    'message' => 'No user found'
                ], 404);
            }
        } else {
            return response([
                'message' => 'You are not authorized to perform this action'
            ], 401);
        }
    }



    
    //update user's data
    
    public function update(Request $request, $id)
    {
        //Get Bearer Token
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        //Get Id
        $userId = $decoded->data;


        if ($userId == $id) {
            $user = User::find($id);
            $user->update($request->all());
            $user->save();

            return $user;
        } else {
            return response([
                'message' => 'You are not authorized to perform this action'
            ], 401);
        }
    }


  
    // delete user's data
    
    public function delete(Request $request, $id)
    {
        //Get Bearer Token
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        //Get Id
        $userId = $decoded->data;

        if ($userId == $id) {
            $getUser = User::destroy($id);

            if ($getUser == 1) {
                return response([
                    'message' => 'User Deleted Succesfully'
                ]);
            } elseif ($getUser == 0) {
                return response([
                    'message' => 'Already deleted'
                ]);
            } else {
                return response([
                    'message' => 'Not user found'
                ], 404);
            }
        } else {
            return response([
                'message' => 'You are not authorized '
            ], 401);
        }
    }

    //    search user's by name
    
    public function search($name)
    {
        return User::where('name', 'like', '%' . $name . '%')->get();
    }
}
