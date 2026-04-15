<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStock extends Model
{
    protected $fillable = [
        'warehouse_id', 'batch_id', 'quantity', 'reserved_qty', 'reorder_level',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_qty' => 'integer',
        'reorder_level' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function availableQty(): int
    {
        return $this->quantity - $this->reserved_qty;
    }

    public function isLowStock(): bool
    {
        return $this->reorder_level !== null && $this->availableQty() <= $this->reorder_level;
    }

    public function scopeLowStock($query)
    {
        return $query->whereNotNull('reorder_level')
            ->whereRaw('(quantity - reserved_qty) <= reorder_level');
    }
}
