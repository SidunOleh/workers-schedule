<?php

use App\Http\Controllers\Events\ClearController;
use App\Http\Controllers\Events\CopyController;
use App\Http\Controllers\Events\DeleteController as EventsDeleteController;
use App\Http\Controllers\Events\GetController;
use App\Http\Controllers\Events\StoreController as EventsStoreController;
use App\Http\Controllers\Events\UpdateController as EventsUpdateController;
use App\Http\Controllers\Users\ChangeUnavailableDaysController;
use App\Http\Controllers\Users\ClockInController;
use App\Http\Controllers\Users\ClockOutController;
use App\Http\Controllers\Users\DeleteController;
use App\Http\Controllers\Users\GetAllUnavailableDaysController;
use App\Http\Controllers\Users\GetClockInController;
use App\Http\Controllers\Users\GetUnavailableDaysController;
use App\Http\Controllers\Users\GetWorkersController;
use App\Http\Controllers\Users\StoreController;
use App\Http\Controllers\Users\UpdateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum',])->group(function () {
    Route::prefix('/users')->name('users.')->group(function () {
        Route::get('/workers', GetWorkersController::class)->name('get-workers');
        Route::post('/', StoreController::class)->name('store');
        Route::put('/{user}', UpdateController::class)->name('update');
        Route::delete('/{user}', DeleteController::class)->name('delete');
        Route::get('/unavailable-days', GetAllUnavailableDaysController::class)->name('get-unavailable-days');
        Route::get('/{user}/unavailable-days', GetUnavailableDaysController::class)->name('get-unavailable-days-for-user');
        Route::post('/{user}/unavailable-days', ChangeUnavailableDaysController::class)->name('change-unavailable-days');
        Route::get('/{user}/clock-in', GetClockInController::class)->name('get-clock-in');
        Route::post('/{user}/clock-in', ClockInController::class)->name('clock-in');
        Route::post('/{user}/clock-out', ClockOutController::class)->name('out');
    });

    Route::prefix('/events')->name('events.')->group(function () {
        Route::get('/', GetController::class)->name('get');
        Route::post('/', EventsStoreController::class)->name('store');
        Route::post('/clear', ClearController::class)->name('clear');
        Route::post('/copy', CopyController::class)->name('copy');
        Route::put('/{workerEvent}', EventsUpdateController::class)->name('update');
        Route::delete('/{workerEvent}', EventsDeleteController::class)->name('delete');
    });
});