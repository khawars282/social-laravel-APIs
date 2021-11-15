<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\User;
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
        $userId = $decoded->data;

        //Get friends of this user
        $sentRequests = SentFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('receiver_id')->toArray();
        $recievedRequests = ReceivedFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('sender_id')->toArray();

        $getPost = Post::find($id);

        //user_id of author of this post
        $author = $getPost->user_id;

        //Get user
        $user = User::where('id', $author)->first();

        
        if (in_array($author, $sentRequests) || in_array($author, $recievedRequests) || $author == $userId || $getPost->privacy == false) {

            $commentCreated =  Comment::create([
                'user_id' => $userId,
                'post_id' => $id,
                'content' => $request->content,
            ]);

            $user->notify(new CommentOnYourPost($commentCreated));

            return $commentCreated;
        } else {
            return response([
                'message' => 'You are not allowed to comment on this post'
            ], 404);
        }
    }


    /*
        Updates comment by id
    */
    public function update(Request $request, $id)
    {
        //Get Bearer Token
        $token = $request->bearerToken();

        //Get comment
        $getComment = Comment::find($id);

        if (!$getComment) {
            return response([
                'message' => 'Comment does not exist'
            ]);
        }

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        if ($request->content == null) {
            return response([
                'message' => 'content is required'
            ]);
        }

        //Decode
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));

        //Get Id
        $userId = $decoded->data;

        //Get friends of this user
        $sentRequests = SentFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('receiver_id')->toArray();
        $recievedRequests = ReceivedFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('sender_id')->toArray();

        //Get Post id
        $postId = $getComment->post_id;
        $getPost = Post::find($postId);

        //user_id of author of this post
        $author = $getPost->user_id;

        //user_id of commenter
        $commenter = $getComment->user_id;

        /*
            If author of the posts is
            > User's friend
            > User is the author of the post
            > Post is public
            allow user to delete comment otherwise return unauthorized response
        */


        if ((in_array($author, $sentRequests) || in_array($author, $recievedRequests) || $author == $userId || $getPost->privacy == false) && $commenter == $userId) {

            $comment = Comment::where('id', $id)->where('user_id', $userId)->first();

            if ($comment) {
                $comment->content = $request->content;
                $comment->update();

                return $comment;
            } else {
                return response([
                    'message' => 'Something went wrong'
                ], 404);
            }
        } else {
            return response([
                'message' => 'You are not allowed to update this comment'
            ], 404);
        }
    }


    /*
        Deletes comment by id
    */

    public function delete(Request $request, $id)
    {
        //Get Bearer Token
        $token = $request->bearerToken();

        //Get comment
        $getComment = Comment::find($id);

        if (!$getComment) {
            return response([
                'message' => 'Comment does not exist'
            ]);
        }

        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        //Decode
        $decoded = JWT::decode($token, new Key('Social', 'HS256'));

        //Get Id
        $userId = $decoded->data;

        //Get friends of this user
        $sentRequests = SentFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('receiver_id')->toArray();
        $recievedRequests = ReceivedFriendRequest::all()->where('user_id', $userId)->where('status', true)->pluck('sender_id')->toArray();

        //Get Post id
        $postId = $getComment->post_id;
        $getPost = Post::find($postId);

        //user_id of author of this post
        $author = $getPost->user_id;

        //user_id of commenter
        $commenter = $getComment->user_id;

        
        if (in_array($author, $sentRequests) || in_array($author, $recievedRequests) || $author == $userId || $getPost->privacy == false && $commenter == $userId) {

            $comment = Comment::where('id', $id)->where('user_id', $userId)->first();

            if ($comment) {
                $comment->delete();

                return response([
                    'message' => 'Comment deleted successfully',
                    'comment' => $comment
                ]);
            } else {
                return response([
                    'message' => 'You are not allowed to comment on this post'
                ], 404);
            }
        } else {
            return response([
                'message' => 'You are not allowed to comment on this post'
            ], 404);
        }
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

        $comments = Comment::where('user_id', $userId)->get();

        return $comments;
    }}
