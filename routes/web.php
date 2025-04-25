<?php

use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    dd('welcome');
//    return view('welcome');
//});

Route::get('/', [\App\Http\Controllers\Web\RegistrationsController::class, 'index'])->middleware('only.ua')->name('web.home');
Route::post('/registration', [\App\Http\Controllers\Web\RegistrationsController::class, 'registration'])->name('web.post.registration');

