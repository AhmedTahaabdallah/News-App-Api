<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \App\Comment;
use \App\Post;
use Carbon\Carbon;
use \App\Http\Resources\CommentResource;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required',
            'post_id' => 'required'
        ]);

        $post = Post::find($request->get('post_id'));
        if($post == null){
            return new CommentResource(['error' => 'please enter correct post_id']);
        }
        if(strlen(trim($request->get('content'))) < 1 ){
            return new CommentResource(['error' => 'The lowest number of comment content is one letters']);
        }

        if(strlen(trim($request->get('content'))) > 3500 ){
            return new CommentResource(['error' => 'The largest  number of comment content is 3500 letters']);
        }     
        $comment = new Comment();
        $comment->content = trim($request->get('content'));
        $comment->date_written = Carbon::now()->format('y-m-d H:i:s');;
        $comment->post_id = $request->get('post_id');
        $comment->user_id = $request->user()->id;
        $comment->save();
        //return new CommentResource($comment);
        return new CommentResource(['status' => 'comment added']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
