<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'batch_id', 'quantity',
        'unit_price', 'mrp', 'discount_pct', 'discount_amount',
        'taxable_amount', 'cgst_rate', 'cgst_amount', 'sgst_rate',
        'sgst_amount', 'igst_rate', 'igst_amount', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'discount_pct' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'cgst_rate' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_rate' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_rate' => 'decimal:2',
            'igst_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
