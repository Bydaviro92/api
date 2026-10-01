<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/users', [UserController::class, 'get']);

Route::post('/users/create', [UserController::class, 'create']);

Route::post('/users/login', [UserController::class, 'login']);

Route::put('/users/update_username', [UserController::class, 'update_username']);

Route::put('/users/update_email', [UserController::class, 'update_email']);

Route::put('/users/update_password', [UserController::class, 'update_password']);

Route::delete('/users/delete', [UserController::class, 'delete']);