<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'trans_by',
        'amount',
        'trans_type',
        'no_days',
        'trans_status',
        'card_id'
    ];

    public function card()
    {
        return $this->belongsTo('App\Models\Card');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function agent()
    {
        return $this->hasOne('App\Models\User','id', 'trans_by');
    }

}
