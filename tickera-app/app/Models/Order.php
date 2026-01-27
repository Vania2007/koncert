<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_email',
        'customer_name',
        'total_amount',
        'status',
        'payment_id',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}