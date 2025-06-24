<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

Route::get('/status', fn() => "OK");

Route::post('/task', [TaskController::class, 'Create']);
Route::get('/task', [TaskController::class, 'GetAll']);
Route::get('/task/{d}', [TaskController::class, 'Get']);

Route::post('/category', [CategoryController::class, 'Create']);
Route::get('/category', [CategoryController::class, 'GetAll']);
Route::get('/category/{d}', [CategoryController::class, 'Get']);

Route::post('/comment', [CommentController::class, 'Create']);
Route::get('/comment', [CommentController::class, 'GetAll']);
Route::get('/comment/{d}', [CommentController::class, 'Get']);


Route::middleware('auth:api_oauth')->group(function() {
    Route::put('/task/{d}', [TaskController::class, 'Modify']);
    Route::delete('/task/{d}', [TaskController::class, 'Delete']);

    Route::put('/category/{d}', [CategoryController::class, 'Modify']);
    Route::delete('/category/{d}', [CategoryController::class, 'Delete']);

    Route::put('/comment/{d}', [CommentController::class, 'Modify']);
    Route::delete('/comment/{d}', [CommentController::class, 'Delete']);
});
