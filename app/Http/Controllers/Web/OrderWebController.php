<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class OrderWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['client:id,business_name']);

        if (auth()->user()->isClient()) {
            $query->where('client_id', auth()->user()->client?->id);
        }

        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('client', fn($c) => $c->where('business_name', 'like', "%{$search}%")));
        }
        if ($status = $request->get('status')) $query->where('status', $status);
        if ($from = $request->get('from')) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->get('to')) $query->whereDate('created_at', '<=', $to);

        $orders = $query->latest()->paginate(25)->withQueryString();
        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['client', 'items.product', 'invoice', 'createdBy']);
        return view('orders.show', compact('order'));
    }

    public function create()
    {
        $clients = Client::orderBy('business_name')->get(['id', 'business_name']);
        $products = Product::active()->with('hsnCode')->orderBy('name')->get();
        return view('orders.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        // Handle client-side cart (items_json from place-order page)
        if ($request->has('items_json')) {
            $items = json_decode($request->input('items_json'), true);
            if (!is_array($items) || empty($items)) {
                return back()->withErrors(['items' => 'Cart is empty.']);
            }
            $request->merge(['items' => $items]);
            // For client users, auto-set client_id
            if (auth()->user()->isClient()) {
                $request->merge(['client_id' => auth()->user()->client->id]);
            }
        }

        $rules = [
            'items' => 'required|array|min:1',
            'notes' => 'nullable|string|max:1000',
        ];
        if (!auth()->user()->isClient()) {
            $rules['client_id'] = 'required|exists:clients,id';
        }
        $v = $request->validate($rules);

        $clientId = auth()->user()->isClient() ? auth()->user()->client->id : $v['client_id'];

        $warehouse = Warehouse::first();

        // Items may come as batch_id+quantity (from client) or product_id+quantity+unit_price (from admin)
        $itemsData = $v['items'];
        $hasBatchId = isset($itemsData[0]['batch_id']);

        // Resolve batches and calculate totals
        $subtotal = 0;
        $resolvedItems = [];

        foreach ($itemsData as $item) {
            if ($hasBatchId) {
                $batch = \App\Models\Batch::with('product')->find($item['batch_id']);
                if (!$batch) continue;
                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) $batch->selling_price;
                $lineTotal = $price * $qty;
                $resolvedItems[] = [
                    'product_id' => $batch->product_id,
                    'batch_id' => $batch->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'mrp' => (float) $batch->mrp,
                    'taxable_amount' => $lineTotal,
                    'line_total' => $lineTotal,
                ];
            } else {
                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) ($item['unit_price'] ?? 0);
                $lineTotal = $price * $qty;
                $product = Product::with('activeBatches')->find($item['product_id']);
                $resolvedItems[] = [
                    'product_id' => $item['product_id'],
                    'batch_id' => $product?->activeBatches?->first()?->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'mrp' => $product?->mrp ?? $price,
                    'taxable_amount' => $lineTotal,
                    'line_total' => $lineTotal,
                ];
            }
            $subtotal += $lineTotal;
        }

        if (empty($resolvedItems)) {
            return back()->withErrors(['items' => 'No valid items in order.']);
        }

        $order = Order::create([
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad(Order::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
            'client_id' => $clientId,
            'warehouse_id' => $warehouse?->id,
            'created_by' => auth()->id(),
            'status' => 'PENDING',
            'is_credit_order' => $request->boolean('is_credit_order', true),
            'subtotal' => $subtotal,
            'taxable_amount' => $subtotal,
            'total_amount' => $subtotal,
            'notes' => $v['notes'] ?? null,
        ]);

        foreach ($resolvedItems as $item) {
            $order->items()->create($item);
        }

        $redirect = auth()->user()->isClient() ? route('vendor.orders.show', $order) : route('orders.show', $order);
        return redirect($redirect)->with('success', 'Order placed successfully!');
    }

    public function approve(Order $order)
    {
        $order->update(['status' => 'APPROVED', 'approved_at' => now(), 'approved_by' => auth()->id()]);
        return back()->with('success', 'Order approved.');
    }

    public function reject(Request $request, Order $order)
    {
        $order->update(['status' => 'CANCELLED', 'cancelled_by' => auth()->id(), 'cancelled_at' => now(), 'cancellation_reason' => $request->get('reason')]);
        return back()->with('success', 'Order rejected.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $v = $request->validate(['status' => 'required|string|in:PENDING,APPROVED,PACKED,OUT_FOR_DELIVERY,DELIVERED,CANCELLED']);
        $order->update(['status' => $v['status']]);
        return back()->with('success', 'Order status updated.');
    }
}
