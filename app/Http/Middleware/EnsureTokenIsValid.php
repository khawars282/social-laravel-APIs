<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Models\User;

use Illuminate\Support\Facades\Validator;

use Firebase\JWT\JWT;
use MongoDB\Client as test; 
use Firebase\JWT\Key;

class EnsureTokenIsValid
{
    
      
    /**

* Handle an incoming request.

*

* @param \Illuminate\Http\Request $request

* @param \Closure $next

* @return mixed

*/

public function handle(Request $request, Closure $next)
{
    //Check User With Token

    $token = $request->bearerToken();

    $decoded = JWT::decode($token, new Key('Social', 'HS256'));

    $user = $decoded->data;
    // dd($user);
    // $var = Token::where('user_id', $user_id)->first();
    $collection = (new test())->social_app->users;
        //Check If Token Exits
        $user_id= $collection->findOne(['email' => $user->email]);
            $var = $collection->findOne([
                '_id' => $user_id->_id,
            ]);
            //  dd($user_id['_id']);
             $varr = json_encode($var['_id']);
             $var2 = json_decode($varr, true);
            //  dd($var2['$oid']);
    //Find User From With Id

    if(!isset($var)) {

        // $uid = User::find($user_id);

        // return response([

        // 'Status' => '200',

        // 'email' => $uid->email,

        // 'password' => $uid->password

        // ], 200);
        return response([

            'Status' => '400',
    
            'message' => 'Bad Request',
    
            'Error' => 'Incorrect userid = '.$user
    
            ], 400);

    } else {
        return $next($request);
    }

}

     
}

