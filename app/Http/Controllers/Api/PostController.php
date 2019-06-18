<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \App\Post;
use \App\Category;
use Carbon\Carbon;
use Storage;
use File;
use \App\Http\Resources\PostsResources;
use \App\Http\Resources\PostResources;
use \App\Http\Resources\CommentsResources;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::join('users','posts.user_id','users.id')
        ->join('categories','posts.category_id','categories.id')
        ->select('posts.id','posts.title',
        'posts.content','posts.date_written','posts.featured_image',
        'posts.votes_up','posts.votes_down',
        'posts.voters_up','posts.voters_down',
        'posts.user_id','users.name as autherName',
        'users.avater as autherAvater','posts.category_id','categories.title as categoryTitle')        
        ->with(['comments', 'author','category'])
        ->paginate(env('POSTS_PER_PAGE'));
        return new PostsResources($posts);
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
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
            'featured_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        if(strlen(trim($request->get('title'))) < 20 ){
            return new PostResources(['error' => 'The lowest number of post title is 20 letters']);
        }

        if(strlen(trim($request->get('title'))) > 150 ){
            return new PostResources(['error' => 'The largest  number of post title is 150 letters']);
        }

        if(strlen(trim($request->get('content'))) < 40 ){
            return new PostResources(['error' => 'The lowest number of post content is 40 letters']);
        }

        /*if(strlen($request->get('content')) > 1500 ){
            return new UserResource(['error' => 'The largest  number of post title is 150 letters']);
        }*/
        $category = Category::find($request->get('category_id'));
        if($category == null){
            return new PostResources(['error' => 'please enter correct category_id']);
        }
        $user = $request->user();
        $post = new Post();

        $post->title = $request->get('title');
        $post->content = $request->get('content');
        if(intval($request->get('category_id')) != 0){
            $post->category_id = intval($request->get('category_id'));
        }
        $post->user_id = $user->id;
        $post->votes_up = 0;
        $post->votes_down = 0;
        $post->voters_up = null;
        $post->voters_down = null;
        $post->date_written = Carbon::now()->format('Y-m-d H:i:s');
        $post->featured_image_name = 'image name';
        if($request->hasFile('featured_image')){
            /*$image = $request->file('featured_image');
            $fileName = time() . $image->getClientOriginalName();
            Storage::disk('images')->putFileAs(
                'posts/',
                $image,
                $fileName
            );            
            $post->featured_image = url('/') . '/images/posts/' . $fileName;*/                      
            $image = $request->file('featured_image');
            $newName = time(). '_' . rand() . '.' . $image
            ->getClientOriginalExtension();
            $image->move(public_path('images/posts'), $newName);
            $post->featured_image_name = $newName;
            $post->featured_image = url('/') . '/images/posts/' . $newName;
        }

        $post->save();
        //return new PostResources($post);
        return new PostResources(['status' => 'post added']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::/*join('users','posts.user_id','users.id')
        ->join('categories','posts.category_id','categories.id')
        ->select('posts.id','posts.title',
        'posts.content','posts.date_written','posts.featured_image',
        'posts.votes_up','posts.votes_down','posts.user_id','users.name as autherName',
        'users.avater as autherAvater','posts.category_id','categories.title as categoryTitle')
        ->find($id);*/
        with(['comments', 'author','category'])
        ->where('id', $id)->get();
        return new PostResources($post);
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
        $user = $request->user();
        $post = Post::find($id);

        if($post == null){
            return new PostResources(['error' => 'post not found']);
        }

        if($post->user_id != $user->id){
            return new PostResources(['error' => 'you are not the author for this post!']);
        }

        if($request->has('title')){
            if(strlen(trim($request->get('title'))) < 20 ){
                return new PostResources(['error' => 'The lowest number of post title is 20 letters']);
            }
    
            if(strlen(trim($request->get('title'))) > 150 ){
                return new PostResources(['error' => 'The largest  number of post title is 150 letters']);
            }            
            $post->title = $request->get('title');
        }
        if($request->has('content')){
            if(strlen(trim($request->get('content'))) < 40 ){
                return new PostResources(['error' => 'The lowest number of post content is 40 letters']);
            }
            $post->content = $request->get('content');
        }
        if($request->has('category_id')){
            $category = Category::find($request->get('category_id'));
            if($category == null){
                return new PostResources(['error' => 'please enter correct category_id']);
            }
            if(intval($request->get('category_id')) != 0){
                $post->category_id = intval($request->get('category_id'));
            }
        }  
        if($request->has('featured_image')){
            if($request->hasFile('featured_image')){
                /*$image = $request->file('featured_image');
                $fileName = time() . $image->getClientOriginalName();
                Storage::disk('images')->putFileAs(
                    'posts/' . $fileName,
                    $image,
                    $fileName
                );            
                $post->featured_image = url('/') . '/images/posts/' . $fileName;*/                    

                $request->validate([
                    'featured_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
                ]);
                $image = $request->file('featured_image');
                $newName = time(). '_' . rand() . '.' . $image
                ->getClientOriginalExtension();
                $image->move(public_path('images/posts'), $newName);

                if( $post->featured_image_name != 'image name'){
                    $myfile_path = public_path().'/images/posts/' . $post->featured_image_name;                
                    File::delete($myfile_path);
                    $post->featured_image_name = $newName;
                } else{
                    $post->featured_image_name = $newName;
                }                
                
                $post->featured_image = url('/') . '/images/posts/' . $newName;
            }
        }        
        $post->save();
        //return new PostResources($post);
        return new PostResources(['status' => 'post updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $post = Post::find($id);
        if($post == null){
            return new PostResources(['error' => 'post not found']);
        }

        $user = $request->user();

        if($post->user_id != $user->id){
            return new PostResources(['error' => 'you are not the author for this post!']);
        }

        $imageName = $post->featured_image_name;
        $myfile_path = public_path().'/images/posts/' . $imageName;
        $post->delete();
        if( $imageName != 'image name'){
            File::delete($myfile_path);
        }        
        //return new PostResources($post);
        return new PostResources(['status' => 'post deleted']);
    }

    public function comments($id)
    {
        $post = Post::find($id);
        if($post == null){
            //return new AuthorPostsResource(['error' => 'user not found']);
            return new PostResources(['error' => 'post not found']);
        }
        $comments = $post->comments()
        ->join('users','comments.user_id','users.id')
        ->join('posts','comments.post_id','posts.id')
        ->select('comments.id',
        'comments.content','comments.date_written',
        'comments.user_id','users.name as autherName',
        'users.avater as autherAvater','comments.post_id')
        ->paginate(env('COMMENTS_PER_PAGE'));
        return new CommentsResources($comments);
    }
}
