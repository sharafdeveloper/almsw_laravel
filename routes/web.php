<?php

use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\SaleInvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Schedule;


Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (Admin + Employee)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('role:admin,employee');

    // Sale Invoice (Admin + Employee can view/create/print; only Admin can edit/delete)
    Route::middleware('role:admin,employee')->group(function () {
        Route::get('/sale-invoice', [SaleInvoiceController::class, 'index'])->name('sale-invoice');
        Route::get('/sale-invoice/create', [SaleInvoiceController::class, 'create'])->name('sale-invoice.create');
        Route::post('/sale-invoice', [SaleInvoiceController::class, 'store'])->name('sale-invoice.store');
        Route::get('/sale-invoice/{sale_invoice}', [SaleInvoiceController::class, 'show'])->name('sale-invoice.show');
        Route::get('/sale-invoice/{sale_invoice}/print', [SaleInvoiceController::class, 'print'])->name('sale-invoice.print');

        // Customers (Admin + Employee can view/create; Admin-only can manage updates/deletes)
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
        Route::get('/customers/{customer}/ledger/print', [CustomerController::class, 'printLedger'])->name('customers.ledger.print');

        // Cashbook (Admin + Employee can view/print/export)
        Route::get('/cashbook',           [CashbookController::class, 'index'])->name('cashbook');
        Route::get('/cashbook/export',    [CashbookController::class, 'exportCsv'])->name('cashbook.export');
        Route::get('/cashbook/print',     [CashbookController::class, 'print'])->name('cashbook.print');

        // Customer search utilities (Admin + Employee)
        Route::get('/admin/customers/search', function(\Illuminate\Http\Request $request) {
            $q = $request->input('q', '');
            return \App\Models\Customer::active()
                ->where(function($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%");
                })
                ->orderBy('name')
                ->limit(15)
                ->get(['id', 'name', 'city']);
        });

        Route::get('/admin/suppliers/search', function(\Illuminate\Http\Request $request) {
            $q = $request->input('q', '');
            return \App\Models\Customer::active()
                ->where(function($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%");
                })
                ->orderBy('name')
                ->limit(15)
                ->get(['id', 'name', 'city']);
        });

        // Payments (Admin + Employee can view/create; Admin-only can edit/delete)
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    });

    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        // Products
        Route::get('/products', [ProductController::class, 'index'])->name('products');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
        Route::get('/inventory/print', [InventoryController::class, 'print'])->name('inventory.print');
        Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');


        // Customers
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Sale Invoice edit/delete (Admin only)
        Route::get('/sale-invoice/{sale_invoice}/edit', [SaleInvoiceController::class, 'edit'])->name('sale-invoice.edit');
        Route::put('/sale-invoice/{sale_invoice}', [SaleInvoiceController::class, 'update'])->name('sale-invoice.update');
        Route::delete('/sale-invoice/{sale_invoice}', [SaleInvoiceController::class, 'destroy'])->name('sale-invoice.destroy');

        // Purchase Invoice
        Route::get('/purchase-invoice', [PurchaseInvoiceController::class, 'index'])->name('purchase-invoice');
        Route::get('/purchase-invoice/create', [PurchaseInvoiceController::class, 'create'])->name('purchase-invoice.create');
        Route::post('/purchase-invoice', [PurchaseInvoiceController::class, 'store'])->name('purchase-invoice.store');
        Route::get('/purchase-invoice/{purchase_invoice}', [PurchaseInvoiceController::class, 'show'])->name('purchase-invoice.show');
        Route::get('/purchase-invoice/{purchase_invoice}/edit', [PurchaseInvoiceController::class, 'edit'])->name('purchase-invoice.edit');
        Route::put('/purchase-invoice/{purchase_invoice}', [PurchaseInvoiceController::class, 'update'])->name('purchase-invoice.update');
        Route::delete('/purchase-invoice/{purchase_invoice}', [PurchaseInvoiceController::class, 'destroy'])->name('purchase-invoice.destroy');
        Route::get('/purchase-invoice/{purchase_invoice}/print', [PurchaseInvoiceController::class, 'print'])->name('purchase-invoice.print');

        // Payments
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        // Cashbook
        Route::post('/cashbook/opening',  [CashbookController::class, 'updateOpening'])->name('cashbook.opening');

        // Balance Sheet (customer Debit/Credit list)
        Route::get('/balance-sheet',       [BalanceSheetController::class, 'index'])->name('balance-sheet');
        Route::get('/balance-sheet/print', [BalanceSheetController::class, 'print'])->name('balance-sheet.print');

        // Backup
        // Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');

        // Backup
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');

        Route::get('/backup/database', [BackupController::class, 'database'])->name('backup.database');

        Route::get('/backup/full', [BackupController::class, 'fullBackup'])->name('backup.full');

        Route::get('/backup/download/{id}', [BackupController::class, 'download'])->name('backup.download');


        // Restore Database 
        Route::prefix('backup')->name('backup.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');

            Route::get('/database', [BackupController::class, 'database'])->name('database');

            Route::post('/validate', [BackupController::class, 'validateBackup'])->name('validate');

            Route::post('/restore', [BackupController::class, 'restore'])->name('restore');

            Route::post('/backup/validate', [BackupController::class, 'validateBackup'])->name('backup.validate');
        });

        // Route::post('/backup/restore-ready',[BackupController::class, 'restoreReady'])->name('backup.restore.ready');


       
   

   
    Route::post('/backup/restore-execute',[BackupController::class, 'restoreExecute'])->name('backup.restore.execute');



    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Schedule::command('backup:auto')
            ->dailyAt('09:00');

    Schedule::command('backup:auto')
            ->dailyAt('23:00');
    
    Route::view('/test-file-access', 'test-file-access');
    //testing the print local controller
    Route::get(
    '/sale-invoice/{sale_invoice}/print-local',
    [SaleInvoiceController::class, 'printLocal']
)->name('sale-invoice.print-local');
   

});

    

require __DIR__.'/auth.php';
