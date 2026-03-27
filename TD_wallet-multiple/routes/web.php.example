<?php

use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminOperatorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\OperatorPaymentController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TopupController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\ForcePasswordChange;

Route::redirect('/', '/customer/login');
// Route::get('/logout-forced', [App\Http\Controllers\OperatorController::class, 'logout']);

//  ============ Route untuk Customer =================
Route::prefix('customer')->name('customer.')->group(function () {
    // Guest (Belum Login)
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerController::class, 'login'])->name('login.submit');
        // Forget Password
        Route::get('/forgot-password', [CustomerController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [CustomerController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [CustomerController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('/reset-password', [CustomerController::class, 'updatePassword'])->name('password.update');
    });
    // Terautentikasi (Sudah Login sebagai Customer)
    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');

        Route::get('/change-password', [CustomerController::class, 'showForceChangePassword'])->name('password.change');
        Route::post('/change-password', [CustomerController::class, 'updateForcePassword'])->name('password.update');


        Route::middleware([ForcePasswordChange::class])->group(function () {
            Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
            Route::get('/transaction/{id}', [CustomerDashboardController::class, 'showInvoiceDetail'])->name('transaction.detail');
            Route::get('/profile', [CustomerDashboardController::class, 'profile'])->name('profile');
            Route::put('/profile/password', [CustomerDashboardController::class, 'updatePassword'])->name('profile.password');

            // Route Pembayaran Customer via QR Code
            Route::get('/pay/auth', [CustomerPaymentController::class, 'showAuth'])->name('payment.auth');
            Route::post('/pay/auth', [CustomerPaymentController::class, 'verifyAuth'])->name('payment.verify');
            Route::get('/pay/invoice/{token}', [CustomerPaymentController::class, 'showInvoice'])->name('payment.invoice');
            Route::get('/pay/qr', [CustomerPaymentController::class, 'showQr'])->name('payment.qr');
            Route::get('/pay/check-status/{token}', [CustomerPaymentController::class, 'checkStatus'])->name('payment.check_status');
        });
    });
});

// ============ Route untuk Operator =================
Route::middleware('guest')->group(function () {
    Route::get('/operator/login', [OperatorController::class, 'showLoginForm'])->name('login');
    Route::post('/operator/login', [OperatorController::class, 'login'])->name('login.submit');
});

Route::prefix('operator')->middleware(['auth'])->group(function () {
    // --- Dashboard & Profil Umum ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [OperatorController::class, 'profile'])->name('admin.profile');
    Route::put('/profile/password', [OperatorController::class, 'updatePassword'])->name('admin.profile.password');
    //logout
    Route::post('/logout', [OperatorController::class, 'logout'])->name('logout');
    // --- Akses Kasir, Admin, Superadmin ---
    Route::middleware('role:kasir,admin,superadmin')->group(function () {
        // Topup Saldo
        Route::get('/topup', [TopupController::class, 'index'])->name('topup.index');
        Route::post('/topup', [TopupController::class, 'store'])->name('topup.store');
        // Registrasi Customer Baru
        Route::get('/customer/register', [AdminCustomerController::class, 'create'])->name('customers.create');
        Route::post('/customer/register', [AdminCustomerController::class, 'store'])->name('customers.store');
    });
    // --- Akses Admin & Superadmin ---
    Route::middleware('role:admin,superadmin')->group(function () {
        // Route Menampilkan Riwayat
        Route::get('/transactions', [TransactionController::class, 'index'])->name('admin.transactions');
        Route::get('/transactions/export', [TransactionController::class, 'export'])->name('admin.transactions.export');
        // Route Manajemen Operator
        Route::get('/operators', [AdminOperatorController::class, 'index'])->name('operators.index');
        Route::get('/operators/create', [AdminOperatorController::class, 'create'])->name('operators.create');
        Route::post('/operators', [AdminOperatorController::class, 'store'])->name('operators.store');
        Route::get('/operators/{operator}/edit', [AdminOperatorController::class, 'edit'])->name('operators.edit');
        Route::put('/operators/{operator}', [AdminOperatorController::class, 'update'])->name('operators.update');
        Route::delete('/operators/{operator}', [AdminOperatorController::class, 'destroy'])->name('operators.destroy');
        // Route Manajemen Customer
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/export', [AdminCustomerController::class, 'export'])->name('customers.export');
        Route::get('/customers/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');
    });
    // --- Akses Hanya Superadmin ---
    Route::middleware('role:superadmin')->group(function () {});
    // --- Pembayaran Scanner QR ---
    Route::get('/scan-qr', [OperatorPaymentController::class, 'showScanner'])->name('scan.index');
    Route::post('/scan-qr/process', [OperatorPaymentController::class, 'processPayment'])->name('scan.process');
});
