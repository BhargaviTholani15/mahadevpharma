<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\WarehouseStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isClient()) {
            return $this->clientDashboard($user);
        }

        return $this->adminDashboard();
    }

    private function adminDashboard(): JsonResponse
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        // Key metrics
        $totalRevenue = Invoice::where('status', '!=', 'CANCELLED')
            ->whereMonth('invoice_date', now()->month)
            ->sum('grand_total');

        $totalOutstanding = Client::sum('current_outstanding');

        $todayOrders = Order::whereDate('created_at', $today)->count();
        $pendingOrders = Order::where('status', 'PENDING')->count();

        $totalProducts = Product::active()->count();
        $totalClients = Client::active()->count();

        // Low stock products
        $lowStock = WarehouseStock::lowStock()
            ->with(['batch.product:id,name,sku', 'warehouse:id,name,code'])
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'product' => $s->batch->product->name,
                'sku' => $s->batch->product->sku,
                'warehouse' => $s->warehouse->code,
                'batch' => $s->batch->batch_number,
                'available' => $s->availableQty(),
                'reorder_level' => $s->reorder_level,
            ]);

        // Expiring soon (next 90 days)
        $expiringSoon = Batch::expiringSoon(90)
            ->with('product:id,name,sku')
            ->active()
            ->limit(10)
            ->get()
            ->map(fn($b) => [
                'product' => $b->product->name,
                'batch' => $b->batch_number,
                'expiry_date' => $b->expiry_date->format('Y-m-d'),
                'days_left' => (int) now()->diffInDays($b->expiry_date),
            ]);

        // Monthly sales trend (last 6 months)
        $salesTrend = Invoice::where('status', '!=', 'CANCELLED')
            ->where('invoice_date', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as month"),
                DB::raw('SUM(grand_total) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Recent orders
        $recentOrders = Order::with(['client:id,business_name', 'createdBy:id,full_name'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'client' => $o->client->business_name,
                'total' => $o->total_amount,
                'status' => $o->status,
                'created_at' => $o->created_at->format('Y-m-d H:i'),
            ]);

        // Payment collections this month
        $monthlyCollections = Payment::where('status', 'CONFIRMED')
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');

        return response()->json([
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'total_outstanding' => $totalOutstanding,
                'monthly_collections' => $monthlyCollections,
                'today_orders' => $todayOrders,
                'pending_orders' => $pendingOrders,
                'total_products' => $totalProducts,
                'total_clients' => $totalClients,
            ],
            'low_stock' => $lowStock,
            'expiring_soon' => $expiringSoon,
            'sales_trend' => $salesTrend,
            'recent_orders' => $recentOrders,
        ]);
    }

    private function clientDashboard($user): JsonResponse
    {
        $client = $user->client;
        if (!$client) {
            return response()->json(['message' => 'Client profile not found'], 404);
        }

        $recentOrders = Order::where('client_id', $client->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'total' => $o->total_amount,
                'status' => $o->status,
                'created_at' => $o->created_at->format('Y-m-d H:i'),
            ]);

        $unpaidInvoices = Invoice::where('client_id', $client->id)
            ->unpaid()
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'invoice_number' => $i->invoice_number,
                'grand_total' => $i->grand_total,
                'balance_due' => $i->balance_due,
                'due_date' => $i->due_date->format('Y-m-d'),
                'is_overdue' => $i->isOverdue(),
            ]);

        return response()->json([
            'metrics' => [
                'credit_limit' => $client->credit_limit,
                'current_outstanding' => $client->current_outstanding,
                'available_credit' => $client->availableCredit(),
                'total_orders' => $client->orders()->count(),
                'pending_orders' => $client->orders()->where('status', 'PENDING')->count(),
            ],
            'recent_orders' => $recentOrders,
            'unpaid_invoices' => $unpaidInvoices,
        ]);
    }
}
