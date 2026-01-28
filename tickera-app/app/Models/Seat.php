<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $guarded = [];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
    
    // Получить полное название места (Ряд 5, Место 10)
    public function getLabelAttribute()
    {
        $parts = [];
        if ($this->section) $parts[] = $this->section;
        if ($this->row) $parts[] = "Ряд {$this->row}";
        $parts[] = "Место {$this->number}";
        
        return implode(', ', $parts);
    }
}