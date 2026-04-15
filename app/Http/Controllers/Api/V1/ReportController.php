<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function salesReport(Request $request): JsonResponse
    {
        $from = $request->get('from_date', now()->startOfMonth()->toDateString());
        $to = $request->get('to_date', now()->toDateString());

        $sales = Invoice::where('status', '!=', 'CANCELLED')
            ->whereBetween('invoice_date', [$from, $to])
            ->select(
                DB::raw("DATE_FORMAT(invoice_date, '%Y-%m-%d') as date"),
                DB::raw('SUM(grand_total) as revenue'),
                DB::raw('SUM(taxable_total) as taxable'),
                DB::raw('SUM(cgst_total + sgst_total + igst_total) as tax'),
                DB::raw('COUNT(*) as invoice_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = Invoice::where('status', '!=', 'CANCELLED')
            ->whereBetween('invoice_date', [$from, $to])
            ->selectRaw('SUM(grand_total) as total_revenue, SUM(taxable_total) as total_taxable, COUNT(*) as total_invoices')
            ->first();

        return response()->json([
            'period' => ['from' => $from, 'to' => $to],
            'summary' => $summary,
            'daily' => $sales,
        ]);
    }

    public function outstandingReport(): JsonResponse
    {
        $clients = Client::where('current_outstanding', '>', 0)
            ->with('user:id,full_name,phone')
            ->orderByDesc('current_outstanding')
            ->get()
            ->map(fn($c) => [
                'client_id' => $c->id,
                'business_name' => $c->business_name,
                'phone' => $c->user->phone,
                'credit_limit' => $c->credit_limit,
                'outstanding' => $c->current_outstanding,
                'available_credit' => $c->availableCredit(),
                'utilization_pct' => $c->credit_limit > 0
                    ? round(($c->current_outstanding / $c->credit_limit) * 100, 1)
                    : 0,
            ]);

        $totalOutstanding = $clients->sum('outstanding');

        return response()->json([
            'total_outstanding' => $totalOutstanding,
            'clients' => $clients,
        ]);
    }

    public function agingReport(): JsonResponse
    {
        $aging = DB::table('clients as c')
            ->join('invoices as i', 'i.client_id', '=', 'c.id')
            ->whereNotIn('i.status', ['CANCELLED', 'PAID'])
            ->groupBy('c.id', 'c.business_name')
            ->havingRaw('SUM(i.balance_due) > 0')
            ->orderByDesc(DB::raw('SUM(i.balance_due)'))
            ->select([
                'c.id as client_id',
                'c.business_name',
                DB::raw("SUM(CASE WHEN i.balance_due > 0 AND DATEDIFF(CURDATE(), i.due_date) < 0 THEN i.balance_due ELSE 0 END) as not_due"),
                DB::raw("SUM(CASE WHEN i.balance_due > 0 AND DATEDIFF(CURDATE(), i.due_date) BETWEEN 0 AND 30 THEN i.balance_due ELSE 0 END) as bucket_0_30"),
                DB::raw("SUM(CASE WHEN i.balance_due > 0 AND DATEDIFF(CURDATE(), i.due_date) BETWEEN 31 AND 60 THEN i.balance_due ELSE 0 END) as bucket_31_60"),
                DB::raw("SUM(CASE WHEN i.balance_due > 0 AND DATEDIFF(CURDATE(), i.due_date) BETWEEN 61 AND 90 THEN i.balance_due ELSE 0 END) as bucket_61_90"),
                DB::raw("SUM(CASE WHEN i.balance_due > 0 AND DATEDIFF(CURDATE(), i.due_date) > 90 THEN i.balance_due ELSE 0 END) as bucket_90_plus"),
                DB::raw('SUM(i.balance_due) as total'),
            ])
            ->get();

        return response()->json(['aging' => $aging]);
    }

    public function collectionReport(Request $request): JsonResponse
    {
        $from = $request->get('from_date', now()->startOfMonth()->toDateString());
        $to = $request->get('to_date', now()->toDateString());

        $collections = Payment::where('status', 'CONFIRMED')
            ->whereBetween('payment_date', [$from, $to])
            ->select(
                'payment_method',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('payment_method')
            ->get();

        $total = $collections->sum('total');

        return response()->json([
            'period' => ['from' => $from, 'to' => $to],
            'total' => $total,
            'by_method' => $collections,
        ]);
    }

    public function productPerformance(Request $request): JsonResponse
    {
        $from = $request->get('from_date', now()->startOfMonth()->toDateString());
        $to = $request->get('to_date', now()->toDateString());

        $products = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('products as p', 'p.id', '=', 'ii.product_id')
            ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
            ->where('i.status', '!=', 'CANCELLED')
            ->whereBetween('i.invoice_date', [$from, $to])
            ->select(
                'p.id', 'p.name', 'p.sku', 'b.name as brand',
                DB::raw('SUM(ii.quantity) as total_qty'),
                DB::raw('SUM(ii.line_total) as total_revenue'),
                DB::raw('COUNT(DISTINCT i.id) as invoice_count')
            )
            ->groupBy('p.id', 'p.name', 'p.sku', 'b.name')
            ->orderByDesc('total_revenue')
            ->limit(50)
            ->get();

        return response()->json(['products' => $products]);
    }
}
