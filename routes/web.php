<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentExceptionController;
use App\Http\Controllers\TrackingEventController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Admin & Operator Shipment & Operations Management Routes
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
        Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
        Route::get('/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
        Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
        Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy');

        // Record tracking events
        Route::post('/shipments/{shipment}/tracking-events', [TrackingEventController::class, 'store'])->name('shipments.tracking-events.store');

        // ETA & Delay management
        Route::post('/shipments/{shipment}/eta', [ShipmentExceptionController::class, 'updateEta'])->name('shipments.eta.update');

        // Shipment Exceptions management
        Route::post('/shipments/{shipment}/exceptions', [ShipmentExceptionController::class, 'store'])->name('shipments.exceptions.store');
        Route::patch('/exceptions/{exception}/status', [ShipmentExceptionController::class, 'updateStatus'])->name('exceptions.status.update');
    });

    // Shipment Read Routes (All authenticated roles)
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');

    // Admin Only Master Data Routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('carriers', CarrierController::class)->except(['create', 'show', 'edit']);
        Route::get('/locations/geocode', [LocationController::class, 'geocode'])->name('locations.geocode');
        Route::resource('locations', LocationController::class)->except(['create', 'show', 'edit']);
    });
});
