<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\key;
use App\Models\Token;

class UserController extends Controller
{
    public function register(Request $req)
    {
    	//Validate data
        $data = $req->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:12'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        //Request is valid, create new user
        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => $req->password
        ]);
        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }

    //create Token
    function createToken($data)
    {

        $key = "Social";

        $payload = array(

            "iss" => "http://127.0.0.1:8000",

            "aud" => "http://127.0.0.1:8000/api",

            "iat" => time(),

            "nbf" => 1357000000,

            "data" => $data,
            // 'expires_in' => auth()->factory()->getTTL() * 60,

        );

        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
        
 	
 		// Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $jwt,
        ]);    
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:12'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }else{
            echo "login is scusse";
            $user= User::where('email', $request->email)->first();
            $t= Token::where('token', $request->token)->first();

            // dd($user->id);
            $token = $this->createToken($user->id);
            $tokenData = Token::create([
                'token' => $token,
                'user_id' => $user->id
            ]);

            $response = [
                'user' => $user,
                'token' => $token,
            ];
        
             return response($response, 201);
        }
    }


    function logout(Request $request)
    {

        //Decode Token
        
        $jwt = $request->bearerToken();
        
        $key = 'Social';
        
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        
        $userID = $decoded->data;
        
        //Check If Token Exits
        
        $userExist = Token::where("user_id",$userID)->first();
        
        if($userExist){
        
            $userExist->delete();
        
        }else{
        
            return response([
        
            "message" => "This user is already logged out"
            
            ], 404);
            
        }
        
            return response([
            
            "message" => "logout successfull"
            
            ], 200);
        
        }
    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
 
        $user = JWTAuth::authenticate($request->token);
 
        return response()->json(['user' => $user]);
    }
}

