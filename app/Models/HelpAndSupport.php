<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpAndSupport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
}
