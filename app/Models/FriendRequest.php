<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FriendRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'request_sent_by',
        'request_sent_to',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'sender_id' => 'string',
        'receiver_id' => 'string',
        'status' => 'string',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    // friend request
    public function parentSenderDetail()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function parentReceiverDetail()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    public function childSenderDetail()
    {
        return $this->belongsTo(Kid::class, 'sender_id', 'id');
    }

    public function childReceiverDetail()
    {
        return $this->belongsTo(Kid::class, 'receiver_id', 'id');
    }
}
