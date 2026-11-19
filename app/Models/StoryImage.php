<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoryImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'story_id',
        'images',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'story_id' => 'string',
    ];
    
    protected $appends = ['images_medium', 'images_thumb'];
    
    public function getImagesAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }
    
    public function getImagesMediumAttribute()
    {
        return Helper::getImageVariants($this->images)['medium'];
    }
    
    public function getImagesThumbAttribute()
    {
        return Helper::getImageVariants($this->images)['thumb'];
    }
}
