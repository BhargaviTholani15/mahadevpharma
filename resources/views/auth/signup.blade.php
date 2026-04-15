<x-layouts.guest title="Register Your Pharmacy">
<div class="min-h-screen flex">
    {{-- Left panel --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-green-700 via-green-800 to-green-950 relative overflow-hidden">
        <div class="relative z-10 flex flex-col justify-center px-16">
            <div class="mb-8">
                <img src="/logo.png" alt="Mahadev Pharma" class="h-16 object-contain brightness-0 invert">
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">Register Your Pharmacy</h1>
            <p class="text-lg text-green-200 max-w-md">Join 100+ pharmacies that trust Mahadev Pharma for quality medicines, same-day delivery, and GST-compliant invoicing.</p>
            <div class="mt-12 space-y-4">
                @foreach([
                    ['💊', 'Browse 500+ medicines from top brands'],
                    ['💳', 'Flexible credit terms & easy payments'],
                    ['🚚', 'Same-day delivery in Hyderabad & Secunderabad'],
                    ['📄', 'GST-compliant invoices with HSN codes'],
                ] as [$emoji, $text])
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                        <span class="text-xl">{{ $emoji }}</span>
                    </div>
                    <span class="text-base text-green-100/90">{{ $text }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="flex-1 flex items-center justify-center p-8 overflow-y-auto">
        <div class="w-full max-w-lg">
            <div class="lg:hidden flex items-center gap-3 mb-8">
                <img src="/logo.png" alt="Mahadev Pharma" class="h-10 object-contain">
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Create Your Account</h2>
            <p class="text-gray-500 mb-8">Fill in your details to get started</p>

            @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('signup.store') }}" class="space-y-5">
                @csrf

                {{-- Personal Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Personal Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label" for="full_name">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required
                                   class="form-input" placeholder="Your full name">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label" for="phone">Phone Number <span class="text-red-500">*</span></label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                                       class="form-input" placeholder="10-digit phone number">
                            </div>
                            <div>
                                <label class="form-label" for="email">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       class="form-input" placeholder="your@email.com">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label" for="password">Password <span class="text-red-500">*</span></label>
                                <input type="password" id="password" name="password" required
                                       class="form-input" placeholder="Min 6 characters">
                            </div>
                            <div>
                                <label class="form-label" for="password_confirmation">Confirm Password <span class="text-red-500">*</span></label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                       class="form-input" placeholder="Re-enter password">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Business Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Business Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label" for="business_name">Pharmacy / Business Name <span class="text-red-500">*</span></label>
                            <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}" required
                                   class="form-input" placeholder="Your pharmacy name">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label" for="drug_license_no">Drug License No. <span class="text-red-500">*</span></label>
                                <input type="text" id="drug_license_no" name="drug_license_no" value="{{ old('drug_license_no') }}" required
                                       class="form-input" placeholder="e.g., 345/HD/AP/2002">
                            </div>
                            <div>
                                <label class="form-label" for="gst_number">GST Number</label>
                                <input type="text" id="gst_number" name="gst_number" value="{{ old('gst_number') }}"
                                       class="form-input" placeholder="15-digit GSTIN">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Address --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Address</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label" for="address_line1">Address <span class="text-red-500">*</span></label>
                            <input type="text" id="address_line1" name="address_line1" value="{{ old('address_line1') }}" required
                                   class="form-input" placeholder="Street address">
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="form-label" for="city">City <span class="text-red-500">*</span></label>
                                <input type="text" id="city" name="city" value="{{ old('city') }}" required
                                       class="form-input" placeholder="City">
                            </div>
                            <div>
                                <label class="form-label" for="state">State <span class="text-red-500">*</span></label>
                                <input type="text" id="state" name="state" value="{{ old('state', 'Telangana') }}" required
                                       class="form-input" placeholder="State">
                            </div>
                            <div>
                                <label class="form-label" for="pincode">Pincode <span class="text-red-500">*</span></label>
                                <input type="text" id="pincode" name="pincode" value="{{ old('pincode') }}" required
                                       class="form-input" placeholder="6-digit">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full h-11 text-base">Create Account</button>

                <p class="text-center text-base text-gray-500">
                    Already have an account?
                    <a href="{{ route('vendor.login') }}" class="text-green-600 hover:text-green-700 font-medium">Sign In</a>
                </p>
            </form>
        </div>
    </div>
</div>
</x-layouts.guest>
