<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HsnCode extends Model
{
    protected $fillable = [
        'code', 'description', 'cgst_rate', 'sgst_rate', 'igst_rate',
        'effective_from', 'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'cgst_rate' => 'decimal:2',
            'sgst_rate' => 'decimal:2',
            'igst_rate' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('effective_from', '<=', now())
            ->where(fn($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', now()));
    }
}
