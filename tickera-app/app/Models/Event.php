<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'location',
        'start_time',
        'end_time',
    ];
    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}
