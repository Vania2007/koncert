<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    // 👇 Мы убираем старый $fillable и оставляем только $guarded = [], 
    // чтобы разрешить запись ЛЮБЫХ полей, которые есть в базе.
    protected $guarded = [];

    protected $casts = [
        'is_checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
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