<?php

use App\Http\Controllers\Events\ClearController;
use App\Http\Controllers\Events\CopyController;
use App\Http\Controllers\Events\DeleteController as EventsDeleteController;
use App\Http\Controllers\Events\GetController;
use App\Http\Controllers\Events\StoreController as EventsStoreController;
use App\Http\Controllers\Events\UpdateController as EventsUpdateController;
use App\Http\Controllers\Workers\ChangeUnavailableDaysController;
use App\Http\Controllers\Workers\DeleteController;
use App\Http\Controllers\Workers\GetAllController;
use App\Http\Controllers\Workers\GetUnavailableDaysController;
use App\Http\Controllers\Workers\StoreController;
use App\Http\Controllers\Workers\UpdateController;
use Illuminate\Support\Facades\Route;

Route::prefix('/workers')->name('workers.')->group(function () {
    Route::get('/', GetAllController::class)->name('get-all');
    Route::post('/', StoreController::class)->name('store');
    Route::put('/{worker}', UpdateController::class)->name('update');
    Route::delete('/{worker}', DeleteController::class)->name('delete');
    Route::get('/{worker}/unavailable-days', GetUnavailableDaysController::class)->name('get-unavailable-days');
    Route::post('/{worker}/unavailable-days', ChangeUnavailableDaysController::class)->name('change-unavailable-days');
});

Route::prefix('/events')->name('events.')->group(function () {
    Route::get('/', GetController::class)->name('get');
    Route::post('/', EventsStoreController::class)->name('store');
    Route::post('/clear', ClearController::class)->name('clear');
    Route::post('/copy', CopyController::class)->name('copy');
    Route::put('/{workerEvent}', EventsUpdateController::class)->name('update');
    Route::delete('/{workerEvent}', EventsDeleteController::class)->name('delete');
});