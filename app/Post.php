<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable =[
        'title',
        'content',
        'date_written',
        'featured_image',
        'votes_up',
        'votes_down',
        'voters_up',
        'voters_down',
        'user_id',
        'category_id'
    ];
    //protected $primaryKey = 'id';
    protected $hidden = [
        'updated_at','created_at','featured_image_name'
    ];
    public function author() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

}
