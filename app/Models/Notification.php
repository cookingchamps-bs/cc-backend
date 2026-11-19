<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'kid_id' => 'string',
        'sender_id' => 'string',
        'receiver_id' => 'string',
        'status' => 'string',
    ];

    public function userDetails()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
