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
        // $user_id= $usercol->findOne(['email' => $user->email]);
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
            // $coment = $collection->deleteOne(

            //     ['_id' =>[ 'comments.comment' => $pid ] ],

            //    );
               
            $coment =$collection->deleteOne(array("_id" => $pid),
                array('$unset' => array('comments.userid' => '')));
                // $coment = $collection->updateOne(

                //     ['_id' => $pid],
    
                //     ['$unset' => ['comments.' => ''],
    
                // ]);
               dd($coment);
    }


    /*
        Returns user's comments
    */
    public function showComments(Request $request)
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

        $usercol = (new test())->social_app->users;
        //Check If Token Exits
        // $user_id= $usercol->findOne(['email' => $user->email]);
        $postExist= $usercol->findOne([
           'user' => $user,
        ]);
        // dd($user);
        $collection = (new test())->social_app->posts;
        $pid =new \MongoDB\BSON\ObjectId($request->id);
        $postExist= $collection->findOne([
                'comments.userid' => $pid,
            ]);
            // dd($postExist);
            // $coment = $collection->deleteOne(

            //     ['_id' =>[ 'comments.comment' => $pid ] ],

            //    );
               
            $coment =$collection->find([
                'comments.comment' => $pid,
            ]);
            dd($postExist);
        return $comments;
    }}
