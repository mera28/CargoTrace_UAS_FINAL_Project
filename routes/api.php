<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ResourceApiController;
use App\Http\Controllers\Api\ShipmentApiController;
use Illuminate\Support\Facades\Route;

// Public Authentication Endpoints
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Current User Profile & Token Revocation
    Route::get('/user', [AuthApiController::class, 'user']);
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Master Data Resource Reading (All authenticated roles)
    Route::get('/carriers', [ResourceApiController::class, 'carriers']);
    Route::get('/locations', [ResourceApiController::class, 'locations']);

    // Shipment Resource Reading (Customer automatically scoped by ShipmentService/Ownership check)
    Route::get('/shipments', [ShipmentApiController::class, 'index']);
    Route::get('/shipments/{shipment}', [ShipmentApiController::class, 'show']);
    Route::get('/shipments/{shipment}/tracking', [ShipmentApiController::class, 'tracking']);
    Route::get('/shipments/{shipment}/exceptions', [ResourceApiController::class, 'shipmentExceptions']);

    // Operations Management (Admin & Operator only)
    Route::middleware('role:admin,operator')->group(function () {
        Route::post('/shipments', [ShipmentApiController::class, 'store']);
        Route::match(['put', 'patch'], '/shipments/{shipment}', [ShipmentApiController::class, 'update']);
        Route::delete('/shipments/{shipment}', [ShipmentApiController::class, 'destroy']);

        // Milestone Tracking & Exceptions Reporting
        Route::post('/shipments/{shipment}/tracking', [ShipmentApiController::class, 'recordTracking']);
        Route::post('/shipments/{shipment}/exceptions', [ResourceApiController::class, 'storeException']);
        Route::patch('/exceptions/{exception}', [ResourceApiController::class, 'updateExceptionStatus']);
    });

    // Master Data Creation (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::post('/carriers', [ResourceApiController::class, 'storeCarrier']);
        Route::post('/locations', [ResourceApiController::class, 'storeLocation']);
    });
});
