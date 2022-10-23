<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
    ];

    public function getUpdatedAtColumn() {
        return null;
    }
}
