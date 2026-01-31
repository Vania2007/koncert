<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title', 
        'image', 
        'description', 
        'start_time', 
        'end_time', 
        'location', 
        'city', 
        'hall_id'
    ];

    // 👇 Магия: автоматическое удаление зависимых данных
    protected static function booted(): void
    {
        static::deleting(function (Event $event) {
            foreach ($event->ticketTypes as $ticketType) {
                // 1. Сначала удаляем билеты, купленные на этот тип билета
                // Используем прямую команду к базе, чтобы не вызывать лишних событий
                Ticket::where('ticket_type_id', $ticketType->id)->delete();
                
                // 2. Затем удаляем сам тип билета
                $ticketType->delete();
            }
        });
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}