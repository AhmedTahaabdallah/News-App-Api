<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::get('users', function(){
    $user = \App\User::paginate();
    return new App\Http\Resources\UsersResource($user);
});*/

// start user
Route::get('authors', 'Api\UserController@index');
Route::get('author/{id}', 'Api\UserController@show');
Route::get('posts/author/{id}', 'Api\UserController@posts');
Route::get('comments/author/{id}', 'Api\UserController@comments');

Route::post('register', 'Api\UserController@store');
Route::post('login', 'Api\UserController@getToken');
// end user


// start Category and posts
Route::get('categories', 'Api\CategoryController@index');
Route::get('posts/categories/{id}', 'Api\CategoryController@posts');
Route::get('posts', 'Api\PostController@index');
Route::get('posts/{id}', 'Api\PostController@show');
Route::get('comments/posts/{id}', 'Api\PostController@comments');
// end Category and posts

Route::middleware('auth:api')->group(function(){
    Route::post('update-user/{id}', 'Api\UserController@update');
    Route::post('posts', 'Api\PostController@store');
    Route::post('posts/{id}', 'Api\PostController@update');
    //Route::delete('posts/{id}', 'Api\PostController@destroy');
    Route::post('delete-post/{id}', 'Api\PostController@destroy');

    Route::post('comment', 'Api\CommentsController@store');
});
