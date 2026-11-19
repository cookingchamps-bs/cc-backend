<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model,SoftDeletes};

class Subscription extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];
    
    protected $casts = [
        'id' => 'string'
    ];
    
    protected $hidden = ['deleted_at','description'];
    
    public function subscriptionDetails(){
        return $this->hasMany(SubscriptionDetail::class);
    }
    
    public function userPurchaseSubscription(){
        return $this->hasMany(UserSubscription::class);
    }
}
