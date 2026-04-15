<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['client:id,business_name', 'warehouse:id,name,code']);

        $user = $request->user();
        if ($user->isClient()) {
            $query->where('client_id', $user->client->id);
        }

        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        if ($from = $request->get('from_date')) {
            $query->whereDate('invoice_date', '>=', $from);
        }

        if ($to = $request->get('to_date')) {
            $query->whereDate('invoice_date', '<=', $to);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('business_name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->latest('invoice_date')->paginate($request->get('per_page', 25));

        return response()->json($invoices);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load([
            'client.user:id,full_name,phone',
            'warehouse', 'order:id,order_number',
            'items.product:id,name,sku',
            'items.batch:id,batch_number',
            'paymentAllocations.payment:id,payment_number,payment_date,payment_method',
        ]);

        return response()->json(['invoice' => $invoice]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $client = Client::findOrFail($validated['client_id']);
            $company = CompanySetting::get();
            $warehouse = Warehouse::first();
            $isInterState = $company->state_code !== $client->state_code;

            $subtotal = 0;
            $discountTotal = 0;
            $cgstTotal = 0;
            $sgstTotal = 0;
            $igstTotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $batch = Batch::with('product.hsnCode')->findOrFail($item['batch_id']);
                $hsn = $batch->product->hsnCode;

                $qty = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discPct = $item['discount_pct'] ?? 0;

                $lineSub = $unitPrice * $qty;
                $discAmt = round($lineSub * $discPct / 100, 2);
                $taxableAmt = $lineSub - $discAmt;

                $cgstRate = $isInterState ? 0 : (float) $hsn->cgst_rate;
                $sgstRate = $isInterState ? 0 : (float) $hsn->sgst_rate;
                $igstRate = $isInterState ? (float) $hsn->igst_rate : 0;

                $cgstAmt = round($taxableAmt * $cgstRate / 100, 2);
                $sgstAmt = round($taxableAmt * $sgstRate / 100, 2);
                $igstAmt = round($taxableAmt * $igstRate / 100, 2);
                $lineTotal = $taxableAmt + $cgstAmt + $sgstAmt + $igstAmt;

                $subtotal += $lineSub;
                $discountTotal += $discAmt;
                $cgstTotal += $cgstAmt;
                $sgstTotal += $sgstAmt;
                $igstTotal += $igstAmt;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'hsn_code' => $hsn->code,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'mrp' => $batch->mrp,
                    'discount_pct' => $discPct,
                    'discount_amount' => $discAmt,
                    'taxable_amount' => $taxableAmt,
                    'cgst_rate' => $cgstRate,
                    'cgst_amount' => $cgstAmt,
                    'sgst_rate' => $sgstRate,
                    'sgst_amount' => $sgstAmt,
                    'igst_rate' => $igstRate,
                    'igst_amount' => $igstAmt,
                    'line_total' => $lineTotal,
                ];
            }

            $taxableTotal = $subtotal - $discountTotal;
            $grandTotal = $taxableTotal + $cgstTotal + $sgstTotal + $igstTotal;

            $invoice = Invoice::create([
                'invoice_number' => $company->nextInvoiceNumber(),
                'order_id' => 0,
                'client_id' => $client->id,
                'warehouse_id' => $warehouse->id ?? 1,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'taxable_total' => $taxableTotal,
                'cgst_total' => $cgstTotal,
                'sgst_total' => $sgstTotal,
                'igst_total' => $igstTotal,
                'round_off' => 0,
                'grand_total' => $grandTotal,
                'amount_paid' => 0,
                'balance_due' => $grandTotal,
                'status' => 'ISSUED',
                'is_inter_state' => $isInterState,
                'supply_state_code' => $company->state_code,
                'billing_state_code' => $client->state_code,
            ]);

            foreach ($itemsData as $iData) {
                InvoiceItem::create(array_merge($iData, ['invoice_id' => $invoice->id]));
            }

            // Ledger entry
            $lastEntry = LedgerEntry::where('client_id', $client->id)->latest('id')->first();
            $runningBalance = ($lastEntry?->running_balance ?? 0) + $grandTotal;

            LedgerEntry::create([
                'client_id' => $client->id,
                'entry_date' => $validated['invoice_date'],
                'entry_type' => 'INVOICE',
                'debit_amount' => $grandTotal,
                'credit_amount' => 0,
                'running_balance' => $runningBalance,
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'narration' => "Invoice {$invoice->invoice_number}",
                'financial_year' => LedgerEntry::currentFinancialYear(),
                'created_by' => $request->user()->id,
            ]);

            $client->update(['current_outstanding' => $runningBalance]);

            ActivityLog::log('invoice.created', 'invoice', $invoice->id);

            return response()->json(['invoice' => $invoice->load('items')], 201);
        });
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load([
            'client.user:id,full_name,phone',
            'warehouse', 'items.product:id,name,sku,generic_name',
            'items.batch:id,batch_number,expiry_date',
        ]);

        $company = CompanySetting::get();

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $company,
            'amountInWords' => $this->numberToWords($invoice->grand_total),
        ]);

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    private function numberToWords(float $number): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $convert = function (int $n) use (&$convert, $ones, $tens): string {
            if ($n < 20) return $ones[$n];
            if ($n < 100) return $tens[(int)($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
            if ($n < 1000) return $ones[(int)($n / 100)] . ' Hundred' . ($n % 100 ? ' and ' . $convert($n % 100) : '');
            if ($n < 100000) return $convert((int)($n / 1000)) . ' Thousand' . ($n % 1000 ? ' ' . $convert($n % 1000) : '');
            if ($n < 10000000) return $convert((int)($n / 100000)) . ' Lakh' . ($n % 100000 ? ' ' . $convert($n % 100000) : '');
            return $convert((int)($n / 10000000)) . ' Crore' . ($n % 10000000 ? ' ' . $convert($n % 10000000) : '');
        };

        $rupees = (int) floor($number);
        $paise = (int) round(($number - $rupees) * 100);
        $result = $convert($rupees) . ' Rupees';
        if ($paise > 0) $result .= ' and ' . $convert($paise) . ' Paise';
        return $result . ' Only';
    }
}
