<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $hidden = [
        'bank_account_no', 'bank_ifsc', 'bank_name', 'bank_branch',
    ];

    protected $fillable = [
        'user_id', 'business_name', 'proprietor_name', 'business_type',
        'drug_license_no', 'dl_expiry_date', 'gst_number', 'pan_number', 'fssai_number',
        'state_code', 'address_line1', 'address_line2', 'city', 'district', 'state', 'pincode',
        'alt_phone', 'contact_person', 'contact_designation',
        'delivery_address', 'delivery_city', 'delivery_pincode',
        'delivery_instructions', 'preferred_delivery_time',
        'bank_name', 'bank_account_no', 'bank_ifsc', 'bank_branch',
        'credit_limit', 'current_outstanding', 'credit_period_days',
        'kyc_verified', 'kyc_verified_at', 'kyc_verified_by', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'current_outstanding' => 'decimal:2',
            'kyc_verified' => 'boolean',
            'kyc_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function availableCredit(): float
    {
        return max(0, $this->credit_limit - $this->current_outstanding);
    }

    public function canPlaceOrder(float $amount): bool
    {
        return ($this->current_outstanding + $amount) <= $this->credit_limit;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeKycVerified($query)
    {
        return $query->where('kyc_verified', true);
    }
}
