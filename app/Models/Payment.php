<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'payment_number', 'client_id', 'amount', 'payment_method',
        'payment_date', 'reference_number', 'gateway_txn_id', 'status',
        'notes', 'received_by', 'confirmed_by', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function allocatedAmount(): float
    {
        return $this->allocations()->sum('amount');
    }

    public function unallocatedAmount(): float
    {
        return $this->amount - $this->allocatedAmount();
    }

    public static function generatePaymentNumber(): string
    {
        $year = now()->format('Y');
        $last = static::whereYear('created_at', $year)->latest('id')->first();
        $seq = $last ? ((int) substr($last->payment_number, -4)) + 1 : 1;
        return "PAY-{$year}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
