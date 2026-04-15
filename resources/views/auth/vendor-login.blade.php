<x-layouts.guest title="Client Login">
<div class="min-h-screen flex">
    {{-- Left panel --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-green-700 via-green-800 to-green-950 relative overflow-hidden">
        <div class="relative z-10 flex flex-col justify-center px-16">
            <div class="mb-8">
                <img src="/logo.png" alt="Mahadev Pharma" class="h-16 object-contain brightness-0 invert">
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">Client Portal</h1>
            <p class="text-lg text-green-200 max-w-md">Browse our product catalog, place orders, track deliveries, and manage your pharmacy account.</p>
            <div class="mt-16 grid grid-cols-3 gap-6">
                @foreach([['Fast', 'Delivery'], ['500+', 'Products'], ['GST', 'Compliant']] as [$val, $label])
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                    <p class="text-2xl font-bold text-white">{{ $val }}</p>
                    <p class="text-sm text-green-200">{{ $label }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="flex-1 flex items-center justify-center p-8">
        <div class="w-full max-w-md">
            <div class="lg:hidden flex items-center gap-3 mb-8">
                <img src="/logo.png" alt="Mahadev Pharma" class="h-10 object-contain">
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Client Login</h2>
            <p class="text-gray-500 mb-8">Sign in to your pharmacy partner account</p>

            @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="portal" value="vendor">
                <div>
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                           class="form-input" placeholder="Enter your phone number">
                </div>
                <div>
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           class="form-input" placeholder="Enter your password">
                </div>
                <button type="submit" class="btn-primary w-full h-11">Sign In</button>

                <p class="text-center text-base text-gray-500">
                    Don't have an account?
                    <a href="{{ route('signup') }}" class="text-green-600 hover:text-green-700 font-medium">Register Your Pharmacy</a>
                </p>
            </form>
        </div>
    </div>
</div>
</x-layouts.guest>
