<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'static.home')->name('static.home');

Route::middleware('guest')->group(function (): void {
    Route::livewire('/signin', 'auth.login')->name('login');
});
