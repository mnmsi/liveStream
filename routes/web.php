<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::controller(RegisterController::class)->group(function () {
    Route::get('register', 'showRegistrationForm')->name('register.show');
    Route::post('register', 'register')->name('register');
});

Route::middleware('checkUserRegistration')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::get('/', 'login');
        Route::get('login', 'login')->name('login');
        Route::post('login-check', 'checkAuth')->name('login.check');
        Route::get('logout', 'logoutUser')->name('logout');
    });

    Route::middleware('auth')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        Route::controller(ConfigController::class)->prefix('config')->as('config.')->group(function () {
            Route::get('list', 'list')->name('list');
            Route::get('create', 'create')->name('create');
            Route::post('store', 'store')->name('store');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::post('update/{id}', 'update')->name('update');
            Route::get('destroy/{id}', 'destroy')->name('destroy');
        });
    });
});


