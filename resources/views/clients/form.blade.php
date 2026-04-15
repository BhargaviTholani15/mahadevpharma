<x-layouts.app :title="$client ? 'Edit Client' : 'Add Client'">
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4"><a href="{{ route('clients.index') }}" class="btn-secondary btn-sm">&larr; Back</a><h1 class="text-2xl font-bold text-gray-900">{{ $client ? 'Edit Client' : 'Add Client' }}</h1></div>
    @if($errors->any())<div class="rounded-xl p-4 bg-red-50 border border-red-200 text-red-700 text-sm"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ $client ? route('clients.update', $client) : route('clients.store') }}" class="space-y-6">
        @csrf @if($client) @method('PUT') @endif
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Business Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="form-label">Business Name *</label><input type="text" name="business_name" value="{{ old('business_name', $client?->business_name) }}" required class="form-input"></div>
                <div><label class="form-label">Proprietor Name</label><input type="text" name="proprietor_name" value="{{ old('proprietor_name', $client?->proprietor_name) }}" class="form-input"></div>
                <div><label class="form-label">Contact Person</label><input type="text" name="contact_person" value="{{ old('contact_person', $client?->contact_person) }}" class="form-input"></div>
                <div><label class="form-label">Alt Phone</label><input type="text" name="alt_phone" value="{{ old('alt_phone', $client?->alt_phone) }}" class="form-input"></div>
                <div><label class="form-label">GST Number</label><input type="text" name="gst_number" value="{{ old('gst_number', $client?->gst_number) }}" class="form-input" maxlength="15"></div>
                <div><label class="form-label">Drug License No</label><input type="text" name="drug_license_no" value="{{ old('drug_license_no', $client?->drug_license_no) }}" class="form-input"></div>
                <div><label class="form-label">PAN Number</label><input type="text" name="pan_number" value="{{ old('pan_number', $client?->pan_number) }}" class="form-input" maxlength="10"></div>
            </div>
        </div>
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Address</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2"><label class="form-label">Address</label><input type="text" name="address_line1" value="{{ old('address_line1', $client?->address_line1) }}" class="form-input"></div>
                <div><label class="form-label">City</label><input type="text" name="city" value="{{ old('city', $client?->city) }}" class="form-input"></div>
                <div><label class="form-label">State</label><input type="text" name="state" value="{{ old('state', $client?->state) }}" class="form-input"></div>
                <div><label class="form-label">Pincode</label><input type="text" name="pincode" value="{{ old('pincode', $client?->pincode) }}" class="form-input" maxlength="10"></div>
                <div><label class="form-label">State Code</label><input type="text" name="state_code" value="{{ old('state_code', $client?->state_code) }}" class="form-input" maxlength="5"></div>
            </div>
        </div>
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Payment Terms</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="form-label">Credit Limit (₹)</label><input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $client?->credit_limit) }}" class="form-input"></div>
                <div><label class="form-label">Credit Period (days)</label><input type="number" name="credit_period_days" value="{{ old('credit_period_days', $client?->credit_period_days) }}" class="form-input"></div>
            </div>
        </div>
        <div class="flex gap-3"><button type="submit" class="btn-primary">{{ $client ? 'Update Client' : 'Create Client' }}</button><a href="{{ route('clients.index') }}" class="btn-secondary">Cancel</a></div>
    </form>
</div>
</x-layouts.app>
