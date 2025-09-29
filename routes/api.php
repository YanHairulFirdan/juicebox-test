<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:10,1');

        Route::prefix('users')->name('user.')->group(function () {
            Route::get('/', [\App\Http\Controllers\UserController::class, 'index'])->name('index');
            Route::get('/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('show');
        });

        Route::prefix('posts')->name('posts.')->controller(\App\Http\Controllers\PostController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{post}', 'show')->name('show');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::prefix('posts')->name('posts.')->controller(\App\Http\Controllers\PostController::class)->group(function () {
            Route::post('/', 'store')->name('store');
            Route::patch('/{post}', 'update')->name('update');
            Route::delete('/{post}', 'destroy')->name('destroy');
        });
    });
});
