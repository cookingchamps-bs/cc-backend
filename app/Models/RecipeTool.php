<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeTool extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tool_name',
        'tool_image',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
    ];
    
    protected $appends = ['tool_image_medium', 'tool_image_thumb'];
    
    public function getToolImageAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }
    
    public function getToolImageMediumAttribute()
    {
        return Helper::getImageVariants($this->tool_image)['medium'];
    }
    
    public function getToolImageThumbAttribute()
    {
        return Helper::getImageVariants($this->tool_image)['thumb'];
    }
}
