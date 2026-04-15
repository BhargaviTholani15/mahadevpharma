<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // Core
        'name', 'generic_name', 'brand_id', 'category_id', 'hsn_code_id',
        'dosage_form', 'strength', 'pack_size', 'sku',
        'is_active',

        // Pharmaceutical Details
        'composition', 'schedule_type',
        'storage_conditions', 'shelf_life_months', 'is_controlled', 'is_returnable',

        // Manufacturer & Regulatory
        'manufacturer_name', 'manufacturer_address', 'country_of_origin', 'marketing_authorization',

        // Default Pricing
        'mrp', 'purchase_price', 'selling_price',

        // Inventory Defaults
        'min_stock_level', 'reorder_level', 'reorder_quantity', 'lead_time_days', 'rack_location',

        // Description & Media
        'description', 'usage_instructions', 'side_effects', 'image_url', 'barcode', 'tags',

        // Batch Configuration
        'batch_prefix', 'near_expiry_alert_days',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_controlled' => 'boolean',
        'is_returnable' => 'boolean',
        'mrp' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'tags' => 'array',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function hsnCode(): BelongsTo
    {
        return $this->belongsTo(HsnCode::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(Batch::class)->where('is_active', true);
    }

    public function totalStock(): int
    {
        return $this->batches()
            ->join('warehouse_stocks', 'batches.id', '=', 'warehouse_stocks.batch_id')
            ->sum('warehouse_stocks.quantity');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('generic_name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%")
              ->orWhere('manufacturer_name', 'like', "%{$term}%");
        });
    }

    public function scopeLowStock($query, int $threshold = 50)
    {
        return $query->whereHas('batches', function ($q) use ($threshold) {
            $q->whereHas('warehouseStocks', function ($wq) use ($threshold) {
                $wq->whereRaw('quantity - reserved_qty < ?', [$threshold]);
            });
        });
    }

    public function scopeControlled($query)
    {
        return $query->where('is_controlled', true);
    }

    public function scopeBySchedule($query, string $schedule)
    {
        return $query->where('schedule_type', $schedule);
    }
}
