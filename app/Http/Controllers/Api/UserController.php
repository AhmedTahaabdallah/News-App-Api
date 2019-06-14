<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \App\User;
use Storage;
use File;
use \App\Http\Resources\UsersResource;
use \App\Http\Resources\UserResource;
use \App\Http\Resources\AuthorPostsResource;
use \App\Http\Resources\AuthorCommentsResource;
use App\Http\Resources\TokenResource;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::paginate(env('AUTHORS_PER_PAGE'));
        return new UsersResource($user);
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
            'name' => 'required',
            'email' => 'required',
            'password' => 'required'
        ]);
        $user = User::where('email', trim($request->get('email')))->first();
        if($user != null){
            return new UserResource(['error' => 'user exists']);
        }

        if(strlen(trim($request->get('name'))) < 5 ){
            return new UserResource(['error' => 'The lowest number of user name is 5 letters']);
        }

        if(strlen(trim($request->get('name'))) > 12 ){
            return new UserResource(['error' => 'The largest  number of user name is 12 letters']);
        }
        $user = new User();
        $user->name = trim($request->get('name'));
        $user->email = trim($request->get('email'));
        $user->password = Hash::make($request->get('password'));
        $user->avater_name = 'image names';
        $user->save();
        //return new UserResource($user);
        return new UserResource(['status' => 'user added']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return new UserResource(User::find($id));
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
        $user = User::find($id);
        if($user == null){
            return new UserResource(['error' => 'user not found']);
        }
        if($request->has('name')){
            if(strlen(trim($request->get('name'))) < 5 ){
                return new UserResource(['error' => 'The lowest number of user name is 5 letters']);
            }
    
            if(strlen(trim($request->get('name'))) > 12 ){
                return new UserResource(['error' => 'The largest  number of user name is 12 letters']);
            }
            $user->name = trim($request->get('name'));
        }
        if($request->hasFile('avater')){
            /*$image = $request->file('avater');
            $fileName = time() . $image->getClientOriginalName();
            Storage::disk('images')->putFileAs(
                'users/' . $fileName,
                $image,
                $fileName
            );            
            $user->avater = url('/') . '/images/users/' . $fileName;*/                      
            
            $request->validate([
                'avater' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);
            $image = $request->file('avater');
            $newName = time(). '_' . rand() . '.' . $image
            ->getClientOriginalExtension();
            $image->move(public_path('images/users'), $newName);
            
            if($user->avater_name != 'image names'){
                $myfile_path = public_path().'/images/users/' . $user->avater_name;                
                File::delete($myfile_path);             
                $user->avater_name = $newName;
            } else{
                $user->avater_name = $newName;
            }
            
            $user->avater = url('/') . '/images/users/' . $newName;
        }
        $user->save();
        //return new UserResource($user);
        return new UserResource(['status' => 'user updated']);
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

    public function posts($id)
    {
        $user = User::find($id);
        if($user == null){
            //return new AuthorPostsResource(['error' => 'user not found']);
            return new UserResource(['error' => 'user not found']);
        }
        $posts = $user->posts()
        ->join('users','posts.user_id','users.id')
        ->join('categories','posts.category_id','categories.id')
        ->select('posts.id','posts.title',
        'posts.content','posts.date_written','posts.featured_image',
        'posts.votes_up','posts.votes_down','posts.user_id','users.name as autherName',
        'users.avater as autherAvater','posts.category_id','categories.title as categoryTitle')
        ->paginate(env('POSTS_PER_PAGE'));        
        return new AuthorPostsResource($posts);;
    }

    public function comments($id)
    {
        $user = User::find($id);
        if($user == null){
            //return new AuthorPostsResource(['error' => 'user not found']);
            return new UserResource(['error' => 'user not found']);
        }
        $comments = $user->comments()
        ->join('users','comments.user_id','users.id')
        ->join('posts','comments.post_id','posts.id')
        ->select('comments.id',
        'comments.content','comments.date_written',
        'comments.user_id','users.name as autherName',
        'users.avater as autherAvater','comments.post_id')
        ->paginate(env('COMMENTS_PER_PAGE'));
        return new AuthorCommentsResource($comments);;
    }

    public function getToken(Request $request){
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);
        /*$credentials = $request->only('email','password');
        if(Auth::attempt($credentials)){
            $user = User::where('email', $request->get('email'))->first();
            return new TokenResource([ 'token' => $user->api_token]);
        }
        return new TokenResource([ 'error' =>'user not Found']);*/

        $user = User::where('email', $request->get('email'))->first();
        if($user != null){
            $credentials = $request->only('email','password');
            if(Auth::attempt($credentials)){
                return new TokenResource([ 'token' => $user->api_token]);
            }
            return new TokenResource([ 'error' =>'wrong password']);
        }
        return new TokenResource([ 'error' =>'user not Found']);
    }
}
