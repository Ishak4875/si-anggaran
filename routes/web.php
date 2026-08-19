<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaguController;
use App\Http\Controllers\PpkController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/sync', [DashboardController::class, 'sync'])->name('packets.sync');
    Route::post('/ditjen-progres', [DashboardController::class, 'updateDitjenProgres'])->name('ditjen.update');
    Route::get('/satker/{slug}', [DashboardController::class, 'satker'])->name('satker.show');
    Route::post('/satker/{slug}/ppk', [DashboardController::class, 'assignPpk'])->name('satker.assignPpk');

    Route::get('/pagu', [PaguController::class, 'index'])->name('pagu.index');
    Route::post('/pagu', [PaguController::class, 'store'])->name('pagu.store');

    Route::get('/ppk', [PpkController::class, 'index'])->name('ppk.index');
    Route::post('/ppk', [PpkController::class, 'store'])->name('ppk.store');
    Route::put('/ppk/{ppk}', [PpkController::class, 'update'])->name('ppk.update');
    Route::delete('/ppk/{ppk}', [PpkController::class, 'destroy'])->name('ppk.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
