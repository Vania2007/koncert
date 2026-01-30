<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // 👇 Разрешаем запись всех полей (включая customer_email)
    protected $guarded = [];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}