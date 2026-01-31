<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\SeatLockController;

Route::get('/', [EventController::class, 'index'])->name('events.index');

// 1. Страница описания (Лендинг)
Route::get('/event/{event}', [EventController::class, 'show'])->name('events.show');

// 2. Страница выбора мест (Схема)
Route::get('/event/{event}/tickets', [EventController::class, 'selectSeats'])->name('events.tickets');

Route::post('/order', [OrderController::class, 'store'])->name('order.create');
Route::get('/order/success/{order}', [OrderController::class, 'success'])->name('order.success');
Route::get('/order/{order}/download', [TicketController::class, 'downloadOrder'])->name('order.download');
Route::post('/seats/toggle-lock', [SeatLockController::class, 'toggle'])->name('seats.toggle_lock');