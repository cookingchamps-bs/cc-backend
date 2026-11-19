<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeCategory extends Model
{
    
    protected $fillable = [
        'recipe_id',
        'cat_id',
    ];
    
    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'recipe_id' => 'string',
        'cat_id' => 'string'
    ];
    
    public function category(){
        return $this->belongsTo(Category::class, 'cat_id', 'id');
    }
}
