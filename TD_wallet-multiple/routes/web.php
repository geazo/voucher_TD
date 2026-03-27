<?php

use Illuminate\Support\Facades\Route;

// --- Controllers ---
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperatorPaymentController;
use App\Http\Controllers\TopupController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminExtensionController;
use App\Http\Controllers\AdminOperatorController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\TransactionController;

// --- Middlewares ---
use App\Http\Middleware\ForcePasswordChange;

Route::redirect('/', '/customer/login');

// =========================================================================
// 1. AREA CUSTOMER
// =========================================================================
Route::prefix('customer')->name('customer.')->group(function () {

    // --- Guest (Belum Login) ---
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerController::class, 'login'])->name('login.submit');

        // Lupa Password
        Route::get('/forgot-password', [CustomerController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [CustomerController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [CustomerController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('/reset-password', [CustomerController::class, 'updatePassword'])->name('password.update');
    });

    // --- Terautentikasi (Sudah Login) ---
    Route::middleware('auth:customer')->group(function () {

        Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');
        Route::get('/logoutpaksa', [CustomerController::class, 'logout']); // Tambahan untuk debug

        // Ganti Password Paksa (Untuk akun baru)
        Route::get('/change-password', [CustomerController::class, 'showForceChangePassword'])->name('force_password.change');
        Route::post('/change-password', [CustomerController::class, 'updateForcePassword'])->name('force_password.update');

        // Akses Utama (Setelah melewati syarat ganti password)
        Route::middleware([ForcePasswordChange::class])->group(function () {
            // Dashboard & Profil
            Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
            Route::get('/profile', [CustomerDashboardController::class, 'profile'])->name('profile');
            Route::put('/profile/password', [CustomerDashboardController::class, 'updatePassword'])->name('profile.password');
            Route::get('/transaction/{id}', [CustomerDashboardController::class, 'showInvoiceDetail'])->name('transaction.detail');
            Route::get('/saldo-info', [CustomerDashboardController::class, 'saldoInfo'])->name('saldo.info');

            // Pembayaran via QR Code
            Route::get('/pay/auth', [CustomerPaymentController::class, 'showAuth'])->name('payment.auth');
            Route::post('/pay/auth', [CustomerPaymentController::class, 'verifyAuth'])->name('payment.verify');
            Route::get('/pay/qr', [CustomerPaymentController::class, 'showQr'])->name('payment.qr');
            Route::get('/pay/check-status/{token}', [CustomerPaymentController::class, 'checkStatus'])->name('payment.check_status');
            Route::get('/pay/invoice/{token}', [CustomerPaymentController::class, 'showInvoice'])->name('payment.invoice');
        });
    });
});

// =========================================================================
// 2. AREA OPERATOR / ADMIN
// =========================================================================

// --- Guest Operator ---
Route::middleware('guest')->group(function () {
    Route::get('/operator/login', [OperatorController::class, 'showLoginForm'])->name('login');
    Route::post('/operator/login', [OperatorController::class, 'login'])->name('login.submit');
});

// --- Terautentikasi Operator ---
Route::prefix('operator')->middleware(['auth'])->group(function () {

    // Akses Umum (Semua level role yang sudah login)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [OperatorController::class, 'profile'])->name('admin.profile');
    Route::put('/profile/password', [OperatorController::class, 'updatePassword'])->name('admin.profile.password');
    Route::post('/logout', [OperatorController::class, 'logout'])->name('logout');

    // Akses Kasir, Admin, Superadmin
    Route::middleware('role:kasir,admin,superadmin')->group(function () {
        // Topup & Register Customer
        Route::get('/topup', [TopupController::class, 'index'])->name('topup.index');
        Route::post('/topup', [TopupController::class, 'store'])->name('topup.store');
        Route::get('/customer/register', [AdminCustomerController::class, 'create'])->name('customers.create');
        Route::post('/customer/register', [AdminCustomerController::class, 'store'])->name('customers.store');

        // POS / Kasir Scanner
        Route::get('/scan-qr', [OperatorPaymentController::class, 'showScanner'])->name('scan.index');
        Route::post('/scan-qr/process', [OperatorPaymentController::class, 'processPayment'])->name('scan.process');
    });

    // Akses Admin & Superadmin (Hak Akses Lebih Tinggi)
    Route::middleware('role:admin,superadmin')->group(function () {
        // -----------------------------------------------------------------
        // MASTER DATA ITEM / LAYANAN (ROUTE BARU)
        // -----------------------------------------------------------------
        Route::get('/items', [ItemController::class, 'index'])->name('admin.items.index');
        Route::post('/items', [ItemController::class, 'store'])->name('admin.items.store');
        Route::put('/items/{id}', [ItemController::class, 'update'])->name('admin.items.update');
        Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('admin.items.destroy');

        // Manajemen Operator
        Route::get('/operators', [AdminOperatorController::class, 'index'])->name('operators.index');
        Route::get('/operators/create', [AdminOperatorController::class, 'create'])->name('operators.create');
        Route::post('/operators', [AdminOperatorController::class, 'store'])->name('operators.store');
        Route::get('/operators/{operator}/edit', [AdminOperatorController::class, 'edit'])->name('operators.edit');
        Route::put('/operators/{operator}', [AdminOperatorController::class, 'update'])->name('operators.update');
        Route::delete('/operators/{operator}', [AdminOperatorController::class, 'destroy'])->name('operators.destroy');

        // Manajemen Customer
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/export', [AdminCustomerController::class, 'export'])->name('customers.export');
        Route::get('/customers/{customer}/detail', [AdminCustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');

        // Laporan Transaksi
        Route::get('/transactions', [TransactionController::class, 'index'])->name('admin.transactions');
        Route::get('/transactions/export', [TransactionController::class, 'export'])->name('admin.transactions.export');

        // Route untuk Admin/Operator memproses Extension
        Route::get('/extensions/create', [AdminExtensionController::class, 'createRequest'])->name('admin.extensions.create');
        Route::post('/extensions/store', [AdminExtensionController::class, 'storeRequest'])->name('admin.extensions.store');
        Route::get('/extensions', [AdminExtensionController::class, 'index'])->name('admin.extensions.index');

        //route untuk manajemen membership
        Route::resource('memberships', MembershipController::class)->except(['create', 'show', 'edit']);
    });

    // Akses Khusus Superadmin
    Route::middleware('role:superadmin')->group(function () {
        // Ruang untuk rute superadmin
        Route::get('/extensions/approvals', [AdminExtensionController::class, 'index'])->name('superadmin.extensions.index');
        Route::post('/extensions/{id}/process', [AdminExtensionController::class, 'process'])->name('superadmin.extensions.process');
    });
});
