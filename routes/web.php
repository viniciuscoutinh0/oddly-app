<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use App\Livewire\Pools\Create;
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
    Route::get('/pools/create', Create::class)->name('pools.create');
    // Placeholder: replaced by Show component in Task 5
    Route::get('/pools/{pool:slug}', Create::class)->name('pools.show');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('static.home');
})->middleware('auth')->name('logout');
