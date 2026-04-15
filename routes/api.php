<?php

use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\BulkOperationController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\HsnCodeController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CompanySettingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DeliveryAgentController;
use App\Http\Controllers\Api\V1\DeliveryController;
use App\Http\Controllers\Api\V1\GstReportController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseOrderController;
use App\Http\Controllers\Api\V1\RazorpayController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SalesReturnController;
use App\Http\Controllers\Api\V1\StockTransferController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UserManagementController;
use App\Http\Controllers\Api\V1\LookupController;
use App\Http\Controllers\Api\V1\WarehouseController;
use Illuminate\Support\Facades\Route;

// Public auth routes (rate limited)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
});

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/brands', [ProductController::class, 'brands']);
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/hsn-codes', [ProductController::class, 'hsnCodes']);
    Route::get('/products/barcode-lookup', [ProductController::class, 'lookupBarcode']);

    // Lookup lists (dropdown data for product forms)
    Route::get('/lookup/{type}', [LookupController::class, 'index'])
        ->whereIn('type', ['dosage-forms', 'strengths', 'pack-sizes', 'drug-schedules', 'storage-conditions']);

    Route::middleware('role:admin,staff')->group(function () {
        Route::post('/products', [ProductController::class, 'store'])->middleware('permission:products.create');
        Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('permission:products.edit');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.delete');

        // Categories CRUD
        Route::get('/categories/manage', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Brands CRUD
        Route::get('/brands/manage', [BrandController::class, 'index']);
        Route::post('/brands', [BrandController::class, 'store']);
        Route::put('/brands/{brand}', [BrandController::class, 'update']);
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy']);

        // HSN Codes CRUD
        Route::get('/hsn-codes/manage', [HsnCodeController::class, 'index']);
        Route::post('/hsn-codes', [HsnCodeController::class, 'store']);
        Route::put('/hsn-codes/{hsnCode}', [HsnCodeController::class, 'update']);
        Route::delete('/hsn-codes/{hsnCode}', [HsnCodeController::class, 'destroy']);

        // Lookup CRUD (Dosage Forms, Strengths, Pack Sizes, Drug Schedules, Storage Conditions)
        Route::post('/lookup/{type}', [LookupController::class, 'store'])
            ->whereIn('type', ['dosage-forms', 'strengths', 'pack-sizes', 'drug-schedules', 'storage-conditions']);
        Route::put('/lookup/{type}/{id}', [LookupController::class, 'update'])
            ->whereIn('type', ['dosage-forms', 'strengths', 'pack-sizes', 'drug-schedules', 'storage-conditions']);
        Route::delete('/lookup/{type}/{id}', [LookupController::class, 'destroy'])
            ->whereIn('type', ['dosage-forms', 'strengths', 'pack-sizes', 'drug-schedules', 'storage-conditions']);
    });

    // Inventory
    Route::get('/inventory/stocks', [InventoryController::class, 'stocks']);
    Route::get('/inventory/batches', [InventoryController::class, 'batches']);
    Route::get('/inventory/movements', [InventoryController::class, 'movements']);
    Route::get('/inventory/warehouses', [InventoryController::class, 'warehouses']);
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStockAlerts']);
    Route::get('/inventory/expiry-alerts', [InventoryController::class, 'expiryAlerts']);

    Route::middleware('role:admin,staff')->group(function () {
        Route::post('/inventory/batches', [InventoryController::class, 'storeBatch']);
        Route::post('/inventory/adjust', [InventoryController::class, 'adjustStock'])->middleware('permission:inventory.adjust');
    });

    // Razorpay Payments
    Route::post('/razorpay/create-order', [RazorpayController::class, 'createOrder']);
    Route::post('/razorpay/verify-payment', [RazorpayController::class, 'verifyPayment']);

    // My Account (client self-service)
    Route::get('/my-ledger', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if (!$user->isClient() || !$user->client) {
            return response()->json(['message' => 'Not a client account'], 403);
        }
        return app(ClientController::class)->ledger($request, $user->client);
    });
    Route::get('/my-statement', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if (!$user->isClient() || !$user->client) {
            return response()->json(['message' => 'Not a client account'], 403);
        }
        return app(ClientController::class)->statement($user->client);
    });

    // Clients
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/clients', [ClientController::class, 'index']);
        Route::get('/clients/{client}', [ClientController::class, 'show']);
        Route::post('/clients', [ClientController::class, 'store'])->middleware('permission:clients.create');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->middleware('permission:clients.edit');
        Route::post('/clients/{client}/verify-kyc', [ClientController::class, 'verifyKyc'])->middleware('permission:clients.kyc');
        Route::get('/clients/{client}/ledger', [ClientController::class, 'ledger']);
        Route::get('/clients/{client}/statement', [ClientController::class, 'statement']);
    });

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);

    Route::middleware('role:admin,staff')->group(function () {
        Route::post('/orders/{order}/approve', [OrderController::class, 'approve'])->middleware('permission:orders.approve');
        Route::post('/orders/{order}/reject', [OrderController::class, 'reject'])->middleware('permission:orders.approve');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    });

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);
    Route::post('/invoices', [InvoiceController::class, 'store'])->middleware('role:admin,staff');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);

    Route::middleware('role:admin,staff')->group(function () {
        Route::post('/payments', [PaymentController::class, 'store'])->middleware('permission:payments.create');
    });

    // Delivery
    Route::get('/deliveries', [DeliveryController::class, 'index']);

    Route::middleware('role:admin,staff')->group(function () {
        Route::post('/deliveries/assign', [DeliveryController::class, 'assign'])->middleware('permission:delivery.assign');
        Route::patch('/deliveries/{assignment}/status', [DeliveryController::class, 'updateStatus']);

        // Delivery Agents
        Route::get('/delivery-agents', [DeliveryAgentController::class, 'index']);
        Route::get('/delivery-agents/{deliveryAgent}', [DeliveryAgentController::class, 'show']);
        Route::post('/delivery-agents', [DeliveryAgentController::class, 'store']);
        Route::put('/delivery-agents/{deliveryAgent}', [DeliveryAgentController::class, 'update']);
        Route::delete('/delivery-agents/{deliveryAgent}', [DeliveryAgentController::class, 'destroy']);
        Route::post('/delivery-agents/{deliveryAgent}/toggle-available', [DeliveryAgentController::class, 'toggleAvailable']);
    });

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports/sales', [ReportController::class, 'salesReport']);
        Route::get('/reports/outstanding', [ReportController::class, 'outstandingReport']);
        Route::get('/reports/aging', [ReportController::class, 'agingReport']);
        Route::get('/reports/collections', [ReportController::class, 'collectionReport']);
        Route::get('/reports/product-performance', [ReportController::class, 'productPerformance']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // ─── Admin-only Enterprise Routes ───────────────────────────────────────

    Route::middleware('role:admin')->group(function () {
        // User Management
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::get('/users/{user}', [UserManagementController::class, 'show']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::put('/users/{user}', [UserManagementController::class, 'update']);
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);
        Route::post('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive']);

        // Roles & Permissions
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{role}', [RoleController::class, 'show']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{role}', [RoleController::class, 'update']);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
        Route::post('/roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
        Route::get('/permissions', [RoleController::class, 'permissions']);

        // Warehouse Management
        Route::get('/warehouses', [WarehouseController::class, 'index']);
        Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show']);
        Route::post('/warehouses', [WarehouseController::class, 'store']);
        Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update']);
        Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy']);
        Route::get('/warehouses/{warehouse}/stock-summary', [WarehouseController::class, 'stockSummary']);

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{log}', [AuditLogController::class, 'show']);

        // Company Settings
        Route::get('/settings', [CompanySettingController::class, 'show']);
        Route::put('/settings', [CompanySettingController::class, 'update']);
    });

    // ─��─ Enterprise Operations (Admin & Staff) ─────────────────────────────

    Route::middleware('role:admin,staff')->group(function () {
        // Suppliers
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
        Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('permission:suppliers.create');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('permission:suppliers.edit');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers.delete');

        // Purchase Orders
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->middleware('permission:purchase_orders.create');
        Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
        Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->middleware('permission:purchase_orders.approve');
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);

        // Sales Returns
        Route::get('/sales-returns', [SalesReturnController::class, 'index']);
        Route::get('/sales-returns/{salesReturn}', [SalesReturnController::class, 'show']);
        Route::post('/sales-returns', [SalesReturnController::class, 'store']);
        Route::post('/sales-returns/{salesReturn}/approve', [SalesReturnController::class, 'approve'])->middleware('permission:returns.approve');
        Route::post('/sales-returns/{salesReturn}/receive', [SalesReturnController::class, 'receive']);
        Route::post('/sales-returns/{salesReturn}/reject', [SalesReturnController::class, 'reject']);
        Route::post('/sales-returns/{salesReturn}/credit-note', [SalesReturnController::class, 'issueCreditNote']);

        // Stock Transfers
        Route::get('/stock-transfers', [StockTransferController::class, 'index']);
        Route::get('/stock-transfers/{stockTransfer}', [StockTransferController::class, 'show']);
        Route::post('/stock-transfers', [StockTransferController::class, 'store']);
        Route::post('/stock-transfers/{stockTransfer}/approve', [StockTransferController::class, 'approve'])->middleware('permission:transfers.approve');
        Route::post('/stock-transfers/{stockTransfer}/ship', [StockTransferController::class, 'ship']);
        Route::post('/stock-transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive']);
        Route::post('/stock-transfers/{stockTransfer}/cancel', [StockTransferController::class, 'cancel']);

        // Bulk Operations
        Route::get('/bulk/export/products', [BulkOperationController::class, 'exportProducts']);
        Route::get('/bulk/export/clients', [BulkOperationController::class, 'exportClients']);
        Route::get('/bulk/export/orders', [BulkOperationController::class, 'exportOrders']);
        Route::post('/bulk/import/products', [BulkOperationController::class, 'importProducts'])->middleware('permission:bulk.import');
        Route::post('/bulk/import/clients', [BulkOperationController::class, 'importClients'])->middleware('permission:bulk.import');

        // GST Reports
        Route::get('/gst/gstr1', [GstReportController::class, 'gstr1']);
        Route::get('/gst/gstr3b', [GstReportController::class, 'gstr3b']);
        Route::get('/gst/hsn-summary', [GstReportController::class, 'hsnSummary']);
    });
});
