<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model,SoftDeletes};
use Carbon\Carbon;

class Order extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];
    
    protected $hidden = ['deleted_at'];
    
    protected $casts = [
        'id' => 'string'
    ];
    
    public function getFormattedOrderDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('d F Y');
    }
    

    
    public function deliveryAddress(){
        return $this->belongsTo(Address::class, 'shipping_address_id', 'id');
    }
    
    
    public function User(){
        
         return $this->belongsTo(User::class,'user_id','id');
    }
    
    public function orderItem()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    /** Alias used by admin vendor views (`$order->items`). */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    
    
}
