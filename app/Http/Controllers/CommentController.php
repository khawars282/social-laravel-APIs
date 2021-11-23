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
{
    public function create(Request $request, $id)
    {
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response()->json([
                'message' => 'Bearer token not found'
            ]);
        }

        $decoded = JWT::decode($token, new Key('Social', 'HS256'));

        $user = $decoded->data;
        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
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
             return response()->json([
                'message' => 'You are comment on this post',
                'comment' => $comment
            ], 200);

    }


    public function update(Request $request, $id)
    {
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response()->json([
                'message' => 'Bearer token not found'
            ]);
        }

        if ($request->comment == null) {
            return response()->json([
                'message' => 'content is required'
            ]);
        }

        $decoded = JWT::decode($token, new Key('Social', 'HS256'));

        $user = $decoded->data;

        $currentUser =(new test())->social_app->users;
        $user_id= $currentUser->findOne(['email' => $user->email]);
        
            $collection =(new test())->social_app->posts;

            $pid =new \MongoDB\BSON\ObjectId($request->id);
            $postExist= $collection->findOne([
                '_id' => $pid,
            ]);
        $updatedComment = $collection->findOneAndUpdate(
            [ '_id' => $pid ],
            [ '$set' => [ 'comments.comment' => $request->comment ]],
            
        );
        return response()->json([
            'message' => 'You are comment on this post',
            'comment' => $updatedComment
        ], 200);
    }


    public function delete(Request $request, $id)
    {
        $token = $request->bearerToken();


        if (!isset($token)) {
            return response()->json([
                'message' => 'Bearer token not found'
            ]);
        }

        $decoded = JWT::decode($token, new Key('Social', 'HS256'));

        $user = $decoded->data;
        $usercol = (new test())->social_app->users;
        $user_id= $usercol->findOne(['email' => $user->email]);
        $collection = (new test())->social_app->posts;
        $userExist= $collection->findOne([
           'user' => (string)$user_id['_id'],
        ]);
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
        return response()->json([
            'message' => 'You are comment delete on this post',
            'comment' => $postExist
        ], 200);
    }


    public function showComments(Request $request,$id)
    {
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        $decoded = JWT::decode($token, new Key('Social', 'HS256'));

        $user = $decoded->data;
        $usercol = (new test())->social_app->users;
        $user_id= $usercol->findOne(['email' => $user->email]);
        $userExist= $usercol->findOne([
            '_id' => $user_id->_id,
        ]);
        $collection = (new test())->social_app->posts;
        $postUser= $collection->findOne([
            'user' => (string)$userExist['_id'],
        ]);
        $pid =new \MongoDB\BSON\ObjectId($request->id);
        $postExist =$collection->find([
            '_id' => $pid,
        ]);
        
        
        $comments= $collection->findOne([
                'comments.postid' => $id,
            ]);
        return response()->json([
                'message' => 'All comment  on this post',
                'comment' => $comments
            ], 200);
            
    }}
