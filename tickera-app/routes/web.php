<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TicketController;

Route::get('/', [EventController::class, 'index'])->name('events.index');
Route::get('/event/{id}', [EventController::class, 'show'])->name('events.show');
Route::post('/order', [OrderController::class, 'store'])->name('order.store');
Route::get('/order/{order}/download', [TicketController::class, 'downloadOrder'])->name('order.download');