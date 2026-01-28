<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'static.home')->name('home-page');
