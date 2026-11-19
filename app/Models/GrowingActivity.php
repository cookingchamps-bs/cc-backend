<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrowingActivity extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['title', 'description', 'image', 'status'];
    
    protected $hidden = [
        'deleted_at',
    ];
    
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
