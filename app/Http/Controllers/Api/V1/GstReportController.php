<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GstReportController extends Controller
{
    public function gstr1(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $month = $validated['month'];
        $year = $validated['year'];

        $invoices = Invoice::with(['client:id,business_name,gst_number,state_code', 'items.product:id,name'])
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->whereNotIn('status', ['CANCELLED'])
            ->get();

        // B2B: Invoices to clients with GST number
        $b2b = $invoices->filter(fn($inv) => !empty($inv->client->gst_number))
            ->groupBy(fn($inv) => $inv->client->gst_number)
            ->map(function ($clientInvoices) {
                $client = $clientInvoices->first()->client;
                return [
                    'client_name' => $client->business_name,
                    'gst_number' => $client->gst_number,
                    'invoices' => $clientInvoices->map(fn($inv) => [
                        'invoice_number' => $inv->invoice_number,
                        'invoice_date' => $inv->invoice_date->format('Y-m-d'),
                        'taxable_total' => $inv->taxable_total,
                        'cgst_total' => $inv->cgst_total,
                        'sgst_total' => $inv->sgst_total,
                        'igst_total' => $inv->igst_total,
                        'grand_total' => $inv->grand_total,
                    ])->values(),
                    'total_taxable' => $clientInvoices->sum('taxable_total'),
                    'total_tax' => $clientInvoices->sum('cgst_total') + $clientInvoices->sum('sgst_total') + $clientInvoices->sum('igst_total'),
                ];
            })->values();

        // B2C: Invoices to clients without GST number
        $b2c = $invoices->filter(fn($inv) => empty($inv->client->gst_number))
            ->map(fn($inv) => [
                'invoice_number' => $inv->invoice_number,
                'invoice_date' => $inv->invoice_date->format('Y-m-d'),
                'client_name' => $inv->client->business_name,
                'taxable_total' => $inv->taxable_total,
                'cgst_total' => $inv->cgst_total,
                'sgst_total' => $inv->sgst_total,
                'igst_total' => $inv->igst_total,
                'grand_total' => $inv->grand_total,
            ])->values();

        // HSN Summary
        $hsnSummary = InvoiceItem::whereHas('invoice', function ($q) use ($year, $month) {
                $q->whereYear('invoice_date', $year)
                  ->whereMonth('invoice_date', $month)
                  ->whereNotIn('status', ['CANCELLED']);
            })
            ->select(
                'hsn_code',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(taxable_amount) as total_taxable_value'),
                DB::raw('SUM(cgst_amount) as total_cgst'),
                DB::raw('SUM(sgst_amount) as total_sgst'),
                DB::raw('SUM(igst_amount) as total_igst'),
                DB::raw('SUM(line_total) as total_value'),
            )
            ->groupBy('hsn_code')
            ->orderBy('hsn_code')
            ->get();

        return response()->json([
            'period' => "{$month}/{$year}",
            'b2b' => $b2b,
            'b2c' => $b2c,
            'hsn_summary' => $hsnSummary,
            'totals' => [
                'total_invoices' => $invoices->count(),
                'total_taxable' => $invoices->sum('taxable_total'),
                'total_cgst' => $invoices->sum('cgst_total'),
                'total_sgst' => $invoices->sum('sgst_total'),
                'total_igst' => $invoices->sum('igst_total'),
                'total_value' => $invoices->sum('grand_total'),
            ],
        ]);
    }

    public function gstr3b(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $month = $validated['month'];
        $year = $validated['year'];

        // Outward supplies
        $invoices = Invoice::whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->whereNotIn('status', ['CANCELLED'])
            ->get();

        $outwardTaxable = $invoices->sum('taxable_total');
        $interStateSupplies = $invoices->where('is_inter_state', true)->sum('taxable_total');

        // Inward supplies from registered persons (purchase orders received)
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->whereIn('status', ['RECEIVED', 'PARTIALLY_RECEIVED'])
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->get();

        $inwardFromRegistered = $purchaseOrders->filter(
            fn($po) => !empty($po->supplier->gst_number)
        )->sum('total_amount');

        // Tax liability
        $cgstLiability = $invoices->sum('cgst_total');
        $sgstLiability = $invoices->sum('sgst_total');
        $igstLiability = $invoices->sum('igst_total');

        // ITC available from received purchase orders
        $itcAvailable = $purchaseOrders->sum('tax_amount');

        return response()->json([
            'period' => "{$month}/{$year}",
            'outward_supplies' => [
                'taxable_value' => $outwardTaxable,
                'inter_state_supplies' => $interStateSupplies,
                'intra_state_supplies' => $outwardTaxable - $interStateSupplies,
            ],
            'inward_supplies' => [
                'from_registered_persons' => $inwardFromRegistered,
            ],
            'tax_liability' => [
                'cgst' => $cgstLiability,
                'sgst' => $sgstLiability,
                'igst' => $igstLiability,
                'total' => $cgstLiability + $sgstLiability + $igstLiability,
            ],
            'itc_available' => [
                'total' => $itcAvailable,
            ],
            'net_tax_payable' => [
                'cgst' => max(0, $cgstLiability - ($itcAvailable / 2)),
                'sgst' => max(0, $sgstLiability - ($itcAvailable / 2)),
                'igst' => $igstLiability,
            ],
        ]);
    }

    public function hsnSummary(Request $request): JsonResponse
    {
        $request->mergeIfMissing([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $summary = InvoiceItem::whereHas('invoice', function ($q) use ($validated) {
                $q->whereDate('invoice_date', '>=', $validated['date_from'])
                  ->whereDate('invoice_date', '<=', $validated['date_to'])
                  ->whereNotIn('status', ['CANCELLED']);
            })
            ->select(
                'hsn_code',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(taxable_amount) as total_taxable_value'),
                DB::raw('SUM(cgst_amount) as total_cgst'),
                DB::raw('SUM(sgst_amount) as total_sgst'),
                DB::raw('SUM(igst_amount) as total_igst'),
                DB::raw('SUM(line_total) as total_value'),
            )
            ->groupBy('hsn_code')
            ->orderBy('hsn_code')
            ->get();

        return response()->json([
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'hsn_summary' => $summary,
            'totals' => [
                'total_quantity' => $summary->sum('total_quantity'),
                'total_taxable_value' => $summary->sum('total_taxable_value'),
                'total_cgst' => $summary->sum('total_cgst'),
                'total_sgst' => $summary->sum('total_sgst'),
                'total_igst' => $summary->sum('total_igst'),
                'total_value' => $summary->sum('total_value'),
            ],
        ]);
    }
}
