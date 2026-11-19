<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishList extends Model
{
    protected $guarded = [];
    
    protected $casts = [
       'id' => 'string',
       'product_id' => 'string'
    ];
    
    protected $hidden = ['deleted_at'];
    
    public function products(){
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
