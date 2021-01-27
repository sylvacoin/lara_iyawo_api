<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    use HasFactory;

    protected $guard = [];
    protected $fillable = [
        'user_group',
        'user_group_menus',
        'is_default',
        'can_edit',
        'account_type'
    ];

    function Users()
    {
        return $this->hasMany('App\Models\User');
    }
}
