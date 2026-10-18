<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryCalendarController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::get('/public/bills/{billNumber}/pdf', [BillingController::class, 'publicPdf'])->name('bills.public.pdf');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    Route::get('/customers/{customerId}/calendar', [DeliveryCalendarController::class, 'show'])->name('calendar.show');
    Route::post('/calendar/entry', [DeliveryCalendarController::class, 'saveEntry'])->name('calendar.entry.save');

    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/preview/{customerId}/{year}/{month}', [BillingController::class, 'preview'])->name('preview');
        Route::post('/generate', [BillingController::class, 'generate'])->name('generate');
    });

    Route::prefix('bills')->name('bills.')->group(function () {
        Route::get('/{id}', [BillingController::class, 'show'])->name('show');
        Route::get('/{id}/pdf', [BillingController::class, 'downloadPdf'])->name('pdf.download');
        Route::get('/{id}/stream', [BillingController::class, 'streamPdf'])->name('pdf.stream');
        Route::get('/{id}/whatsapp', [BillingController::class, 'shareWhatsApp'])->name('whatsapp');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/password', [SettingsController::class, 'updatePassword'])->name('password');
    });
});
