<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoryShare extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'story_id',
        'story_share_by',
        'role',
        'parent_id',
        'kid_id',
        'story_share_to',
        'story_share_to_role',
    ];

    protected $hidden = [
        'deleted_at',
        'parent_id',
        'kid_id',
    ];

    protected $casts = [
        'id' => 'string',
        'story_id' => 'string',
        'story_share_by' => 'string',
        'parent_id' => 'string',
        'kid_id' => 'string',
        'story_share_to' => 'string',
    ];
}
