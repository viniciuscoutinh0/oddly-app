<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'static.home')->name('static.home');

Route::middleware('guest')->group(function (): void {
    Route::livewire('/signin', 'auth.login')->name('login');
    Route::get('/signup', Register::class)->name('register');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('static.home');
})->middleware('auth')->name('logout');
