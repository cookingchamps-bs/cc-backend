<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
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

    public function getImageAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }
}
