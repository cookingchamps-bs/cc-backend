<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeIngredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recipe_id',
        'name',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'recipe_id' => 'string',
    ];
}
