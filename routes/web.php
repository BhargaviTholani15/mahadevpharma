<?php

use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ClientWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeliveryWebController;
use App\Http\Controllers\Web\InventoryWebController;
use App\Http\Controllers\Web\InvoiceWebController;
use App\Http\Controllers\Web\LookupWebController;
use App\Http\Controllers\Web\OrderWebController;
use App\Http\Controllers\Web\PaymentWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\PurchaseOrderWebController;
use App\Http\Controllers\Web\StockTransferWebController;
use App\Http\Controllers\Web\SupplierWebController;
use App\Http\Controllers\Web\WarehouseWebController;
use Illuminate\Support\Facades\Route;

// ─── Marketing Website ──────────────────────────────────────────────
Route::view('/', 'marketing.home');
Route::view('/about', 'marketing.about');
Route::view('/how-we-work', 'marketing.how-we-work');
Route::view('/blog', 'marketing.blog');
Route::get('/blog/{slug}', function (string $slug) {
    return view('marketing.blog-post', ['slug' => $slug]);
})->name('blog.show');
Route::view('/contact', 'marketing.contact');

// ─── Auth ────────────────────────────────────────────────────────────
Route::get('/admin', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::get('/vendor-portal', [AuthController::class, 'showVendorLogin'])->name('vendor.login');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup'])->name('signup.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Authenticated ──────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Products ──────────────────────────────────────
    Route::resource('products', ProductWebController::class);

    // ─── Lookup CRUD ───────────────────────────────────
    Route::get('/categories', [LookupWebController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [LookupWebController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [LookupWebController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [LookupWebController::class, 'destroyCategory'])->name('categories.destroy');

    Route::get('/brands', [LookupWebController::class, 'brands'])->name('brands.index');
    Route::post('/brands', [LookupWebController::class, 'storeBrand'])->name('brands.store');
    Route::put('/brands/{brand}', [LookupWebController::class, 'updateBrand'])->name('brands.update');
    Route::delete('/brands/{brand}', [LookupWebController::class, 'destroyBrand'])->name('brands.destroy');

    Route::get('/hsn-codes', [LookupWebController::class, 'hsnCodes'])->name('hsn-codes.index');
    Route::post('/hsn-codes', [LookupWebController::class, 'storeHsnCode'])->name('hsn-codes.store');
    Route::put('/hsn-codes/{hsnCode}', [LookupWebController::class, 'updateHsnCode'])->name('hsn-codes.update');
    Route::delete('/hsn-codes/{hsnCode}', [LookupWebController::class, 'destroyHsnCode'])->name('hsn-codes.destroy');

    // Named routes for sidebar — generic lookups
    Route::get('/dosage-forms', [LookupWebController::class, 'genericLookup'])->name('dosage-forms.index')->defaults('type', 'dosage-forms');
    Route::post('/dosage-forms', [LookupWebController::class, 'storeGenericLookup'])->name('dosage-forms.store')->defaults('type', 'dosage-forms');
    Route::put('/dosage-forms/{id}', [LookupWebController::class, 'updateGenericLookup'])->name('dosage-forms.update')->defaults('type', 'dosage-forms');
    Route::delete('/dosage-forms/{id}', [LookupWebController::class, 'destroyGenericLookup'])->name('dosage-forms.destroy')->defaults('type', 'dosage-forms');

    Route::get('/strengths', [LookupWebController::class, 'genericLookup'])->name('strengths.index')->defaults('type', 'strengths');
    Route::post('/strengths', [LookupWebController::class, 'storeGenericLookup'])->name('strengths.store')->defaults('type', 'strengths');
    Route::put('/strengths/{id}', [LookupWebController::class, 'updateGenericLookup'])->name('strengths.update')->defaults('type', 'strengths');
    Route::delete('/strengths/{id}', [LookupWebController::class, 'destroyGenericLookup'])->name('strengths.destroy')->defaults('type', 'strengths');

    Route::get('/pack-sizes', [LookupWebController::class, 'genericLookup'])->name('pack-sizes.index')->defaults('type', 'pack-sizes');
    Route::post('/pack-sizes', [LookupWebController::class, 'storeGenericLookup'])->name('pack-sizes.store')->defaults('type', 'pack-sizes');
    Route::put('/pack-sizes/{id}', [LookupWebController::class, 'updateGenericLookup'])->name('pack-sizes.update')->defaults('type', 'pack-sizes');
    Route::delete('/pack-sizes/{id}', [LookupWebController::class, 'destroyGenericLookup'])->name('pack-sizes.destroy')->defaults('type', 'pack-sizes');

    Route::get('/drug-schedules', [LookupWebController::class, 'genericLookup'])->name('drug-schedules.index')->defaults('type', 'drug-schedules');
    Route::post('/drug-schedules', [LookupWebController::class, 'storeGenericLookup'])->name('drug-schedules.store')->defaults('type', 'drug-schedules');
    Route::put('/drug-schedules/{id}', [LookupWebController::class, 'updateGenericLookup'])->name('drug-schedules.update')->defaults('type', 'drug-schedules');
    Route::delete('/drug-schedules/{id}', [LookupWebController::class, 'destroyGenericLookup'])->name('drug-schedules.destroy')->defaults('type', 'drug-schedules');

    Route::get('/storage-conditions', [LookupWebController::class, 'genericLookup'])->name('storage-conditions.index')->defaults('type', 'storage-conditions');
    Route::post('/storage-conditions', [LookupWebController::class, 'storeGenericLookup'])->name('storage-conditions.store')->defaults('type', 'storage-conditions');
    Route::put('/storage-conditions/{id}', [LookupWebController::class, 'updateGenericLookup'])->name('storage-conditions.update')->defaults('type', 'storage-conditions');
    Route::delete('/storage-conditions/{id}', [LookupWebController::class, 'destroyGenericLookup'])->name('storage-conditions.destroy')->defaults('type', 'storage-conditions');

    // ─── Inventory ─────────────────────────────────────
    Route::get('/inventory', [InventoryWebController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/add-batch', [InventoryWebController::class, 'createBatch'])->name('inventory.create-batch');
    Route::post('/inventory/add-batch', [InventoryWebController::class, 'storeBatch'])->name('inventory.store-batch');

    // ─── Clients ───────────────────────────────────────
    Route::resource('clients', ClientWebController::class);

    // ─── Orders ────────────────────────────────────────
    Route::resource('orders', OrderWebController::class)->only(['index', 'show', 'create', 'store']);
    Route::post('/orders/{order}/approve', [OrderWebController::class, 'approve'])->name('orders.approve');
    Route::post('/orders/{order}/reject', [OrderWebController::class, 'reject'])->name('orders.reject');
    Route::patch('/orders/{order}/status', [OrderWebController::class, 'updateStatus'])->name('orders.update-status');

    // ─── Invoices ──────────────────────────────────────
    Route::get('/invoices', [InvoiceWebController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceWebController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [InvoiceWebController::class, 'downloadPdf'])->name('invoices.pdf');

    // ─── Payments ──────────────────────────────────────
    Route::get('/payments', [PaymentWebController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentWebController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentWebController::class, 'store'])->name('payments.store');

    // ─── Deliveries ────────────────────────────────────
    Route::get('/deliveries', [DeliveryWebController::class, 'index'])->name('deliveries.index');
    Route::get('/delivery-agents', [DeliveryWebController::class, 'agents'])->name('delivery-agents.index');
    Route::post('/delivery-agents', [DeliveryWebController::class, 'storeAgent'])->name('delivery-agents.store');
    Route::put('/delivery-agents/{deliveryAgent}', [DeliveryWebController::class, 'updateAgent'])->name('delivery-agents.update');
    Route::delete('/delivery-agents/{deliveryAgent}', [DeliveryWebController::class, 'destroyAgent'])->name('delivery-agents.destroy');

    // ─── Returns ───────────────────────────────────────
    Route::get('/returns', [AdminWebController::class, 'returns'])->name('returns.index');

    // ─── Reports ───────────────────────────────────────
    Route::get('/reports', [AdminWebController::class, 'reports'])->name('reports.index');
    Route::get('/gst-reports', [AdminWebController::class, 'gstReports'])->name('gst-reports.index');
    Route::get('/bulk-operations', [AdminWebController::class, 'bulkOperations'])->name('bulk.index');

    // ─── Notifications (all authenticated users) ─────
    Route::get('/notifications', [AdminWebController::class, 'notifications'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [AdminWebController::class, 'markNotificationRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [AdminWebController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');

    // ─── Suppliers ─────────────────────────────────────
    Route::get('/suppliers', [SupplierWebController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierWebController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [SupplierWebController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierWebController::class, 'destroy'])->name('suppliers.destroy');

    // ─── Purchase Orders ──────────────────────────────
    Route::get('/purchase-orders', [PurchaseOrderWebController::class, 'index'])->name('purchase-orders.index');

    // ─── Stock Transfers ──────────────────────────────
    Route::get('/stock-transfers', [StockTransferWebController::class, 'index'])->name('stock-transfers.index');

    // ─── Warehouses ───────────────────────────────────
    Route::get('/warehouses', [WarehouseWebController::class, 'index'])->name('warehouses.index');
    Route::post('/warehouses', [WarehouseWebController::class, 'store'])->name('warehouses.store');
    Route::put('/warehouses/{warehouse}', [WarehouseWebController::class, 'update'])->name('warehouses.update');
    Route::delete('/warehouses/{warehouse}', [WarehouseWebController::class, 'destroy'])->name('warehouses.destroy');

    // ─── Admin only ────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [AdminWebController::class, 'users'])->name('users.index');
        Route::post('/users', [AdminWebController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [AdminWebController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/toggle-active', [AdminWebController::class, 'toggleUserActive'])->name('users.toggle-active');
        Route::delete('/users/{user}', [AdminWebController::class, 'destroyUser'])->name('users.destroy');

        Route::get('/roles', [AdminWebController::class, 'roles'])->name('roles.index');
        Route::post('/roles', [AdminWebController::class, 'storeRole'])->name('roles.store');
        Route::post('/roles/{role}/permissions', [AdminWebController::class, 'syncPermissions'])->name('roles.sync-permissions');

        Route::get('/audit-logs', [AdminWebController::class, 'auditLogs'])->name('audit-logs.index');

        Route::get('/settings', [AdminWebController::class, 'settings'])->name('settings.index');
        Route::put('/settings', [AdminWebController::class, 'updateSettings'])->name('settings.update');
    });

    // ─── Vendor Portal ─────────────────────────────────
    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'vendorDashboard'])->name('dashboard');
        Route::get('/place-order', fn() => view('vendor.place-order'))->name('place-order');
        Route::get('/orders', [OrderWebController::class, 'index'])->name('orders');
        Route::get('/orders/{order}', [OrderWebController::class, 'show'])->name('orders.show');
        Route::get('/invoices', [InvoiceWebController::class, 'index'])->name('invoices');
        Route::get('/invoices/{invoice}', [InvoiceWebController::class, 'show'])->name('invoices.show');
        Route::get('/payments', [PaymentWebController::class, 'index'])->name('payments');
        Route::get('/deliveries', [DeliveryWebController::class, 'index'])->name('deliveries');
        Route::get('/returns', [AdminWebController::class, 'returns'])->name('returns');
        Route::get('/ledger', fn() => view('vendor.ledger'))->name('ledger');
        Route::get('/notifications', [AdminWebController::class, 'notifications'])->name('notifications');
    });
});
