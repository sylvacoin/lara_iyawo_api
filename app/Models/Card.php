<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected  $fillable = ['card_name','card_no', 'customer_id', 'start_amount', 'card_balance', 'card_w_balance','card_count'];

    function customer()
    {
        return $this->belongsTo('App\Models\User');
    }
    public function transactions()
    {
        return $this->hasMany('App\Models\TransHeader');
    }

}
