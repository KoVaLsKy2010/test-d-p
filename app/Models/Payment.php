<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{ 
    use HasFactory;

    protected $fillable = [
        'id',
        'status',
        'amount',
        'user_id',
        'payment_id',
        'merchant_id'
    ];
}
