<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'image',
        'password',
        'remember_token',
        'tax_amount',
        'platform_fee',
        'shipping_charges',
        'openai_api_key',
        'stripe_key',
        'stripe_secret',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'openai_api_key',
        'stripe_key',
        'stripe_secret',
    ];

    protected function casts(): array
    {
        return [
            'openai_api_key' => 'encrypted',
            'stripe_key' => 'encrypted',
            'stripe_secret' => 'encrypted',
        ];
    }

    public function getImageAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }
}
