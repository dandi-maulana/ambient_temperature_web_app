<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');


// Location Routes
Route::get('/lokasi', [LocationController::class, 'index'])->name('lokasi.index');
Route::get('/lokasi/{location}', [LocationController::class, 'show'])->name('lokasi.detail');


// API Routes for real-time data
Route::get('/api/realtime-data', [DashboardController::class, 'getRealtimeData'])->name('api.realtime');
Route::get('/api/location/{location}/realtime', [LocationController::class, 'getLocationRealtimeData'])->name('api.location.realtime');