<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    
    protected $fillable =[
        'content',
        'date_written',
        'user_id',
        'post_id'
    ];
    protected $hidden = [
        'updated_at','created_at'
    ];
}
