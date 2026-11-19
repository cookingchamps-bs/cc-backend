<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];
    
    protected $casts = [
       'id' => 'string'
    ];
    
    protected $hidden = ['deleted_at'];
    
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function product()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'id');
    }
    
    
    protected $appends = ['is_favourite','is_cart_added','cart_quantity','image_medium','image_thumb'];
    
    public function productCategory(){
        return $this->belongsTo(ProductCategory::class, 'product_cat_id', 'id');
    }
    
    public function productImages(){
        return $this->hasMany(ProductImage::class);
    }
    
    public function wishlist(){
        return $this->hasMany(WishList::class);
    }
    
    public function cart(){
        return $this->hasMany(AddToCart::class);
    }
    
    protected static function booted(): void
    {
        static::deleted(function($val) {
            $val->productImages()->delete();
        });
    }
    
    public function getIsCartAddedAttribute()
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        if ($this->relationLoaded('cart')) {
            return $this->cart->isNotEmpty();
        }
        
        return $this->cart()
           ->where(function($query) use($user) {
               if($user->role === 'parent') {
                   $query->where('parent_id', $user->id)
                         ->whereNull('kid_id')
                         ->where('role', 'parent');
               }elseif ($user->role === 'kid') {
                   $query->where('kid_id', $user->id)
                          ->where('parent_id', $user->parent_id)
                          ->where('role', 'kid');
               }
           })
           ->exists();
    }

    public function getCartQuantityAttribute()
    {
        $user = auth()->user();

        if (!$user) {
            return 0;
        }

        if ($this->relationLoaded('cart')) {
            return (int) ($this->cart->first()->quantity ?? 0);
        }

        $cartItem = $this->cart()
           ->where(function($query) use($user) {
               if($user->role === 'parent') {
                   $query->where('parent_id', $user->id)
                         ->whereNull('kid_id')
                         ->where('role', 'parent');
               }elseif ($user->role === 'kid') {
                   $query->where('kid_id', $user->id)
                          ->where('parent_id', $user->parent_id)
                          ->where('role', 'kid');
               }
           })
           ->first();

        return (int) ($cartItem->quantity ?? 0);
    }
    
    public function getIsFavouriteAttribute()
    {
        $user = auth()->user();
    
        if (!$user) {
            return false;
        }
        
        if ($this->relationLoaded('wishlist')) {
            return $this->wishlist->isNotEmpty();
        }
    
        return $this->wishlist()
            ->where(function ($query) use ($user) {
                if ($user->role === 'parent') {
                    $query->where('parent_id', $user->id)
                          ->whereNull('kid_id')
                          ->where('role', 'parent');
                } elseif ($user->role === 'kid') {
                    $query->where('kid_id', $user->id)
                          ->where('parent_id', $user->parent_id)
                          ->where('role', 'kid');
                }
            })
            ->exists();
    }
    
    public function getImageMediumAttribute()
    {
        return Helper::getImageVariants($this->image)['medium'];
    }
    
    public function getImageThumbAttribute()
    {
        return Helper::getImageVariants($this->image)['thumb'];
    }
    
    public function getImageAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }
}
