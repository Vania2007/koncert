<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    protected $guarded = [];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}