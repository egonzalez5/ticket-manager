<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::middleware('auth')->apiResource('tickets', TicketController::class);