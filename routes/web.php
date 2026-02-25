<?php

use App\Http\Controllers\Auth\LogInController;
use App\Http\Controllers\Auth\LogOutController;
use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LogInController::class)
    ->name('login');
Route::post('/logout', LogOutController::class)
    ->name('logout');

Route::get('/{any?}', IndexController::class);
