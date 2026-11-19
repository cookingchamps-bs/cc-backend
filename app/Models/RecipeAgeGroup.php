<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeAgeGroup extends Model
{
    protected $fillable = [
        'recipe_id',
        'age_group_id',
    ];
    
    protected $casts = [
        'id' => 'string',
        'recipe_id' => 'string',
        'age_group_id' => 'string'
    ];
    
    public function ageGroup(){
        return $this->belongsTo(AgeGroup::class, 'age_group_id', 'id');
    }
}
