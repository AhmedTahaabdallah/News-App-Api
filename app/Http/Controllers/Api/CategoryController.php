<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use  \App\Http\Resources\PostsResources;
use \App\Http\Resources\CategoriesResources;
use \App\Http\Resources\CategoryResource;
use \App\Category;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new CategoriesResources(Category::paginate(env('CATEGORIES_PER_PAGE')));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

    public function posts($id)
    {
        $category = Category::find($id);
        if($category == null){
            //return new AuthorPostsResource(['error' => 'user not found']);
            return new CategoryResource(['error' => 'Category not found']);
        }
        $posts = $category->posts()
        ->join('users','posts.user_id','users.id')
        ->join('categories','posts.category_id','categories.id')
        ->select('posts.id','posts.title',
        'posts.content','posts.date_written','posts.featured_image',
        'posts.votes_up','posts.votes_down','posts.user_id','users.name as autherName',
        'users.avater as autherAvater','posts.category_id','categories.title as categoryTitle')
        ->paginate(env('POSTS_PER_PAGE'));
        return new PostsResources($posts);
    }
}
