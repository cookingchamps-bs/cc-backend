<?php
namespace App\Models;
use Illuminate\Database\Eloquent\{Model,SoftDeletes};

class Address extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];
    
    protected $casts = [
       'id' => 'string',
       'parent_id' => 'string'
    ];
    
    protected $hidden = ['deleted_at'];

}
