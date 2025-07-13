<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HealthController;



// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');


// Location Routes
Route::get('/lokasi', [LocationController::class, 'index'])->name('lokasi.index');
Route::get('/lokasi/{location}', [LocationController::class, 'show'])->name('lokasi.detail');

// Report Routes
Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');
Route::get('/laporan/export', [ReportController::class, 'export'])->name('laporan.export');

// Health Routes
Route::get('/kesehatan', [HealthController::class, 'index'])->name('kesehatan.index');

// API Routes for real-time data
Route::get('/api/realtime-data', [DashboardController::class, 'getRealtimeData'])->name('api.realtime');
Route::get('/api/location/{location}/realtime', [LocationController::class, 'getLocationRealtimeData'])->name('api.location.realtime');