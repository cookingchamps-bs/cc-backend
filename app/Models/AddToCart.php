<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model,SoftDeletes};

class AddToCart extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];
    
    protected $casts = [
       'id'         => 'string',
       'product_id' => 'string',
       'quantity'   => 'string',
    ];
    
    protected $hidden = ['deleted_at'];
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
