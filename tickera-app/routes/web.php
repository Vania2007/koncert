<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TicketController;

Route::get('/', [EventController::class, 'index'])->name('events.index');

// 👇 БЫЛО {id}, СТАЛО {event} — это включит магию Laravel
Route::get('/event/{event}', [EventController::class, 'show'])->name('events.show');

Route::post('/order', [OrderController::class, 'store'])->name('order.create');
Route::get('/order/success/{order}', [OrderController::class, 'success'])->name('order.success');
Route::get('/order/{order}/download', [TicketController::class, 'downloadOrder'])->name('order.download');