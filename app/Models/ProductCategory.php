<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'image',
        'status'
    ];
    
    protected $casts = [
       'id' => 'string'
    ];
    
    protected $hidden = ['deleted_at'];
    
    protected $appends = ['image_medium', 'image_thumb'];
    
    public function getImageAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }
    
    public function getImageMediumAttribute()
    {
        return Helper::getImageVariants($this->image)['medium'];
    }
    
    public function getImageThumbAttribute()
    {
        return Helper::getImageVariants($this->image)['thumb'];
    }
}
