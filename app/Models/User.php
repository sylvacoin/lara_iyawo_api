<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'phone',
        'balance',
        'w_balance',
        'email',
        'password',
        'user_group_id',
        'customer_no',
        'handler_id',
        'has_alert',
        'is_flagged',
    ];

    function customers()
    {
        return $this->hasMany('User', 'handler_id');
    }

    function customer_cards()
    {
        return $this->hasMany('App\Models\Card', 'customer_id');
    }

    function userGroup()
    {
        return $this->belongsTo('App\Models\UserGroup');
    }

    function userDetail(){
        return $this->hasOne('App\Models\UserDetail');
    }

    function transactions(){
        return $this->hasMany('App\Models\TransHeader');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

}
