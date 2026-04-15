<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'product_id', 'batch_number', 'mfg_date', 'expiry_date',
        'mrp', 'purchase_price', 'selling_price', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'mfg_date' => 'date',
            'expiry_date' => 'date',
            'mrp' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 90): bool
    {
        return $this->expiry_date->isBetween(now(), now()->addDays($days));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>', now());
    }

    public function scopeExpiringSoon($query, int $days = 90)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }
}
