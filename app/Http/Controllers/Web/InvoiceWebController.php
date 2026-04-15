<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['client:id,business_name', 'order:id,order_number']);
        if (auth()->user()->isClient()) {
            $query->where('client_id', auth()->user()->client?->id);
        }
        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('client', fn($c) => $c->where('business_name', 'like', "%{$search}%")));
        }
        if ($status = $request->get('status')) $query->where('status', $status);
        if ($from = $request->get('from')) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->get('to')) $query->whereDate('created_at', '<=', $to);

        $invoices = $query->latest()->paginate(25)->withQueryString();
        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'order.items.product', 'items.product', 'paymentAllocations.payment']);
        return view('invoices.show', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items.product.hsnCode', 'items.batch', 'order']);
        $company = \App\Models\CompanySetting::first();
        $amountInWords = $this->amountToWords($invoice->grand_total);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice', 'company', 'amountInWords'));
        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    private function amountToWords(float $amount): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $rupees = (int) $amount;
        $paise = round(($amount - $rupees) * 100);

        if ($rupees === 0) return 'Zero Rupees Only';

        $words = '';
        if ($rupees >= 10000000) { $words .= $this->twoDigitWords((int)($rupees / 10000000), $ones, $tens) . ' Crore '; $rupees %= 10000000; }
        if ($rupees >= 100000) { $words .= $this->twoDigitWords((int)($rupees / 100000), $ones, $tens) . ' Lakh '; $rupees %= 100000; }
        if ($rupees >= 1000) { $words .= $this->twoDigitWords((int)($rupees / 1000), $ones, $tens) . ' Thousand '; $rupees %= 1000; }
        if ($rupees >= 100) { $words .= $ones[(int)($rupees / 100)] . ' Hundred '; $rupees %= 100; }
        if ($rupees > 0) { $words .= $this->twoDigitWords($rupees, $ones, $tens); }

        $words = trim($words) . ' Rupees';
        if ($paise > 0) $words .= ' and ' . $this->twoDigitWords((int) $paise, $ones, $tens) . ' Paise';
        return $words . ' Only';
    }

    private function twoDigitWords(int $num, array $ones, array $tens): string
    {
        if ($num < 20) return $ones[$num];
        return $tens[(int)($num / 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
    }
}
