<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'client_id', 'entry_date', 'entry_type', 'debit_amount',
        'credit_amount', 'running_balance', 'reference_type',
        'reference_id', 'narration', 'financial_year', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'debit_amount' => 'decimal:2',
            'credit_amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function currentFinancialYear(): string
    {
        $now = now();
        $year = $now->month >= 4 ? $now->year : $now->year - 1;
        return $year . '-' . substr($year + 1, 2);
    }
}
