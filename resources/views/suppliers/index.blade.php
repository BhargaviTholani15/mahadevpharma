<x-layouts.app title="Suppliers">
<div class="space-y-6" x-data="{
    showModal: false,
    editId: null,
    form: { name: '', contact_person: '', email: '', phone: '', gst_number: '', drug_license_no: '', address_line1: '', city: '', state: '', state_code: '', pincode: '', payment_terms_days: '', is_active: true },
    resetForm() {
        this.form = { name: '', contact_person: '', email: '', phone: '', gst_number: '', drug_license_no: '', address_line1: '', city: '', state: '', state_code: '', pincode: '', payment_terms_days: '', is_active: true };
    },
    openAdd() { this.resetForm(); this.editId = null; this.showModal = true; },
    openEdit(s) {
        this.editId = s.id;
        this.form = {
            name: s.name || '', contact_person: s.contact_person || '', email: s.email || '',
            phone: s.phone || '', gst_number: s.gst_number || '', drug_license_no: s.drug_license_no || '',
            address_line1: s.address_line1 || '', city: s.city || '', state: s.state || '',
            state_code: s.state_code || '', pincode: s.pincode || '',
            payment_terms_days: s.payment_terms_days || '', is_active: s.is_active
        };
        this.showModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Suppliers</h1><p class="text-gray-500 mt-1">Manage pharmaceutical suppliers</p></div>
        <button @click="openAdd()" class="btn-primary">+ Add Supplier</button>
    </div>

    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, contact, phone, GST..." class="form-input"></div>
            <button type="submit" class="btn-primary">Search</button>
            @if(request('search'))<a href="{{ route('suppliers.index') }}" class="btn-secondary">Clear</a>@endif
        </form>
    </div>

    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>GST Number</th>
                        <th>Drug License</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $s)
                    <tr>
                        <td class="font-medium text-gray-900">{{ $s->name }}</td>
                        <td>{{ $s->contact_person ?? '-' }}</td>
                        <td>{{ $s->phone ?? '-' }}</td>
                        <td>{{ $s->email ?? '-' }}</td>
                        <td class="font-mono text-xs">{{ $s->gst_number ?? '-' }}</td>
                        <td class="font-mono text-xs">{{ $s->drug_license_no ?? '-' }}</td>
                        <td>{{ $s->city ?? '-' }}</td>
                        <td>{{ $s->state ?? '-' }}</td>
                        <td><x-status-badge :status="$s->is_active ? 'ACTIVE' : 'INACTIVE'" /></td>
                        <td>
                            <div class="flex gap-1">
                                <button @click="openEdit({{ $s->toJson() }})" class="btn-secondary btn-sm">Edit</button>
                                <form method="POST" action="{{ route('suppliers.destroy', $s) }}" onsubmit="return confirm('Delete this supplier?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Del</button></form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-8 text-gray-400">No suppliers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $suppliers->links() }}</div>
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="showModal" x-transition.opacity class="modal-overlay" @click.self="showModal = false" style="display:none">
        <div class="modal-content max-w-2xl" @click.stop>
            <div class="px-6 py-4 border-b"><h3 class="text-lg font-semibold" x-text="editId ? 'Edit Supplier' : 'Add Supplier'"></h3></div>
            <form :action="editId ? '{{ url('suppliers') }}/' + editId : '{{ route('suppliers.store') }}'" method="POST" class="px-6 py-4 space-y-4">
                @csrf
                <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="form-label">Name *</label><input type="text" name="name" x-model="form.name" required class="form-input"></div>
                    <div><label class="form-label">Contact Person</label><input type="text" name="contact_person" x-model="form.contact_person" class="form-input"></div>
                    <div><label class="form-label">Phone *</label><input type="text" name="phone" x-model="form.phone" required class="form-input"></div>
                    <div><label class="form-label">Email</label><input type="email" name="email" x-model="form.email" class="form-input"></div>
                    <div><label class="form-label">GST Number</label><input type="text" name="gst_number" x-model="form.gst_number" class="form-input"></div>
                    <div><label class="form-label">Drug License No</label><input type="text" name="drug_license_no" x-model="form.drug_license_no" class="form-input"></div>
                    <div class="md:col-span-2"><label class="form-label">Address</label><input type="text" name="address_line1" x-model="form.address_line1" class="form-input"></div>
                    <div><label class="form-label">City</label><input type="text" name="city" x-model="form.city" class="form-input"></div>
                    <div><label class="form-label">State</label><input type="text" name="state" x-model="form.state" class="form-input"></div>
                    <div><label class="form-label">State Code</label><input type="text" name="state_code" x-model="form.state_code" maxlength="2" class="form-input"></div>
                    <div><label class="form-label">Pincode</label><input type="text" name="pincode" x-model="form.pincode" class="form-input"></div>
                    <div><label class="form-label">Payment Terms (days)</label><input type="number" name="payment_terms_days" x-model="form.payment_terms_days" min="0" class="form-input"></div>
                    <div class="flex items-center gap-2 pt-6">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" x-bind:checked="form.is_active" class="rounded border-gray-300 text-green-600 focus:ring-green-500" id="supplier_active">
                        <label for="supplier_active" class="form-label mb-0">Active</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" x-text="editId ? 'Update' : 'Add'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>
