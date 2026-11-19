<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FcmToken extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'child_id',
        'role',
        'device_id',
        'device_type',
        'fcm_token',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
    ];
}
