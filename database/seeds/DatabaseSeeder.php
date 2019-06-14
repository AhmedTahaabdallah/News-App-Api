<?php

use Illuminate\Database\Seeder;
use \App\User;
use \App\Category;
use \App\Post;
use \App\Comment;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        factory(User::class, 50)->create();
        factory(Category::class, 15)->create();
        factory(Post::class, 500)->create();
        factory(Comment::class, 1500)->create();
    }
}
