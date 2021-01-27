<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransLine extends Model
{
    use HasFactory;

    protected $fillable = [
      'trans_header_id',
      'amount',
      'card_id'
    ];
}
