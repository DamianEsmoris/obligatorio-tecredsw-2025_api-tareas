<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/status', fn() => "OK");

Route::post('/task', [TaskController::class, 'Create']);
Route::get('/task', [TaskController::class, 'GetAll']);
Route::get('/task/{d}', [TaskController::class, 'Get']);
Route::put('/task/{d}', [TaskController::class, 'Modify']);
Route::delete('/task/{d}', [TaskController::class, 'Delete']);
