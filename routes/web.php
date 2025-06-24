<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', fn () =>
    response("Unauthenticated", 401)
)->name('login');

