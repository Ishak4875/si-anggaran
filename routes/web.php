<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaguController;
use App\Http\Controllers\PpkController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/sync', [DashboardController::class, 'sync'])->name('packets.sync');
Route::get('/satker/{slug}', [DashboardController::class, 'satker'])->name('satker.show');
Route::post('/satker/{slug}/ppk', [DashboardController::class, 'assignPpk'])->name('satker.assignPpk');

Route::get('/pagu', [PaguController::class, 'index'])->name('pagu.index');
Route::post('/pagu', [PaguController::class, 'store'])->name('pagu.store');

Route::get('/ppk', [PpkController::class, 'index'])->name('ppk.index');
Route::post('/ppk', [PpkController::class, 'store'])->name('ppk.store');
Route::put('/ppk/{ppk}', [PpkController::class, 'update'])->name('ppk.update');
Route::delete('/ppk/{ppk}', [PpkController::class, 'destroy'])->name('ppk.destroy');
