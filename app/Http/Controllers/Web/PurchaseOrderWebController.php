<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderWebController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier:id,name', 'warehouse:id,name,code', 'createdBy:id,full_name']);

        if ($search = $request->get('search')) {
            $query->where('po_number', 'like', "%{$search}%");
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $purchaseOrders = $query->latest()->paginate(25)->withQueryString();

        return view('purchase-orders.index', compact('purchaseOrders'));
    }
}
