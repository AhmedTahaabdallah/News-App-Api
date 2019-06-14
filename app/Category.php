<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable =[
        'title'
    ];

    protected $hidden = [
        'updated_at','created_at'
    ];

    public function posts(){
        return $this->hasMany(Post::class);
    }
}
