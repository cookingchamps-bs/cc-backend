<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\SchoolResetPasswordNotification;


class School extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'school_name',
        'school_logo',
        'contact_person_name',
        'email',
        'password',
        'status',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    
    public function videos(){
         return $this->hasMany(SchoolVideo::class, 'school_id');
    }

    public function getSchoolLogoAttribute($value)
    {
        return Helper::normalizeImagePath($value);
    }

    /**
     * Whether the school may sign in (inactive / deactivated accounts cannot).
     */
    public function isAccountActive(): bool
    {
        return $this->status === null || (int) $this->status === 1;
    }
    
    
    
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new SchoolResetPasswordNotification($token));
    }
    
    
}
