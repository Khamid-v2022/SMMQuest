<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use  App\Models\LoginHistory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'avatar',
        'first_name',
        'last_name',
        'email',
        'verify_code',
        'verified',
        'email_verified_at',
        'password',
        'organization',
        'phone',
        'address',
        'state',
        'zip_code',
        'country',
        'timezone',
        'last_auth_at',
        'is_delete',
        'deleted_at'        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function histories(){
        return $this->hasMany(LoginHistory::class);
    }

    public function providers() {
        return $this->hasMany('App\Models\UserProvider');
    }

    public function lists() {
        return $this->hasMany('App\Models\UserList');
    }
}
