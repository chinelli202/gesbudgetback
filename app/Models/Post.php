<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'post_title', 'slug', 'category', 'author', 'published', 'draft'
    ];
}
