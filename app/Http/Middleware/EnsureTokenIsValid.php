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

    $user_id = $decoded->data;

    $var = Token::where('user_id', $user_id)->first();

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
    
            'Error' => 'Incorrect userid = '.$user_id
    
            ], 400);

    } else {
        return $next($request);
    }

}

     
}

