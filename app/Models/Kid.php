<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Kid extends Authenticatable
{
    use HasApiTokens,SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'role',
        'kid_image',
        'day',
        'month',
        'year',
        'grade',
        'username',
        'password',
    ];

    protected $casts = [
        'id' => 'string',
        'parent_id' => 'string',
        'day' => 'string',
        'year' => 'string',
    ];

    protected $hidden = [
        'deleted_at',
        'kid_image',
        'password',
    ];

    // Append image
    protected $appends = ['image','image_medium','image_thumb','is_purchase_subscription'];
    
    // set accessor for kid image
    public function getImageAttribute()
    {
        // return $this->attributes['kid_image'];

        return ! empty($this->attributes['kid_image'])
        ? Helper::normalizeImagePath($this->attributes['kid_image'])
        : 'uploads/placeholder/default_user.png';
    }
    
    public function getImageMediumAttribute()
    {
        return Helper::getImageVariants($this->image)['medium'];
    }
    
    public function getImageThumbAttribute()
    {
        return Helper::getImageVariants($this->image)['thumb'];
    }

    
    public function getIsPurchaseSubscriptionAttribute()
    {
        return $this->userSubscription()->exists();
    }
    
    public function userSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where([
                // 'role' => 'kid',
                'payment_status'=> 1,
                'is_active' => 1
            ]);
    }

    // parent
    public function parent()
    {
        return $this->belongsTo(User::class)->select('id', 'first_name', 'last_name');
    }

    // stories
    public function stories()
    {
        return $this->hasMany(Story::class)->whereRole('kid')->with(['storyAddedByParent', 'storyAddedByChild']);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class)->whereRole('kid')->with(['recipeAddedByParent', 'recipeAddedByChild']);
    }
    
    public function subscription(){
        return $this->hasOne(UserSubscription::class);
    }

    protected static function booted(): void
    {
        static::deleted(function ($val) {
            // $val->stories()->delete();
            // $val->recipes()->delete();
            
            $storiesId = $val->stories->pluck('id');
            StoryImage::whereIn('story_id', $storiesId)->delete();
            Story::whereIn('id', $storiesId)->delete();

            // Delete recipes
            $recipeIds = $val->recipes->pluck('id');
            RecipeIngredient::whereIn('recipe_id', $recipeIds)->delete();
            RecipeStep::whereIn('recipe_id', $recipeIds)->delete();
            Recipe::whereIn('id', $recipeIds)->delete();
        });

    }
}
