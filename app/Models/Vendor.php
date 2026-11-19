<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'username',
        'password',
        'name',
        'mobile_no',
        'company_name',
        'remember_token',
    ];

    protected $casts = [
       'id' => 'string'
    ];

    protected $hidden = ['deleted_at', 'password', 'remember_token'];

    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }
}
