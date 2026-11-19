<?php

namespace App\Models;

use App\Helpers\Helper;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory,Notifiable,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
     
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
        ];
    }
    
    protected $appends = ['is_purchase_subscription', 'image_medium', 'image_thumb'];
    
    public function getIsPurchaseSubscriptionAttribute()
    {
        return $this->userSubscription()->exists();
    }
       
    public function userSubscription()
    {
        return $this->hasMany(UserSubscription::class)
                    ->where([
                        'role' => 'parent',
                        'payment_status'=> 1,
                        'is_active' => 1
                    ])->whereNull('kid_id');
    }

    public function kids()
    {
        return $this->hasMany(Kid::class, 'parent_id', 'id');
    }

    public function stories()
    {
        return $this->hasMany(Story::class)->whereRole('parent')->with(['storyAddedByParent', 'storyAddedByChild']);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class)->whereRole('parent')->with(['recipeAddedByParent', 'recipeAddedByChild']);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    /** All subscription purchases made by this parent (including for kids). */
    public function subscriptionPurchases()
    {
        return $this->hasMany(UserSubscription::class, 'user_id', 'id');
    }

    /* ----- Trigger function ---- */
    protected static function booted(): void
    {
        static::deleted(function ($user) {
            $user->kids()->delete();
            
            // delete stories
            $storiesId = $user->stories->pluck('id');
            StoryImage::whereIn('story_id', $storiesId)->delete();
            Story::whereIn('id', $storiesId)->delete();

            // delete recipes
            $recipeIds = $user->recipes->pluck('id');
            RecipeIngredient::whereIn('recipe_id', $recipeIds)->delete();
            RecipeStep::whereIn('recipe_id', $recipeIds)->delete();
            Recipe::whereIn('id', $recipeIds)->delete();

        });
    }
// old
    public function getImageAttribute()
    {
        return ! empty($this->attributes['image'])
        ? Helper::normalizeImagePath($this->attributes['image'])
        : 'uploads/placeholder/default_user.png';

    }
    
// public function getImagePathAttribute()
// {
//     if (!empty($this->attributes['image'])) {
//         return 'public/'.$this->attributes['image'];
//     }

//     return 'public/uploads/placeholder/default_user.png';
// }    
    
    
    
    public function getImageMediumAttribute()
    {
        return Helper::getImageVariants($this->image)['medium'];
    }
    
    public function getImageThumbAttribute()
    {
        return Helper::getImageVariants($this->image)['thumb'];
    }
}
