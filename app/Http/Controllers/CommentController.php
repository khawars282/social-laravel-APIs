<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\User;
use MongoDB\Client as test;
use App\Models\Comment;
use App\Models\Token;
use App\Models\ReceivedFriendRequest;
use App\Models\SentFriendRequest;
use App\Notifications\CommentOnYourPost;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;

use Firebase\JWT\Key;

class CommentController extends Controller
{/*
        Function to create a comment
        parameter: post_id
    */
    public function create(Request $request, $id)
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
        $user = $decoded->data;
        // dd($user);
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
            // dd($userExist);
            $collection =(new test())->social_app->posts;

            $pid =new \MongoDB\BSON\ObjectId($request->id);
            $postExist= $collection->findOne([
                '_id' => $pid,
            ]);
            $author = $postExist->user;
            $uid =new \MongoDB\BSON\ObjectId($author);
            $userExist= $currentUser->findOne([
                '_id' => $uid,
            ]);
            
        //Request is valid, create new post   
        $comment = $collection->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
        [
            '$set' => [
            'comments'=>[
            'comment' => $request->title,
            'userid' => (string)$userExist['_id'],
            'postid' => $id,
            ]
            ]
        ]);
             return response([
                'message' => 'You are comment on this post',
                'comment' => $comment
            ], 200);

    }


    /*
        Updates comment by id
    */
    public function update(Request $request, $id)
    {
        //Get Bearer Token
        // dd('sajjs');
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        if ($request->comment == null) {
            return response([
                'message' => 'content is required'
            ]);
        }

        //Decode
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));

        //Get Id
        $user = $decoded->data;

        //Get friends of this user
        //Get Post id
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
            // dd($userExist);
            $collection =(new test())->social_app->posts;

            $pid =new \MongoDB\BSON\ObjectId($request->id);
            $postExist= $collection->findOne([
                '_id' => $pid,
            ]);
        $updatedComment = $collection->findOneAndUpdate(
            [ '_id' => $pid ],
            [ '$set' => [ 'comments.comment' => $request->comment ]],
            
        );
        return response([
            'message' => 'You are comment on this post',
            'comment' => $updatedComment
        ], 200);
        // dd($updatedComment);
    }

    /*
        Deletes comment by id
    */

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
        $user = $decoded->data;
        $usercol = (new test())->social_app->users;
        //Check If Token Exits
        $user_id= $usercol->findOne(['email' => $user->email]);
        $collection = (new test())->social_app->posts;
        $userExist= $collection->findOne([
           'user' => (string)$user_id['_id'],
        ]);
        // dd($userExist);
        
        $pid =new \MongoDB\BSON\ObjectId($request->id);
        
             $comment = $collection->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)],
            [
            '$unset' => [
            'comments'=>[
            'comment' => '',
            ]
            ]
        ]);
        $postExist= $collection->findOne([
            '_id' => $pid,
        ]);
        return response([
            'message' => 'You are comment delete on this post',
            'comment' => $postExist
        ], 200);
            //    dd($postExist);
    }


    /*
        Returns user's comments
    */
    public function showComments(Request $request,$id)
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
        $user = $decoded->data;
        // dd($user);
        $usercol = (new test())->social_app->users;
        //Check If Token Exits
        $user_id= $usercol->findOne(['email' => $user->email]);
        $userExist= $usercol->findOne([
            '_id' => $user_id->_id,
        ]);
        $collection = (new test())->social_app->posts;
        $postUser= $collection->findOne([
            'user' => (string)$userExist['_id'],
        ]);
        $pid =new \MongoDB\BSON\ObjectId($request->id);
        // dd($postUser);
        $postExist =$collection->find([
            '_id' => $pid,
        ]);
        
        
        $comments= $collection->findOne([
                'comments.postid' => $id,
            ]);
        return response([
                'message' => 'All comment  on this post',
                'comment' => $comments
            ], 200);
            
        // return $comments;
    }}
