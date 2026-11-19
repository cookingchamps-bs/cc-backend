<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class FollowRequest extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'follow_req_sent_by',
        'follow_req_sent_to',
    ];
    
    protected $casts = [
        'id' => 'string',
        'sender_id' => 'string',
        'receiver_id' => 'string',
    ];

    protected $hidden = [
        'deleted_at',
    ];
    
    public function parentSenderDetails(){
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
    
    public function childSenderDetails(){
        return $this->belongsTo(Kid::class, 'sender_id', 'id');
    }
    
    public function parentReceiverDetails(){
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }
    
    public function childReceiverDetails(){
        return $this->belongsTo(Kid::class, 'receiver_id', 'id');
    }
}
