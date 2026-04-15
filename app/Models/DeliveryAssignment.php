<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryAssignment extends Model
{
    protected $fillable = [
        'order_id', 'assigned_to', 'assigned_by', 'delivery_agent_id', 'status',
        'delivery_otp', 'otp_expires_at', 'otp_verified_at',
        'scheduled_date', 'delivered_at', 'delivery_lat', 'delivery_lng',
        'failure_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'otp_expires_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'scheduled_date' => 'date',
            'delivered_at' => 'datetime',
            'delivery_lat' => 'decimal:7',
            'delivery_lng' => 'decimal:7',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function deliveryAgent(): BelongsTo
    {
        return $this->belongsTo(DeliveryAgent::class);
    }

    public function generateOtp(): string
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'delivery_otp' => $otp,
            'otp_expires_at' => now()->addMinutes(30),
        ]);
        return $otp;
    }

    public function verifyOtp(string $otp): bool
    {
        return $this->delivery_otp === $otp
            && $this->otp_expires_at?->isFuture();
    }
}
