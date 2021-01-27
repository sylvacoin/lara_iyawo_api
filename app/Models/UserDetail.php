<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;
    protected $guard = [];
    protected $fillable = [
        'user_id',
        'nok_name',
        'nok_phone',
        'shortee_name',
        'shortee_phone',
        'address',
        'account_no',
        'bank_name',
        'bvn'
    ];

    function User()
    {
        return $this->BelongsTo('App\Models\User');
    }
}
