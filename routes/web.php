<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');


Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/banks', [ProfileController::class, 'updateBanks'])->name('profile.update-banks');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin CMS Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Pohon Jaringan (Genealogy Binary Tree)
    Route::get('/pohon-jaringan', [\App\Http\Controllers\Admin\GenealogyController::class, 'index'])->name('pohon-jaringan');

    // Aktivasi Member Baru
    Route::get('/aktivasi-member', [\App\Http\Controllers\Admin\MemberActivationController::class, 'index'])->name('activation.index');
    Route::post('/aktivasi-member', [\App\Http\Controllers\Admin\MemberActivationController::class, 'store'])->name('activation.store');

    // Repeat Order (RO)
    Route::get('/repeat-order', [\App\Http\Controllers\Admin\RepeatOrderController::class, 'index'])->name('repeat-order.index');
    Route::post('/repeat-order', [\App\Http\Controllers\Admin\RepeatOrderController::class, 'store'])->name('repeat-order.store');
    Route::post('/repeat-order/buy-voucher', [\App\Http\Controllers\Admin\RepeatOrderController::class, 'buyVoucher'])->name('repeat-order.buy-voucher');

    // Purchase Order (PO)
    Route::get('/purchase-order', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'index'])->name('purchase-order.index');
    Route::post('/purchase-order', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'store'])->name('purchase-order.store');
    Route::post('/purchase-order/buy-voucher', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'buyVoucher'])->name('purchase-order.buy-voucher');

    // Voucher / PIN Wallet
    Route::get('/voucher-wallet', [\App\Http\Controllers\Admin\VoucherWalletController::class, 'index'])->name('voucher-wallet.index');
    Route::post('/voucher-wallet/buy', [\App\Http\Controllers\Admin\VoucherWalletController::class, 'buy'])->name('voucher-wallet.buy');
    Route::post('/voucher-wallet/produce', [\App\Http\Controllers\Admin\VoucherWalletController::class, 'produce'])->name('voucher-wallet.produce');
    Route::post('/voucher-wallet/transfer', [\App\Http\Controllers\Admin\VoucherWalletController::class, 'transfer'])->name('voucher-wallet.transfer');

    // Riwayat Steping (Khusus Member)
    Route::get('/riwayat-steping', [\App\Http\Controllers\Admin\StepingHistoryController::class, 'index'])->name('steping-history.index');

    // Keuangan (Finance & E-Wallet Management - Khusus Admin)
    Route::get('/keuangan', [\App\Http\Controllers\Admin\FinanceController::class, 'index'])->name('finance.index');
    Route::post('/keuangan/cashout', [\App\Http\Controllers\Admin\FinanceController::class, 'cashoutBonus'])->name('finance.cashout');
    Route::post('/keuangan/topup-admin', [\App\Http\Controllers\Admin\FinanceController::class, 'topupAdmin'])->name('finance.topup-admin');
    Route::post('/keuangan/generate-saldo', [\App\Http\Controllers\Admin\FinanceController::class, 'generateSaldo'])->name('finance.generate-saldo');
    Route::post('/keuangan/transfer', [\App\Http\Controllers\Admin\FinanceController::class, 'transfer'])->name('finance.transfer');

    // Kelola Produk (Product CRUD - Khusus Admin)
    Route::get('/kelola-produk', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::post('/kelola-produk', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::post('/kelola-produk/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/kelola-produk/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

    // Penarikan Saldo (Withdrawals / WD)
    Route::get('/penarikan-saldo', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/penarikan-saldo', [\App\Http\Controllers\Admin\WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::post('/penarikan-saldo/{withdrawal}/approve', [\App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/penarikan-saldo/{withdrawal}/reject', [\App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Data Jaringan (Member Directory & Impersonation)
    Route::get('/data-jaringan', [\App\Http\Controllers\Admin\NetworkDataController::class, 'index'])->name('network-data.index');
    Route::post('/data-jaringan/impersonate/{user}', [\App\Http\Controllers\Admin\NetworkDataController::class, 'impersonate'])->name('network-data.impersonate');
    Route::post('/data-jaringan/stop-impersonating', [\App\Http\Controllers\Admin\NetworkDataController::class, 'stopImpersonating'])->name('network-data.stop-impersonating');

    // Fitur TPR (Trade Promotion Program)
    Route::get('/fitur-tpr', [\App\Http\Controllers\Admin\TprController::class, 'index'])->name('tpr.index');
    Route::post('/fitur-tpr', [\App\Http\Controllers\Admin\TprController::class, 'store'])->name('tpr.store');
    Route::post('/fitur-tpr/{tprRequest}/approve', [\App\Http\Controllers\Admin\TprController::class, 'approve'])->name('tpr.approve');
    Route::post('/fitur-tpr/{tprRequest}/reject', [\App\Http\Controllers\Admin\TprController::class, 'reject'])->name('tpr.reject');

    // Aktivitas (Activity & Bonus Breakdown)
    Route::get('/aktivitas', [\App\Http\Controllers\Admin\ActivityController::class, 'index'])->name('activities.index');

    // Laporan (Reports & Excel/PDF Exports)
    Route::get('/laporan', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/export-excel', [\App\Http\Controllers\Admin\ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('/laporan/export-pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])->name('reports.export-pdf');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/rewards', [SettingController::class, 'updateRewards'])->name('settings.rewards');

    // Backup Data (JSON)
    Route::get('/backup-data-json', [\App\Http\Controllers\Admin\BackupController::class, 'downloadJson'])->name('backup-json');

    // Pages
    Route::resource('pages', PageController::class);
    Route::put('pages/{page}/sections/{section}', [PageController::class, 'updateSection'])->name('pages.sections.update');

    // Posts & Categories
    Route::resource('posts', PostController::class);
    Route::post('categories', [PostController::class, 'storeCategory'])->name('categories.store');
    Route::delete('categories/{category}', [PostController::class, 'destroyCategory'])->name('categories.destroy');
});

require __DIR__.'/auth.php';
