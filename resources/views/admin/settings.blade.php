<x-layouts.app title="Settings">
<div class="max-w-3xl mx-auto space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Company Settings</h1><p class="text-gray-500 mt-1">Configure business details</p></div>
    @if($errors->any())<div class="rounded-xl p-4 bg-red-50 border border-red-200 text-red-700 text-sm"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
        @csrf @method('PUT')
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Company</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="form-label">Company Name *</label><input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}" required class="form-input"></div>
                <div><label class="form-label">GST Number</label><input type="text" name="gst_number" value="{{ old('gst_number', $settings->gst_number) }}" class="form-input"></div>
                <div><label class="form-label">Drug License No</label><input type="text" name="drug_license_no" value="{{ old('drug_license_no', $settings->drug_license_no) }}" class="form-input"></div>
                <div><label class="form-label">State Code</label><input type="text" name="state_code" value="{{ old('state_code', $settings->state_code) }}" class="form-input"></div>
                <div><label class="form-label">Phone</label><input type="text" name="phone" value="{{ old('phone', $settings->phone) }}" class="form-input"></div>
                <div><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $settings->email) }}" class="form-input"></div>
            </div>
        </div>
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Address</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2"><label class="form-label">Address</label><input type="text" name="address_line1" value="{{ old('address_line1', $settings->address_line1) }}" class="form-input"></div>
                <div><label class="form-label">City</label><input type="text" name="city" value="{{ old('city', $settings->city) }}" class="form-input"></div>
                <div><label class="form-label">State</label><input type="text" name="state" value="{{ old('state', $settings->state) }}" class="form-input"></div>
                <div><label class="form-label">Pincode</label><input type="text" name="pincode" value="{{ old('pincode', $settings->pincode) }}" class="form-input"></div>
            </div>
        </div>
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Invoice</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="form-label">Invoice Prefix</label><input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" class="form-input"></div>
                <div><label class="form-label">Financial Year</label><input type="text" name="financial_year" value="{{ old('financial_year', $settings->financial_year) }}" class="form-input"></div>
            </div>
        </div>
        <button type="submit" class="btn-primary">Save Settings</button>
    </form>
</div>
</x-layouts.app>
