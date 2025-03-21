<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    protected $dates = ['deleted_at'];
    
    protected $fillable = [
        'role_id',
        'first_name',
	    'last_name',
        'name',
        'email',
        'otp',
        'phone',
        'image',
        'station_name',
        'station_image',
        'has_station',
        'station_id',
        'api_token',
        'fcm_token',
        'password',
        'access_token',
        'is_push_notification',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role() {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

}
