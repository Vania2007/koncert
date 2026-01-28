<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
    ];
    protected $fillable = [
        'order_id',
        'ticket_type_id',
        'unique_code',
        'is_checked_in',
        'checked_in_at',
    ];

    public function event()
    {
        return $this->ticketType->event();
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}
