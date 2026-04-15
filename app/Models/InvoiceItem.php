<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'invoice_id', 'order_item_id', 'product_id', 'batch_id',
        'hsn_code', 'quantity', 'unit_price', 'mrp', 'discount_pct',
        'discount_amount', 'taxable_amount', 'cgst_rate', 'cgst_amount',
        'sgst_rate', 'sgst_amount', 'igst_rate', 'igst_amount', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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
