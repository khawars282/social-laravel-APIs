<?php

namespace App\Http\Controllers;


use MongoDB\Client as test;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;

use Firebase\JWT\Key;

class FriendController extends Controller
{
    public function sendRequest(Request $request, $id)
    {
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'token not found'
            ]);
        }
        //Decode data
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        //Get data
        $user = $decoded->data;
        try{
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
            $uid =new \MongoDB\BSON\ObjectId($request->id);
            $userExist= $currentUser->findOne([
                '_id' => $uid,
            ]);
        if (!isset($userExist)) {
            return response([
                'message' => 'Request receiver does not exist'
            ]);
        }
        if ($user_id->_id == $uid) {
            
            return response([
                'message' => 'You not send request to yourself'
            ]);
        }
        
        $requestsSent= $currentUser->findOne([
            'receiver.receiver_id' => (string)$user_id['_id'],
        ]);
        $requestsReceived= $currentUser->findOne([
            'sender.sender_id' => $uid,
        ]);
        if ($requestsSent == null && $requestsReceived == null) {
            //embed data 
            $saveFriendRequest = $currentUser->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
            [
                '$set' => [
                    'receiver'=>[
                        'receiver_id' => (string)$user_id['_id'],
                        'status' => false
                    ]
                    ]
            ]);
            $saveFriendRequest1 = $currentUser->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
            [
                '$set' => [
                    'sender'=>[
                        'sender_id' => $id,
                        'status' => false
                ]
                ]
            ]);
            $receiverFriendRequest = $currentUser->updateOne(['_id' => $user_id->_id],
            [
                '$set' => [
                    'receiver'=>[
                        'receiver_id' => (string)$user_id['_id'],
                        'status' => false
                    ]
                    ]
            ]);
            $receiverFriendRequest1 = $currentUser->updateOne(['_id' => $user_id->_id],
            [
                '$set' => [
                    'sender'=>[
                        'sender_id' => $id,
                        'status' => false
                ]
                ]
            ]);
            return response([
                'message' => 'Request sent to ' . $userExist->name .' This user '.$user_id->name
            ]);
        } else {
            return response([
                'message' => 'Friend request is already pending'
            ]);
        }
    }
    catch (Throwable $ex) {
        report($ex);
        return false;
    }
    }

    //    Function show requests 

    public function showRequests(Request $request)
    {
        //Get Bearer Token
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode token
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        //Get data
        $user = $decoded->data;
        try{
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
        $userExist= $currentUser->findOne(['_id' => $user_id->_id]);
        $requestsReceived= $currentUser->findOne([
            'receiver.receiver_id' => (string)$userExist['_id'],
        ]);
        $requestsSent= $currentUser->findOne([
            'sender.sender_id' => (string)$userExist['_id'],
        ]);
        if ($requestsReceived == null && $requestsSent == null) {
            return response([
                'message' => 'You have no friend requests'
            ]);
        } else {
            return response([
                'requests_sent' => $requestsSent,
                'requests_received' => $requestsReceived
            ]);
        }
    }
    catch (Throwable $ex) {
        report($ex);
        return false;
    }
    }


    public function acceptRequest(Request $request, $id)
    {
        // Bearer Token
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode token
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        //Get data
        $user = $decoded->data;
        try{
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
        $userExist= $currentUser->findOne(['_id' => $user_id->_id]);

        $requestsReceived= $currentUser->findOne([
            'receiver.receiver_id' => (string)$userExist['_id'],
        ]);
        $requestsReceivedStatus= $currentUser->findOne([
            'receiver.status' => true,
        ]);
        $requestsSent= $currentUser->findOne([
            'sender.sender_id' => $id,
        ]);
        $requestsSentStatus= $currentUser->findOne([
            'sender.status' => true,
        ]);

        
        if (isset($requestsReceived)) {

            if ($requestsReceivedStatus==  true && $requestsSentStatus== true) {
                return response([
                    'message' => 'Request already accepted'
                ]);
            }

            $saveFriendRequest = $currentUser->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
            [
                '$set' => [
                    'receiver'=>[
                        'receiver_id' => (string)$user_id['_id'],
                        'status' => true
                    ]
                    ]
            ]);
            $receiverFriendRequest = $currentUser->updateOne(['_id' => $user_id->_id],
            [
                '$set' => [
                    'receiver'=>[
                        'receiver_id' => (string)$user_id['_id'],
                        'status' => true
                    ]
                    ]
            ]);
            
            //Change status sender
            if (isset($requestsSent)) {
            $saveFriendRequest1 = $currentUser->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
            [
                '$set' => [
                    'sender'=>[
                        'sender_id' => $id,
                        'status' => true
                ]
                ]
            ]);
                $receiverFriendRequest1 = $currentUser->updateOne(['_id' => $user_id->_id],
            [
                '$set' => [
                    'sender'=>[
                        'sender_id' => $id,
                        'status' => true
                ]
                ]
            ]);
            }

            return response([
                'message' => 'Request accepted'
            ]);
        } else {
            return response([
                'message' => 'You are all raedy Request accepted'
            ]);
        }
    }
    catch (Throwable $ex) {
        report($ex);
        return false;
    }
    }


    
    public function deleteRequest(Request $request, $id)
    {
        // Bearer Token
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode token
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        //Get data
        $user = $decoded->data;
        try{
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
        $userExist= $currentUser->findOne(['_id' => $user_id->_id]);

        $requestsReceived= $currentUser->findOne([
            'receiver.receiver_id' => (string)$userExist['_id'],
        ]);
        $requestsSent= $currentUser->findOne([
            'sender.sender_id' => $id,
        ]);
        if (isset($requestsReceived)) {
            $saveFriendRequest = $currentUser->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
            [
                '$unset' => [
                    'receiver'=>[
                        'status' => false
                    ]
                    ]
            ]);
            $receiverFriendRequest = $currentUser->updateOne(['_id' => $user_id->_id],
            [
                '$unset' => [
                    'receiver'=>[
                        'status' => false
                    ]
                    ]
            ]);
           
        }
        if (isset($requestsSent)) {
            $saveFriendRequest1 = $currentUser->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
            [
                '$unset' => [
                    'sender'=>[
                        'status' => false
                ]
                ]
            ]);
                $receiverFriendRequest1 = $currentUser->updateOne(['_id' => $user_id->_id],
            [
                '$unset' => [
                    'sender'=>[
                        'status' => false
                ]
                ]
            ]);

            return response([
                'frind'=>'Request deleted',
                'message' => 'You have unsent the request'
            ]);
        }

        return response()->json([
            'message' => 'No request found'
        ]);
    }
    catch (Throwable $ex) {
        report($ex);
        return false;
    }
    }

    public function removeFriend(Request $request, $id)
    {
        // Bearer Token
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode token
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));
        //Get data
        $user = $decoded->data;
        try{
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
        $userExist= $currentUser->findOne(['_id' => $user_id->_id]);

        $requestsReceived= $currentUser->findOne([
            'receiver.receiver_id' => (string)$userExist['_id'],
        ]);
        $requestsSent= $currentUser->findOne([
            'sender.sender_id' => $id,
        ]);
        if (isset($requestsReceived)) {
            $saveFriendRequest = $currentUser->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
            [
                '$unset' => [
                    'receiver'=>[
                        'status' => true
                    ]
                    ]
            ]);
            $receiverFriendRequest = $currentUser->updateOne(['_id' => $user_id->_id],
            [
                '$unset' => [
                    'receiver'=>[
                        'status' => true
                    ]
                    ]
            ]);
           
        }
        if (isset($requestsSent)) {
            $saveFriendRequest1 = $currentUser->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
            [
                '$unset' => [
                    'sender'=>[
                        'status' => true
                ]
                ]
            ]);
                $receiverFriendRequest1 = $currentUser->updateOne(['_id' => $user_id->_id],
            [
                '$unset' => [
                    'sender'=>[
                        'status' => true
                ]
                ]
            ]);

            return response([
                'friend' => ' removed  from your list',
                'message' => 'removed a friend'
            ]);
        }


        return response([
            'message' => 'No friend found'
        ]);
    }
    catch (Throwable $ex) {
        report($ex);
        return false;
    }
    } 
}
