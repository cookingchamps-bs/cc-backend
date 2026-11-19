<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    public function getImageAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }
}
