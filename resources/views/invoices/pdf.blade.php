<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #222; }

        .invoice-box { border: 1px solid #333; }

        /* Header */
        .header { border-bottom: 1px solid #333; padding: 12px 15px; }
        .header-title { text-align: center; font-size: 14px; font-weight: bold; letter-spacing: 2px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 6px; }
        .header-row { display: table; width: 100%; }
        .header-left, .header-right { display: table-cell; width: 50%; vertical-align: top; }
        .company-name { font-size: 14px; font-weight: bold; color: #1a7a2e; }
        .company-info { font-size: 9px; color: #555; line-height: 1.6; margin-top: 3px; }
        .meta-table { font-size: 9px; }
        .meta-table td { padding: 2px 0; }
        .meta-label { color: #666; text-align: right; padding-right: 8px; }
        .meta-value { font-weight: bold; }

        /* Two column sections */
        .two-col { display: table; width: 100%; border-bottom: 1px solid #333; }
        .col-left, .col-right { display: table-cell; width: 50%; vertical-align: top; padding: 10px 15px; }
        .col-left { border-right: 1px solid #333; }
        .section-label { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #888; letter-spacing: 1px; margin-bottom: 5px; }
        .party-name { font-size: 12px; font-weight: bold; margin-bottom: 3px; }
        .party-info { font-size: 9px; color: #555; line-height: 1.6; }
        .info-row { font-size: 9px; margin-top: 2px; }
        .info-label { color: #888; }
        .info-value { font-weight: bold; }

        /* Supply bar */
        .supply-bar { border-bottom: 1px solid #333; padding: 5px 15px; font-size: 9px; display: table; width: 100%; }
        .supply-bar .cell { display: table-cell; width: 50%; }
        .supply-bar .cell.right { text-align: right; }

        /* Items table */
        table.items { width: 100%; border-collapse: collapse; }
        table.items th {
            background: #f0f0f0;
            border: 1px solid #333;
            padding: 5px 4px;
            text-align: center;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
        }
        table.items td {
            border: 1px solid #ccc;
            padding: 4px;
            font-size: 9px;
            vertical-align: middle;
        }
        table.items td.num { text-align: right; }
        table.items td.ctr { text-align: center; }
        table.items .product-name { font-weight: bold; font-size: 9px; }
        table.items .generic { font-size: 7px; color: #888; }

        /* Totals */
        .totals-section { display: table; width: 100%; border-top: 1px solid #333; }
        .totals-left, .totals-right { display: table-cell; width: 50%; vertical-align: top; }
        .totals-left { border-right: 1px solid #333; padding: 10px 15px; }
        .totals-right { padding: 8px 15px; }
        .words-label { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #888; margin-bottom: 3px; }
        .words-text { font-size: 9px; font-style: italic; line-height: 1.5; }
        .totals-table { width: 100%; }
        .totals-table td { padding: 3px 0; font-size: 9px; }
        .totals-table .label { color: #666; }
        .totals-table .value { text-align: right; font-weight: bold; }
        .grand-total-row td { border-top: 2px solid #333; font-size: 12px; padding-top: 6px; }

        /* Footer */
        .footer-section { display: table; width: 100%; border-top: 1px solid #333; }
        .footer-left, .footer-right { display: table-cell; width: 50%; vertical-align: top; padding: 10px 15px; }
        .footer-left { border-right: 1px solid #333; }
        .terms-title { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #888; margin-bottom: 4px; }
        .terms-list { font-size: 8px; color: #666; line-height: 1.6; }
        .sign-area { text-align: right; }
        .sign-for { font-size: 9px; font-weight: bold; margin-bottom: 40px; }
        .sign-line { font-size: 8px; color: #666; border-top: 1px solid #333; display: inline-block; padding-top: 3px; }

        .computer-gen { text-align: center; font-size: 7px; color: #aaa; padding: 4px; border-top: 1px solid #ccc; }
    </style>
</head>
<body>
<div class="invoice-box">

    <!-- Header -->
    <div class="header">
        <div class="header-title">TAX INVOICE</div>
        <div class="header-row">
            <div class="header-left">
                @if(file_exists(public_path('logo.png')))
                <img src="{{ public_path('logo.png') }}" style="height: 35px; margin-bottom: 5px;" alt="Logo">
                @endif
                <div class="company-name">{{ $company->company_name ?? 'Mahadev Pharma' }}</div>
                <div class="company-info">
                    @if($company->address_line1){{ $company->address_line1 }}<br>@endif
                    {{ $company->city ?? '' }}, {{ $company->state ?? '' }} {{ $company->pincode ? '- '.$company->pincode : '' }}<br>
                    @if($company->phone)Ph: {{ $company->phone }}<br>@endif
                    @if($company->email)Email: {{ $company->email }}@endif
                </div>
            </div>
            <div class="header-right">
                <table class="meta-table" style="float: right;">
                    <tr><td class="meta-label">Invoice No:</td><td class="meta-value">{{ $invoice->invoice_number }}</td></tr>
                    <tr><td class="meta-label">Date:</td><td class="meta-value">{{ $invoice->invoice_date->format('d/m/Y') }}</td></tr>
                    <tr><td class="meta-label">Due Date:</td><td class="meta-value">{{ $invoice->due_date->format('d/m/Y') }}</td></tr>
                    <tr><td class="meta-label">State Code:</td><td class="meta-value">{{ $company->state_code ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Seller + Buyer -->
    <div class="two-col">
        <div class="col-left">
            <div class="section-label">Seller Details</div>
            <div class="info-row"><span class="info-label">GSTIN:</span> <span class="info-value">{{ $company->gst_number ?? '-' }}</span></div>
            <div class="info-row"><span class="info-label">DL No:</span> <span class="info-value">{{ $company->drug_license_no ?? '-' }}</span></div>
        </div>
        <div class="col-right">
            <div class="section-label">Bill To / Party</div>
            <div class="party-name">{{ $invoice->client->business_name }}</div>
            <div class="party-info">
                {{ $invoice->client->address_line1 ?? '' }}<br>
                {{ $invoice->client->city }}, {{ $invoice->client->state }} - {{ $invoice->client->pincode }}
            </div>
            @if($invoice->client->gst_number)
            <div class="info-row"><span class="info-label">GSTIN:</span> <span class="info-value">{{ $invoice->client->gst_number }}</span></div>
            @endif
            <div class="info-row"><span class="info-label">DL No:</span> <span class="info-value">{{ $invoice->client->drug_license_no }}</span></div>
            <div class="info-row"><span class="info-label">State Code:</span> <span class="info-value">{{ $invoice->billing_state_code }}</span></div>
        </div>
    </div>

    <!-- Supply Type -->
    <div class="supply-bar">
        <div class="cell"><span class="info-label">Supply Type:</span> <span class="info-value">{{ $invoice->is_inter_state ? 'Inter-State (IGST)' : 'Intra-State (CGST + SGST)' }}</span></div>
        <div class="cell right"><span class="info-label">Place of Supply:</span> <span class="info-value">{{ $invoice->billing_state_code }}</span></div>
    </div>

    <!-- Product Table -->
    <table class="items">
        <thead>
            <tr>
                <th style="width:20px">S.No</th>
                <th style="width:auto">Product Name</th>
                <th>HSN</th>
                <th>Batch</th>
                <th>Exp</th>
                <th>Qty</th>
                <th>MRP</th>
                <th>Rate</th>
                <th>Disc%</th>
                <th>GST%</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            @php
                $gstPct = $invoice->is_inter_state ? $item->igst_rate : ($item->cgst_rate + $item->sgst_rate);
            @endphp
            <tr>
                <td class="ctr">{{ $i + 1 }}</td>
                <td>
                    <span class="product-name">{{ $item->product->name }}</span>
                    @if($item->product->generic_name)<br><span class="generic">{{ $item->product->generic_name }}</span>@endif
                </td>
                <td class="ctr">{{ $item->hsn_code ?? $item->product?->hsnCode?->code ?? '-' }}</td>
                <td class="ctr" style="font-family: monospace; font-size: 8px;">{{ $item->batch?->batch_number ?? '-' }}</td>
                <td class="ctr">{{ $item->batch?->expiry_date ? \Carbon\Carbon::parse($item->batch->expiry_date)->format('m/y') : '-' }}</td>
                <td class="ctr">{{ $item->quantity }}</td>
                <td class="num">{{ number_format($item->mrp, 2) }}</td>
                <td class="num">{{ number_format($item->unit_price, 2) }}</td>
                <td class="ctr">{{ ($item->discount_pct ?? ($item->discount_amount > 0 ? round($item->discount_amount / ($item->unit_price * $item->quantity) * 100, 1) : 0)) > 0 ? ($item->discount_pct ?? round($item->discount_amount / ($item->unit_price * $item->quantity) * 100, 1)).'%' : '-' }}</td>
                <td class="ctr">{{ $gstPct }}%</td>
                <td class="num" style="font-weight:bold">{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <div class="totals-left">
            <div class="words-label">Amount in Words</div>
            <div class="words-text">{{ $amountInWords ?? '' }}</div>

            <div style="margin-top: 12px;">
                <div class="words-label">Summary</div>
                <div style="font-size: 9px; color: #555;">
                    Total Items: {{ count($invoice->items) }} |
                    Total Qty: {{ $invoice->items->sum('quantity') }}
                </div>
            </div>
        </div>
        <div class="totals-right">
            <table class="totals-table">
                <tr><td class="label">Subtotal</td><td class="value">{{ number_format($invoice->subtotal, 2) }}</td></tr>
                @if($invoice->discount_total > 0)
                <tr><td class="label">Discount</td><td class="value">-{{ number_format($invoice->discount_total, 2) }}</td></tr>
                @endif
                <tr><td class="label">Taxable Amount</td><td class="value">{{ number_format($invoice->taxable_total, 2) }}</td></tr>
                @if(!$invoice->is_inter_state)
                <tr><td class="label">CGST</td><td class="value">{{ number_format($invoice->cgst_total, 2) }}</td></tr>
                <tr><td class="label">SGST</td><td class="value">{{ number_format($invoice->sgst_total, 2) }}</td></tr>
                @else
                <tr><td class="label">IGST</td><td class="value">{{ number_format($invoice->igst_total, 2) }}</td></tr>
                @endif
                @if($invoice->round_off != 0)
                <tr><td class="label">Round Off</td><td class="value">{{ number_format($invoice->round_off, 2) }}</td></tr>
                @endif
                <tr class="grand-total-row">
                    <td style="font-weight:bold">Grand Total</td>
                    <td class="value" style="font-size: 13px;">&#8377; {{ number_format($invoice->grand_total, 2) }}</td>
                </tr>
                <tr><td class="label">Paid</td><td class="value" style="color: #16a34a;">{{ number_format($invoice->amount_paid, 2) }}</td></tr>
                <tr><td class="label" style="font-weight:bold">Balance Due</td><td class="value" style="color: {{ $invoice->balance_due > 0 ? '#dc2626' : '#16a34a' }}; font-size: 11px;">&#8377; {{ number_format($invoice->balance_due, 2) }}</td></tr>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-section">
        <div class="footer-left">
            <div class="terms-title">Terms & Conditions</div>
            <div class="terms-list">
                1. Goods once sold will not be taken back<br>
                2. Subject to local jurisdiction only<br>
                3. Payment due within credit period<br>
                4. E. & O.E.
            </div>
        </div>
        <div class="footer-right">
            <div class="sign-area">
                <div class="sign-for">For {{ $company->company_name ?? 'Mahadev Pharma' }}</div>
                <div class="sign-line">Authorised Signatory</div>
            </div>
        </div>
    </div>

    <div class="computer-gen">
        This is a computer generated invoice and does not require a physical signature.
    </div>

</div>
</body>
</html>
