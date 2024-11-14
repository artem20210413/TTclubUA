<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register'])->middleware(['auth:sanctum', 'role:admin']);

Route::post('user/{id}/change-password', [AuthController::class, 'changePasswordByUser'])->middleware(['auth:sanctum', 'role:admin']);

//->middleware('auth:sanctum') Проверяет аутентификацию с использованием Laravel Sanctum, который предоставляет возможность защищать API с помощью токенов.
//->middleware('guest') Проверяет, что пользователь не аутентифицирован (т.е., “гость”).
//->middleware('verified') Проверяет, что пользователь подтвердил свой email.
//->middleware('role:admin') Проверяет, что пользователь обладает определенной ролью, используя Spatie Permissions.
//->middleware('permission:view-dashboard') Проверяет, что у пользователя есть конкретное разрешение для действия.
//Route::group(['middleware' => ['permission:edit articles|publish articles']], function () {}); // маршруты для пользователей, у которых есть либо "edit articles", либо "publish articles"

