<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'kid_id',
        'role',
        'name',
        'banner_image',
        'recipe_tools',
        'status',
        'video_link'
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'kid_id' => 'string',
        'status' => 'string',
        'recipe_of_the_week' => 'string',
    ];
    
    protected $appends = ['banner_image_medium', 'banner_image_thumb'];
    
    public function getBannerImageAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }
    
    public function getBannerImageMediumAttribute()
    {
        return Helper::getImageVariants($this->banner_image)['medium'];
    }
    
    public function getBannerImageThumbAttribute()
    {
        return Helper::getImageVariants($this->banner_image)['thumb'];
    }

    public function recipeIngredient()
    {
        return $this->hasMany(RecipeIngredient::class, 'recipe_id', 'id');
    }

    public function recipeSteps()
    {
        return $this->hasMany(RecipeStep::class);
    }

    public function recipeAddedByParent()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function recipeAddedByChild()
    {
        return $this->belongsTo(Kid::class, 'kid_id', 'id')->select('id', 'parent_id', 'name', 'role', 'kid_image', 'day', 'month', 'year', 'grade');
    }
    
    public function favRecipe(){
        return $this->hasMany(AddToFavorite::class);
    }
    
    public function recipeCategories(){
        return $this->hasMany(RecipeCategory::class)->with('category');
    }
    
    public function recipeAgeGroup(){
        return $this->hasMany(RecipeAgeGroup::class)->with('ageGroup');
    }

    /* ----- Trigger function ---- */
    protected static function booted(): void
    {
        static::deleted(function ($val) {
            $val->recipeIngredient()->delete();
            $val->recipeSteps()->delete();
            $val->favRecipe()->delete();
            $val->recipeCategories()->delete();
            $val->recipeAgeGroup()->delete();
        });
    }
}
