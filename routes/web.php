<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use App\Livewire\Pools\Browse;
use App\Livewire\Pools\Create;
use App\Livewire\Pools\Show;
use Illuminate\Support\Facades\Route;

// Route::view('/', 'static.home')->name('static.home');

Route::middleware('guest')->group(function (): void {
    Route::livewire('/', 'auth.login')->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/logout', LogoutController::class)->name('logout');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::group(['prefix' => 'pools', 'as' => 'pools.'], function (): void {
        Route::get('/', Browse::class)->name('index');
        Route::get('/create/{competition?}', Create::class)->name('create');
        Route::get('/{pool}', Show::class)->name('show');
    });
});
