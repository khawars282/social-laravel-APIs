<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MongoDB\Client as test; 
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\key;
use App\Models\Token;
use App\Mail\ConfirmEmail;
use Illuminate\Support\Facades\Mail;
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
        $collection = (new test())->social_app->users;
            $user = $collection->insertOne([            
            'name' => $req->name,           
            'email' => $req->email,            
            'password' => $req->password,   ]); 
            // dd($user);
        // //Request is valid, create new user
        // $user = User::create([
        //     'name' => $req->name,
        //     'email' => $req->email,
        //     'password' => $req->password
        // ]);
        $url =url('api/EmailConfirmation/'.$req['email']);
        Mail::to($req->email)->send(new ConfirmEmail($url,'khawars282@gmail.com'));
        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }
    // confirmEmail email_verified_at genrate / save
    public function confirmEmail($email){
        $collection = (new test())->social_app->users;
        $user= $collection->findOne(['email' => $email]);
        
        // $user= User::where('email', $email)->first();
        // $user->email_verified_at =$user->email_verified_at =time();
        // dd($user);
        // save({$user});
        // $collection->updateOne(
        //     ['email' => $email],
        //     ['$set' => ['email_verified_at' => date('Y-m-d h:i:s')]
        //     ]);
            $email_verified_at = '';

            if($email_verified_at != null){

                return response([

                'message'=>'Already Verified'

            ]);

            }elseif ($user) {

                $collection->updateOne(

                ['email' => $email],

                ['$set' => ['email_verified_at' => date('Y-m-d h:i:s')]

            ]);

            return response([

            'message'=>'Eamil Verified',
            'user'=>$user

            ]);

            }else{

            return response([

            'message'=>'Error'
            ]);
            }

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
        $collection = (new test())->social_app->users;

        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:12'
        ]);
// dd($credentials);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }else{
            //echo "login is scusse";
            $user= $collection->findOne(['email' => $request->email]);
       
            if($user)
            {
                // dd($user->id);
                $token = $this->createToken($user);

                $collection->updateOne(

                    ['email' => $request->email],
    
                    ['$set' => ['token' => $token],
    
                ]);
            
                // $tokenData = Token::create([
                //     'token' => $token,
                //     'user_id' => $user->id
                // ]);

                $response = [
                    'user' => $user,
                    'token' => $token,
                ];
            }else{
                $response = [
                    // 'user' => $user,
                    'token' => "you already login",
                ];
            }
        
             return response($response, 201);
        }
    }


    function logout(Request $request)
    {
        
        //Decode Token
        
        $jwt = $request->bearerToken();
        
        $key = 'Social';
        
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        
        
        $user = $decoded->data;
        $collection = (new test())->social_app->users;
        //Check If Token Exits
        $user_id= $collection->findOne(['email' => $user->email]);
        
        $userExist= $collection->findOne([
                '_id' => $user_id->_id,
            ]);
        
        if(isset($userExist)){
        
            $collection->updateOne(

                ['email' => $userExist->email],

                ['$unset' => ['token' => ''],

            ]);
        
            
        }else{
        
            return response([
        
            "message" => "This user is already logged out"
            
            ], 404);
            
        }
        

        
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

