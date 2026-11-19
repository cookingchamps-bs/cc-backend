<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'kid_id',
        'role',
        'image',
        'title',
        'description',
        'status',
        'video_link'
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'status' => 'string',
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

    public function storyImages()
    {
        return $this->hasMany(StoryImage::class);
    }

    public function storyAddedByParent()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function storyAddedByChild()
    {
        return $this->belongsTo(Kid::class, 'kid_id', 'id')->select('id', 'parent_id', 'name', 'role', 'kid_image', 'day', 'month', 'year', 'grade');
    }

    protected static function booted(): void
    {
        static::deleted(function ($val) {
            $val->storyImages()->delete();
        });
    }
}
