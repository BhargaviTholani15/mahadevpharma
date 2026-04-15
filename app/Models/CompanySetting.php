<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'company_name', 'gst_number', 'drug_license_no', 'state_code',
        'address_line1', 'city', 'state', 'pincode', 'phone', 'email',
        'logo_path', 'invoice_prefix', 'current_invoice_seq', 'financial_year',
    ];

    public static function get(): static
    {
        return static::first() ?? new static();
    }

    public function nextInvoiceNumber(): string
    {
        $this->increment('current_invoice_seq');
        return $this->invoice_prefix . '-' . $this->financial_year . '-'
            . str_pad($this->current_invoice_seq, 5, '0', STR_PAD_LEFT);
    }
}
