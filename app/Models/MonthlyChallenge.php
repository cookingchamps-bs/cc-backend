<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlyChallenge extends Model
{
     use SoftDeletes;
     
     protected $fillable = ['title','description','banner_image', 'status'];
     
     protected $hidden = [
        'deleted_at',
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
    
    // public function getDescriptionAttribute($val)
    // {
    //     return strip_tags(html_entity_decode($val, ENT_QUOTES, 'UTF-8'));
    // }
        // return strip_tags($val);
}
