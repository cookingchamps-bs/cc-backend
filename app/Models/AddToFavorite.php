<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddToFavorite extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recipe_id',
        'user_id',
        'kid_id',
    ];

    protected $casts = [
        'id' => 'string',
        'recipe_id' => 'string',
        'user_id' => 'string',
        'kid_id' => 'string',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class)->with('recipeIngredient', 'recipeSteps');
    }
}
