<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\HsnCode;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function placeOrder(array $data, int $clientId, int $userId): Order
    {
        return DB::transaction(function () use ($data, $clientId, $userId) {
            $client = Client::lockForUpdate()->findOrFail($clientId);
            $company = CompanySetting::get();

            $isInterState = $company->state_code !== $client->state_code;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'client_id' => $clientId,
                'warehouse_id' => $data['warehouse_id'],
                'status' => 'PENDING',
                'is_credit_order' => $data['is_credit_order'] ?? true,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $subtotal = 0;
            $discountTotal = 0;
            $cgstTotal = 0;
            $sgstTotal = 0;
            $igstTotal = 0;

            foreach ($data['items'] as $item) {
                $batch = Batch::with('product.hsnCode')->findOrFail($item['batch_id']);
                $hsn = $batch->product->hsnCode;

                $qty = $item['quantity'];
                $unitPrice = $batch->selling_price;
                $discountPct = $item['discount_pct'] ?? 0;

                $lineSubtotal = $unitPrice * $qty;
                $discountAmt = round($lineSubtotal * $discountPct / 100, 2);
                $taxableAmt = $lineSubtotal - $discountAmt;

                $cgstRate = $isInterState ? 0 : $hsn->cgst_rate;
                $sgstRate = $isInterState ? 0 : $hsn->sgst_rate;
                $igstRate = $isInterState ? $hsn->igst_rate : 0;

                $cgstAmt = round($taxableAmt * $cgstRate / 100, 2);
                $sgstAmt = round($taxableAmt * $sgstRate / 100, 2);
                $igstAmt = round($taxableAmt * $igstRate / 100, 2);

                $lineTotal = $taxableAmt + $cgstAmt + $sgstAmt + $igstAmt;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $batch->product_id,
                    'batch_id' => $batch->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'mrp' => $batch->mrp,
                    'discount_pct' => $discountPct,
                    'discount_amount' => $discountAmt,
                    'taxable_amount' => $taxableAmt,
                    'cgst_rate' => $cgstRate,
                    'cgst_amount' => $cgstAmt,
                    'sgst_rate' => $sgstRate,
                    'sgst_amount' => $sgstAmt,
                    'igst_rate' => $igstRate,
                    'igst_amount' => $igstAmt,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineSubtotal;
                $discountTotal += $discountAmt;
                $cgstTotal += $cgstAmt;
                $sgstTotal += $sgstAmt;
                $igstTotal += $igstAmt;
            }

            $taxableAmount = $subtotal - $discountTotal;
            $totalAmount = $taxableAmount + $cgstTotal + $sgstTotal + $igstTotal;

            $order->update([
                'subtotal' => $subtotal,
                'discount_amount' => $discountTotal,
                'taxable_amount' => $taxableAmount,
                'cgst_total' => $cgstTotal,
                'sgst_total' => $sgstTotal,
                'igst_total' => $igstTotal,
                'total_amount' => $totalAmount,
            ]);

            // Credit check for credit orders
            if ($order->is_credit_order && !$client->canPlaceOrder($totalAmount)) {
                throw new \Exception('Credit limit exceeded. Available: ' . $client->availableCredit());
            }

            return $order->load('items');
        });
    }

    public function approveOrder(Order $order, int $userId): Order
    {
        return DB::transaction(function () use ($order, $userId) {
            if ($order->status !== 'PENDING') {
                throw new \Exception('Only pending orders can be approved');
            }

            // Reserve stock
            foreach ($order->items as $item) {
                $stock = WarehouseStock::where('warehouse_id', $order->warehouse_id)
                    ->where('batch_id', $item->batch_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($stock->availableQty() < $item->quantity) {
                    throw new \Exception("Insufficient stock for {$item->product->name} batch {$item->batch->batch_number}");
                }

                $stock->increment('reserved_qty', $item->quantity);
            }

            $order->update([
                'status' => 'APPROVED',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            // Generate invoice
            $this->generateInvoice($order);

            return $order->fresh()->load('items', 'invoice');
        });
    }

    public function generateInvoice(Order $order): Invoice
    {
        $client = $order->client;
        $company = CompanySetting::get();
        $isInterState = $company->state_code !== $client->state_code;

        $invoice = Invoice::create([
            'invoice_number' => $company->nextInvoiceNumber(),
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'warehouse_id' => $order->warehouse_id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays($client->credit_period_days)->toDateString(),
            'subtotal' => $order->subtotal,
            'discount_total' => $order->discount_amount,
            'taxable_total' => $order->taxable_amount,
            'cgst_total' => $order->cgst_total,
            'sgst_total' => $order->sgst_total,
            'igst_total' => $order->igst_total,
            'round_off' => 0,
            'grand_total' => $order->total_amount,
            'amount_paid' => 0,
            'balance_due' => $order->total_amount,
            'status' => 'ISSUED',
            'is_inter_state' => $isInterState,
            'supply_state_code' => $company->state_code,
            'billing_state_code' => $client->state_code,
        ]);

        foreach ($order->items as $item) {
            $hsn = $item->product->hsnCode ?? HsnCode::find($item->product->hsn_code_id);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'hsn_code' => $hsn->code,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'mrp' => $item->mrp,
                'discount_pct' => $item->discount_pct,
                'discount_amount' => $item->discount_amount,
                'taxable_amount' => $item->taxable_amount,
                'cgst_rate' => $item->cgst_rate,
                'cgst_amount' => $item->cgst_amount,
                'sgst_rate' => $item->sgst_rate,
                'sgst_amount' => $item->sgst_amount,
                'igst_rate' => $item->igst_rate,
                'igst_amount' => $item->igst_amount,
                'line_total' => $item->line_total,
            ]);
        }

        // Create ledger entry (debit = receivable)
        if ($order->is_credit_order) {
            $lastEntry = LedgerEntry::where('client_id', $order->client_id)
                ->latest('id')->first();

            $runningBalance = ($lastEntry?->running_balance ?? 0) + $order->total_amount;

            LedgerEntry::create([
                'client_id' => $order->client_id,
                'entry_date' => now()->toDateString(),
                'entry_type' => 'INVOICE',
                'debit_amount' => $order->total_amount,
                'credit_amount' => 0,
                'running_balance' => $runningBalance,
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'narration' => "Invoice {$invoice->invoice_number} for Order {$order->order_number}",
                'financial_year' => LedgerEntry::currentFinancialYear(),
                'created_by' => auth()->id() ?? 1,
            ]);

            $client = Client::find($order->client_id);
            $client->update(['current_outstanding' => $runningBalance]);
        }

        return $invoice;
    }

    public function markDelivered(Order $order, int $userId): Order
    {
        return DB::transaction(function () use ($order, $userId) {
            if (!in_array($order->status, ['PACKED', 'OUT_FOR_DELIVERY'])) {
                throw new \Exception('Order must be packed or out for delivery');
            }

            // Deduct stock (release reserved + deduct quantity)
            foreach ($order->items as $item) {
                $stock = WarehouseStock::where('warehouse_id', $order->warehouse_id)
                    ->where('batch_id', $item->batch_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $before = $stock->quantity;
                $stock->decrement('quantity', $item->quantity);
                $stock->decrement('reserved_qty', $item->quantity);

                StockMovement::create([
                    'warehouse_id' => $order->warehouse_id,
                    'batch_id' => $item->batch_id,
                    'movement_type' => 'SALE_OUT',
                    'quantity_change' => -$item->quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $before - $item->quantity,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'performed_by' => $userId,
                ]);
            }

            $order->update(['status' => 'DELIVERED']);

            return $order->fresh();
        });
    }
}
