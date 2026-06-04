<?php

declare(strict_types=1);

use App\Models\League;
use Illuminate\Support\Facades\Route;

Route::view('/', 'static.home')->name('static.home');

Route::middleware('guest')->group(function (): void {
    Route::livewire('/signin', 'auth.login')->name('login');
});

Route::get('/seed', function () {
    $league = League::query()->with('sessions.stages')->first();

    dd($league->sessions->first());
});
