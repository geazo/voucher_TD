<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestAdminController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TopupController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GuestController::class, 'index'])->name('home');
Route::get('/topup', [TopupController::class, 'index'])->name('topup.index');
Route::post('/topup', [TopupController::class, 'store'])->name('topup.store');

// Route::get('/', function () {
//     return redirect('/login');
// });

Route::post('/topup', [GuestController::class, 'store']);

Route::get('/thanks', [GuestController::class, 'thanks'])->name('thanks');

Route::get('/scan', [DashboardController::class, 'scanIndex'])->middleware(['auth', 'verified'])->name('scan');
Route::post('/scan-barcode', [DashboardController::class, 'scanBarcode'])->middleware(['auth', 'verified'])->name('scan.barcode');

// User
Route::get('/user', [DashboardController::class, 'userIndex'])->middleware(['auth', 'verified'])->name('user');
Route::post('/user', [DashboardController::class, 'userStore'])->middleware(['auth', 'verified'])->name('user.store');
Route::put('/user/{id}', [DashboardController::class, 'userUpdate'])->middleware(['auth', 'verified'])->name('user.update');
Route::delete('/user/{id}', [DashboardController::class, 'userDestroy'])->middleware(['auth', 'verified'])->name('user.destroy');

// API untuk modal edit
Route::get('/user/api/{id}', [DashboardController::class, 'userShow'])->middleware(['auth', 'verified']);

// import Excel
Route::post('/user/import', [DashboardController::class, 'importExcel'])->middleware(['auth', 'verified'])->name('user.import');

// export Excel
Route::get('/admin/export', [DashboardController::class, 'exportAllExcel'])->middleware(['auth', 'verified'])->name('admin.export.all');
Route::get('/admin/export/padel',  [DashboardController::class, 'exportPadel'])->middleware(['auth','verified'])->name('admin.export.padel');
Route::get('/admin/export/others', [DashboardController::class, 'exportOthers'])->middleware(['auth','verified'])->name('admin.export.others');

// Outlet
Route::get('/outlet', [DashboardController::class, 'outletIndex'])->middleware(['auth', 'verified'])->name('outlet');
Route::post('/outlet', [DashboardController::class, 'outletStore'])->middleware(['auth', 'verified'])->name('outlet.store');
Route::put('/outlet/{id}', [DashboardController::class, 'outletUpdate'])->middleware(['auth', 'verified'])->name('outlet.update');
Route::delete('/outlet/{id}', [DashboardController::class, 'outletDestroy'])->middleware(['auth', 'verified'])->name('outlet.destroy');

// Voucher
Route::get('/voucher', [DashboardController::class, 'voucherIndex'])->middleware(['auth', 'verified'])->name('voucher');
Route::post('/voucher', [DashboardController::class, 'voucherStore'])->middleware(['auth', 'verified'])->name('voucher.store');
Route::put('/voucher/{id}', [DashboardController::class, 'voucherUpdate'])->middleware(['auth', 'verified'])->name('voucher.update');
Route::delete('/voucher/{id}', [DashboardController::class, 'voucherDestroy'])->middleware(['auth', 'verified'])->name('voucher.destroy');
Route::get('/voucher/search', [DashboardController::class, 'searchVoucher'])->middleware(['auth', 'verified'])->name('voucher.search');
Route::get('/voucher/{id}/benefits', [DashboardController::class, 'getBenefits'])->middleware(['auth', 'verified'])->name('voucher.benefits');

// Benefit
Route::get('/benefit', [DashboardController::class, 'benefitIndex'])->middleware(['auth', 'verified'])->name('benefit');
Route::post('/benefit', [DashboardController::class, 'benefitStore'])->middleware(['auth', 'verified'])->name('benefit.store');
Route::put('/benefit/{id}', [DashboardController::class, 'benefitUpdate'])->middleware(['auth', 'verified'])->name('benefit.update');
Route::delete('/benefit/{id}', [DashboardController::class, 'benefitDestroy'])->middleware(['auth', 'verified'])->name('benefit.destroy');
Route::get('/benefit/search', [DashboardController::class, 'searchbenefit'])->middleware(['auth', 'verified'])->name('benefit.search');

// Transaksi
Route::get('/transaksi', [DashboardController::class, 'transaksiIndex'])->middleware(['auth', 'verified'])->name('transaksi');
Route::post('/voucher/send/{id}', [DashboardController::class, 'sendVoucher'])->name('voucher.send');
Route::post('/voucher/wa/{id}', [DashboardController::class, 'sendVoucherWhatsApp'])->name('voucher.send.whatsapp');
Route::get('/transaksi/search', [DashboardController::class, 'searchTransaksi'])->middleware(['auth', 'verified'])->name('transaksi.search');

Route::patch('/penerima/{id}/reset-voucher', [DashboardController::class, 'resetVoucher'])->name('penerima.resetVoucher');
Route::get('/penerima/{id}/edit', [DashboardController::class, 'editPenerima'])->name('penerima.edit');
Route::patch('/penerima/{id}', [DashboardController::class, 'updatePenerima'])->name('penerima.update');

// HEAD (ADMIN)
Route::middleware(['auth', 'verified', 'head'])->group(function () {
    // Voucher
    Route::get('/voucherHead/search', [HeadController::class, 'searchVoucherHead'])->name('voucherHead.search');

    Route::get('/voucherHead', [HeadController::class, 'voucherHeadIndex'])->name('voucherHead');
    Route::post('/voucherHead', [HeadController::class, 'voucherHeadStore'])->name('voucherHead.store');
    Route::put('/voucherHead/{id}', [HeadController::class, 'voucherHeadUpdate'])->name('voucherHead.update');
    Route::delete('/voucherHead/{id}', [HeadController::class, 'voucherHeadDestroy'])->name('voucherHead.destroy');
    Route::get('/voucherHead/{id}/benefits', [HeadController::class, 'getBenefits'])->middleware(['auth', 'verified'])->name('voucherHead.benefits');

    // Transaksi
    Route::get('/transaksiHead', [HeadController::class, 'transaksiHeadIndex'])->name('transaksiHead');
    Route::get('/transaksiHead/search', [HeadController::class, 'searchTransaksiHead'])->middleware(['auth', 'verified'])->name('transaksiHead.search');

    // Scan
    Route::get('/scanHead', [HeadController::class, 'scanHeadIndex'])->name('scanHead');
    Route::post('/scanHead-barcode', [HeadController::class, 'scanHeadBarcode'])->name('scanHead.barcode');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/{id}/edit', [DashboardController::class, 'edit'])->middleware(['auth', 'verified']);
Route::put('/dashboard/{tamu}', [DashboardController::class, 'update'])->middleware(['auth', 'verified']);
Route::post('/dashboard/{tamu}/keluar', [DashboardController::class, 'signOut'])->middleware(['auth', 'verified']);
Route::get('/dashboard/search', [DashboardController::class, 'search'])->middleware(['auth', 'verified']);
Route::get('/dashboard/create', [DashboardController::class, 'create'])->middleware(['auth', 'verified']);
Route::post('/dashboard/tambah', [DashboardController::class, 'store'])->middleware(['auth', 'verified']);

// GuestAdmin
Route::get('/guest', [GuestAdminController::class, 'index'])->middleware(['auth', 'verified']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
