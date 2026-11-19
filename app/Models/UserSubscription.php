<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSubscription extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];
    
    protected $casts = ['id'=>'string'];
    
    protected $hidden = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kid()
    {
        return $this->belongsTo(Kid::class, 'kid_id');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
