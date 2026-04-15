<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Client;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Batch;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Product::active()->count(),
            'clients' => Client::count(),
            'orders_pending' => Order::where('status', 'PENDING')->count(),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'revenue_month' => Invoice::where('status', 'PAID')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('grand_total'),
            'outstanding' => Invoice::whereIn('status', ['ISSUED', 'PARTIALLY_PAID'])->sum('balance_due'),
            'low_stock' => Product::lowStock(50)->count(),
            'expiring_soon' => Batch::where('expiry_date', '<=', now()->addDays(90))
                ->where('expiry_date', '>', now())
                ->count(),
        ];

        $recentOrders = Order::with(['client:id,business_name'])
            ->latest()->take(10)->get();

        return view('dashboard.index', compact('stats', 'recentOrders'));
    }

    public function vendorDashboard()
    {
        $user = auth()->user();
        $client = $user->client;

        $stats = [
            'orders_total' => $client ? Order::where('client_id', $client->id)->count() : 0,
            'orders_pending' => $client ? Order::where('client_id', $client->id)->where('status', 'PENDING')->count() : 0,
            'outstanding' => $client ? Invoice::where('client_id', $client->id)->whereIn('status', ['ISSUED', 'PARTIALLY_PAID'])->sum('balance_due') : 0,
        ];

        $recentOrders = $client
            ? Order::where('client_id', $client->id)->latest()->take(10)->get()
            : collect();

        return view('vendor.dashboard', compact('stats', 'recentOrders'));
    }
}
