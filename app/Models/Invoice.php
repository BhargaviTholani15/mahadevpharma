<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'order_id', 'client_id', 'warehouse_id',
        'invoice_date', 'due_date', 'subtotal', 'discount_total',
        'taxable_total', 'cgst_total', 'sgst_total', 'igst_total',
        'round_off', 'grand_total', 'amount_paid', 'balance_due',
        'status', 'is_inter_state', 'supply_state_code',
        'billing_state_code', 'cancelled_at', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'taxable_total' => 'decimal:2',
            'cgst_total' => 'decimal:2',
            'sgst_total' => 'decimal:2',
            'igst_total' => 'decimal:2',
            'round_off' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'is_inter_state' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function isPaid(): bool
    {
        return $this->balance_due <= 0;
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->due_date->isPast();
    }

    public function overdueDays(): int
    {
        if (!$this->isOverdue()) return 0;
        return (int) $this->due_date->diffInDays(now());
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', ['PAID', 'CANCELLED']);
    }

    public function scopeOverdue($query)
    {
        return $query->unpaid()->where('due_date', '<', now());
    }
}
