<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryAgent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'alt_phone', 'email',
        'vehicle_type', 'vehicle_number', 'license_number', 'license_expiry',
        'zone', 'address', 'emergency_contact',
        'id_proof_type', 'id_proof_number',
        'joining_date', 'is_available', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function deliveryAssignments(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class);
    }

    public function activeDeliveries(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class)
            ->whereNotIn('status', ['DELIVERED', 'FAILED']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)->where('is_available', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('zone', 'like', "%{$term}%")
              ->orWhere('vehicle_number', 'like', "%{$term}%");
        });
    }
}
