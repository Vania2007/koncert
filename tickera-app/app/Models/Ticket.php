<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'order_id',
        'ticket_type_id',
        'unique_code',
        'is_checked_in',
        'checked_in_at',
    ];

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}