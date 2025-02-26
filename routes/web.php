<?php

use App\Livewire\Cetak;
use App\Livewire\Users;
use App\Livewire\Settings;
use App\Livewire\Dashboard;
use App\Livewire\Keranjang;
use App\Livewire\DetailTempat;
use App\Livewire\Lapor;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\PenyewaanPerJam;
use App\Livewire\PenyewaanPerHari;

Route::get('/', function () {
    return redirect('dashboard');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->name('dashboard');

// Route::get('/users', function () {
//     return view('users');
// })->name('users');

// Route::get('/keranjang', function () {
//     return view('keranjang');
// })->name('keranjang');

// Route::get('/cetak', function () {
//     return view('cetak');
// })->name('cetak');

// Route::get('/settings', function () {
//     return view('settings');
// })->name('settings');

// Route::get('/register', function () {
//     return view('livewire/auth/register');
// })->name('register');

Route::get('/dashboard', Dashboard::class)->name('dashboard');

Route::get('/cetak', Cetak::class)->name('cetak');
Route::get('/keranjang', Keranjang::class)->name('keranjang');
Route::get('/settings', Settings::class)->name('settings');
Route::get('/profile', Profile::class)->name('profile');
Route::get('/lapor', Lapor::class)->name('lapor');

Route::get('/detail-tempat/{id}', DetailTempat::class)->name('detail-tempat');
Route::get('/penyewaan/per-jam/{id_lokasi}', PenyewaanPerJam::class)->name('penyewaan.perjam');
Route::get('/penyewaan-perhari/{id_lokasi}', PenyewaanPerHari::class)->name('penyewaan.perhari');

Route::middleware('guest')->group(function () {
    Route::get('/register', Register::class)->name('register');
    Route::get('/login', Login::class)->name('login');
});

// Logout Route
Route::post('/logout', [Login::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

    
