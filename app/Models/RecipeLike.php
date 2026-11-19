<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class RecipeLike extends Model
{
    
    protected $table = 'recipe_like';
    protected $fillable = ['user_id', 'recipe_id','type','status'];
    
    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'recipe_id' => 'string'
    ];
}
