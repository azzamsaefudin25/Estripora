<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/users', function () {
    return view('users');
})->name('users');

Route::get('/keranjang', function () {
    return view('keranjang');
})->name('keranjang');

Route::get('/cetak', function () {
    return view('cetak');
})->name('cetak');

Route::get('/settings', function () {
    return view('settings');
})->name('settings');
