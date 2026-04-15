<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'client_id', 'warehouse_id', 'status',
        'subtotal', 'discount_amount', 'taxable_amount',
        'cgst_total', 'sgst_total', 'igst_total', 'total_amount',
        'is_credit_order', 'notes', 'approved_by', 'approved_at',
        'packed_by', 'packed_at', 'cancelled_by', 'cancelled_at',
        'cancellation_reason', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'cgst_total' => 'decimal:2',
            'sgst_total' => 'decimal:2',
            'igst_total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'is_credit_order' => 'boolean',
            'approved_at' => 'datetime',
            'packed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
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
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function deliveryAssignment(): HasOne
    {
        return $this->hasOne(DeliveryAssignment::class)->latest();
    }

    public function deliveryAssignments(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = static::whereDate('created_at', today())->latest('id')->first();
        $seq = $lastOrder ? ((int) substr($lastOrder->order_number, -4)) + 1 : 1;
        return "ORD-{$date}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
