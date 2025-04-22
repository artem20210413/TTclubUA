<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/registration', [\App\Http\Controllers\Web\RegistrationsController::class, 'index']);

