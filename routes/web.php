<?php

use App\Livewire\Cetak;
use App\Livewire\Lapor;
use App\Livewire\Users;
use App\Livewire\Ulasan;
use App\Livewire\Profile;
use App\Livewire\Checkout;
use App\Livewire\Kalender;
use App\Livewire\Settings;
use App\Livewire\Dashboard;
use App\Livewire\Keranjang;
use App\Livewire\Auth\Login;
use App\Livewire\DetailTempat;
use App\Livewire\Profile\Edit;
use App\Livewire\Auth\Register;
use App\Livewire\Profile\Index;
use App\Livewire\Kalenderperjam;
use App\Livewire\RiwayatPesanan;
use App\Livewire\Kalenderperhari;
use App\Livewire\PenyewaanPerJam;
use App\Livewire\PenyewaanPerHari;
use App\Livewire\Auth\UbahPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransaksiPdfController;
use App\Livewire\Auth\LupaPassword;

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
Route::get('/riwayat', RiwayatPesanan::class)->name('riwayat');
Route::get('/indexprofile', Index::class)->name('indexProfile');
Route::get('/editprofile', Edit::class)->name('editProfile');
Route::get('/ubahpassword', UbahPassword::class)->name('ubahpassword');
Route::get('/lupapassword', LupaPassword::class)->name('lupapassword');
Route::get('/lapor', Lapor::class)->name('lapor');
Route::get('/ulasan', Ulasan::class)->name('ulasan');
Route::get('/kalenderperhari', Kalenderperhari::class)->name('kalenderperhari');
Route::get('/kalenderperjam', Kalenderperjam::class)->name('kalenderperjam');
Route::get('/detail-tempat/{id}', DetailTempat::class)->name('detail-tempat');
Route::get('/penyewaan-perjam/{id_lokasi}', PenyewaanPerJam::class)->name('penyewaan.perjam');
Route::get('/penyewaan-perhari/{id_lokasi}', PenyewaanPerHari::class)->name('penyewaan.perhari');

Route::middleware('guest')->group(function () {
    Route::get('/register', Register::class)->name('register');
    Route::get('/login', Login::class)->name('login');
});

// Route::middleware(['auth'])->get('/profil', Profile::class)->name('profile.show');

// Route::get('/profil', Profile::class)
//     ->middleware('auth') // <= Ini yang penting
//     ->name('profile.show');

//     // routes/web.php


Route::post('/cetak-transaksi-pdf', [TransaksiPdfController::class, 'generate'])->name('cetak.transaksi.pdf');

// Logout Route
Route::post('/logout', [Login::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

    
