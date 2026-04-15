<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;

class InventoryWebController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'stocks');
        $search = $request->get('search');

        $data = ['tab' => $tab, 'search' => $search];

        if ($tab === 'stocks') {
            $query = Product::with(['activeBatches.warehouseStocks'])->active();
            if ($search) $query->where('name', 'like', "%{$search}%");
            $data['items'] = $query->orderBy('name')->paginate(25)->withQueryString();
        } elseif ($tab === 'batches') {
            $query = Batch::with(['product:id,name,sku', 'warehouseStocks.warehouse:id,name']);
            if ($search) $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
            $data['items'] = $query->orderBy('expiry_date')->paginate(25)->withQueryString();
        } elseif ($tab === 'movements') {
            $query = StockMovement::with(['product:id,name', 'batch:id,batch_number', 'warehouse:id,name', 'user:id,full_name']);
            if ($search) $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
            $data['items'] = $query->latest()->paginate(25)->withQueryString();
        } elseif ($tab === 'low-stock') {
            $data['items'] = Product::lowStock(50)->with(['activeBatches.warehouseStocks'])->orderBy('name')->paginate(25)->withQueryString();
        } elseif ($tab === 'expiry') {
            $days = $request->get('days', 90);
            $data['items'] = Batch::with(['product:id,name', 'warehouseStocks.warehouse:id,name'])
                ->where('expiry_date', '<=', now()->addDays($days))
                ->where('expiry_date', '>', now())
                ->orderBy('expiry_date')
                ->paginate(25)->withQueryString();
            $data['days'] = $days;
        }

        return view('inventory.index', $data);
    }

    public function createBatch()
    {
        return view('inventory.create-batch', [
            'products' => Product::active()->orderBy('name')->get(['id', 'name', 'sku']),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function storeBatch(Request $request)
    {
        $v = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'required|string|max:50',
            'mfg_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
            'purchase_price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $batch = Batch::create([
            'product_id' => $v['product_id'],
            'batch_number' => $v['batch_number'],
            'mfg_date' => $v['mfg_date'] ?? null,
            'expiry_date' => $v['expiry_date'],
            'purchase_price' => $v['purchase_price'],
            'mrp' => $v['mrp'],
            'selling_price' => $v['selling_price'],
        ]);

        WarehouseStock::create([
            'batch_id' => $batch->id,
            'warehouse_id' => $v['warehouse_id'],
            'quantity' => $v['quantity'],
            'reserved_qty' => 0,
        ]);

        StockMovement::create([
            'product_id' => $v['product_id'],
            'batch_id' => $batch->id,
            'warehouse_id' => $v['warehouse_id'],
            'type' => 'IN',
            'reason' => 'purchase',
            'quantity' => $v['quantity'],
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('inventory.index', ['tab' => 'batches'])->with('success', 'Batch added.');
    }
}
