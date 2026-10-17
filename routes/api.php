<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryCalendarController;
use App\Http\Controllers\BillingController;
use App\Models\Customer;

Route::post('/login', [AuthController::class, 'apiLogin']);

Route::middleware('jwt.auth')->group(function () {
    Route::get('/me', [AuthController::class, 'apiMe']);

    Route::get('/customers', function () {
        return response()->json([
            'success' => true,
            'data' => Customer::all(),
        ]);
    });

    Route::get('/customers/{id}', function ($id) {
        $customer = Customer::with(['dailyDeliveries', 'bills', 'payments'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    });

    Route::post('/calendar/entry', [DeliveryCalendarController::class, 'saveEntry']);
    Route::post('/billing/generate', [BillingController::class, 'generate']);
});
