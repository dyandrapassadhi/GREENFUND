<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonasiController as AdminDonasiController;
use App\Http\Controllers\Admin\KampanyeController;
use App\Http\Controllers\Admin\LogPerkembanganController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonasiController;
use App\Http\Controllers\KatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KatalogController::class, 'home'])->name('home');
Route::get('/kampanye', [KatalogController::class, 'index'])->name('katalog');
Route::get('/kampanye/{kampanye:slug}', [KatalogController::class, 'show'])->name('katalog.show');
Route::get('/tentang-sdg', [KatalogController::class, 'tentang'])->name('tentang-sdg');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/donasi', [DonasiController::class, 'store'])->name('donasi.store');
Route::get('/donasi/{donasi:kode}', [DonasiController::class, 'invoice'])->name('donasi.invoice');
Route::get('/donasi/{donasi:kode}/qris.png', [DonasiController::class, 'qris'])->name('donasi.qris');
Route::post('/donasi/{donasi:kode}/bukti', [DonasiController::class, 'uploadBukti'])->name('donasi.bukti');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/donatur', [DashboardController::class, 'donatur'])->name('donatur');

    Route::get('/kampanye', [KampanyeController::class, 'index'])->name('kampanye.index');
    Route::post('/kampanye', [KampanyeController::class, 'store'])->name('kampanye.store');
    Route::put('/kampanye/{kampanye}', [KampanyeController::class, 'update'])->name('kampanye.update');
    Route::delete('/kampanye/{kampanye}', [KampanyeController::class, 'destroy'])->name('kampanye.destroy');

    Route::get('/log', [LogPerkembanganController::class, 'index'])->name('log.index');
    Route::post('/log', [LogPerkembanganController::class, 'store'])->name('log.store');
    Route::delete('/log/{log}', [LogPerkembanganController::class, 'destroy'])->name('log.destroy');

    Route::get('/donasi', [AdminDonasiController::class, 'index'])->name('donasi.index');
    Route::patch('/donasi/{donasi}/status', [AdminDonasiController::class, 'updateStatus'])->name('donasi.status');
});
