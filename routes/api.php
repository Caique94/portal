<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrdemServicoController;

Route::get('/usuarios', [UserController::class, 'list']);
Route::post('/usuarios', [UserController::class, 'store']);
Route::patch('/usuarios/{id}/toggle', [UserController::class, 'toggle']);

// Routes para Ordem de Serviço - Envio de Emails
Route::post('/ordem-servico/enviar-consultor', [OrdemServicoController::class, 'enviarParaConsultor']);
Route::post('/ordem-servico/enviar-cliente', [OrdemServicoController::class, 'enviarParaCliente']);
Route::post('/ordem-servico/enviar-ambos', [OrdemServicoController::class, 'enviarParaAmbos']);
