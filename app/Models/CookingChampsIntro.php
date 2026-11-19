<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CookingChampsIntro extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image',
        'color_code',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function getImageAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }
}
