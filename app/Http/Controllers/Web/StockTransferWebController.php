<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use Illuminate\Http\Request;

class StockTransferWebController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromWarehouse:id,name,code', 'toWarehouse:id,name,code', 'createdBy:id,full_name']);

        if ($search = $request->get('search')) {
            $query->where('transfer_number', 'like', "%{$search}%");
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $transfers = $query->latest()->paginate(25)->withQueryString();

        return view('stock-transfers.index', compact('transfers'));
    }
}
