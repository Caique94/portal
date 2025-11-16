<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/usuarios', [UserController::class, 'list']);
Route::post('/usuarios', [UserController::class, 'store']);
Route::patch('/usuarios/{id}/toggle', [UserController::class, 'toggle']);
