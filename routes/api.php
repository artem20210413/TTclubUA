<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Car\CarController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::post('/user/profile-picture', [MediaController::class, 'updateProfilePicture'])->middleware('auth:sanctum');
Route::delete('/user/profile-picture', [MediaController::class, 'deleteProfilePicture'])->middleware('auth:sanctum');
Route::get('/user/my-cars', [UserController::class, 'myCars'])->middleware('auth:sanctum');
Route::get('/user/all', [UserController::class, 'all'])->middleware('auth:sanctum');
Route::post('/user', [UserController::class, 'update'])->middleware(['auth:sanctum', 'role:admin']);
Route::get('/user', [UserController::class, 'user'])->middleware('auth:sanctum');
Route::post('/user/{id}', [UserController::class, 'updateById'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/user/{id}/change-password', [AuthController::class, 'changePasswordByUser'])->middleware(['auth:sanctum', 'role:admin']);
//Route::post('/user/profile-collection/add', [MediaController::class, 'addProfileCollection'])->middleware('auth:sanctum');
//Route::delete('/user/profile-collection/{id}', [MediaController::class, 'deleteProfilePicture'])->middleware('auth:sanctum');

Route::post('/car/create', [CarController::class, 'create'])->middleware(['auth:sanctum', 'role:admin']);
Route::post('/car/{id}', [CarController::class, 'update'])->middleware(['auth:sanctum']);
Route::get('/car', [CarController::class, 'all'])->middleware(['auth:sanctum']);
Route::get('/car/{id}', [CarController::class, 'find'])->middleware(['auth:sanctum']);
Route::post('/car/{id}/collections', [CarController::class, 'addCollections'])->middleware(['auth:sanctum']);
Route::delete('/car/{id}/collections/{mediaId}', [CarController::class, 'deleteCollections'])->middleware(['auth:sanctum']);

Route::get('/genes', [CarController::class, 'genes'])->middleware(['auth:sanctum']);
Route::get('/models', [CarController::class, 'models'])->middleware(['auth:sanctum']);

Route::get('/cities', [CityController::class, 'all'])->middleware(['auth:sanctum']);

Route::post('/import', [ImportController::class, 'importMain'])->middleware(['auth:sanctum', 'role:admin']);

//->middleware('auth:sanctum') Проверяет аутентификацию с использованием Laravel Sanctum, который предоставляет возможность защищать API с помощью токенов.
//->middleware('guest') Проверяет, что пользователь не аутентифицирован (т.е., “гость”).
//->middleware('verified') Проверяет, что пользователь подтвердил свой email.
//->middleware('role:admin') Проверяет, что пользователь обладает определенной ролью, используя Spatie Permissions.
//->middleware('permission:view-dashboard') Проверяет, что у пользователя есть конкретное разрешение для действия.
//Route::group(['middleware' => ['permission:edit articles|publish articles']], function () {}); // маршруты для пользователей, у которых есть либо "edit articles", либо "publish articles"

