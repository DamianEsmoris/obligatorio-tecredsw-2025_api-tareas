<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiOauthValidation;

Route::get('/status', fn() => "OK");

Route::get('/task', [TaskController::class, 'GetAll']);
Route::get('/task/{d}', [TaskController::class, 'Get']);

Route::get('/category', [CategoryController::class, 'GetAll']);
Route::get('/category/{d}', [CategoryController::class, 'Get']);

Route::get('/comment', [CommentController::class, 'GetAll']);
Route::get('/comment/{d}', [CommentController::class, 'Get']);


Route::middleware(ApiOauthValidation::class)->group(function() {
    Route::post('/task', [TaskController::class, 'Create']);
    Route::put('/task/{d}', [TaskController::class, 'Modify']);
    Route::delete('/task/{d}', [TaskController::class, 'Delete']);

    Route::post('/category', [CategoryController::class, 'Create']);
    Route::put('/category/{d}', [CategoryController::class, 'Modify']);
    Route::delete('/category/{d}', [CategoryController::class, 'Delete']);

    Route::post('/comment', [CommentController::class, 'Create']);
    Route::put('/comment/{d}', [CommentController::class, 'Modify']);
    Route::delete('/comment/{d}', [CommentController::class, 'Delete']);
});
