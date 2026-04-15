<x-layouts.marketing title="How We Work — Mahadev Pharma">

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-green-700 via-green-800 to-emerald-900 overflow-hidden">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyem0wLTRWMjhIMjR2MmgxMnptMC00VjI0SDI0djJoMTJ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight">How We <span class="text-green-300">Work</span></h1>
            <p class="mt-6 text-xl text-green-100/80 leading-relaxed">Simple, transparent, and efficient — from order to delivery in just a few steps.</p>
        </div>
    </div>
</section>

{{-- Steps --}}
<section class="py-16 lg:py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1.5 rounded-full bg-green-100 text-green-700 text-sm font-semibold mb-4">Our Process</span>
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">From Registration to <span class="text-green-600">Doorstep Delivery</span></h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @php
            $steps = [
                ['1', 'Register as a Partner', 'Submit your pharmacy details, drug license, and GST information. Our team verifies your credentials within 24 hours.', '📝', 'from-blue-500 to-blue-600'],
                ['2', 'Browse & Order', 'Access our catalog with 500+ medicines. Place orders online or through your dedicated sales representative.', '🛒', 'from-green-500 to-green-600'],
                ['3', 'Fast Processing', 'Orders are processed immediately. Our warehouse team picks, packs, and prepares your order with proper batch tracking.', '⚡', 'from-amber-500 to-amber-600'],
                ['4', 'Same-Day Delivery', 'Orders placed before 2 PM are delivered the same day. Track your delivery in real-time through your dashboard.', '🚚', 'from-purple-500 to-purple-600'],
                ['5', 'GST-Compliant Invoicing', 'Receive proper tax invoices with HSN codes and CGST/SGST breakdowns. Access invoices and ledger anytime.', '📄', 'from-teal-500 to-teal-600'],
                ['6', 'Flexible Payments', 'Pay via UPI, bank transfer, cheque, or credit line. View your account statement and outstanding balance online.', '💳', 'from-indigo-500 to-indigo-600'],
            ];
            @endphp
            @foreach($steps as [$num, $title, $desc, $emoji, $gradient])
            <div class="relative bg-white border border-gray-100 rounded-2xl p-8 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-white font-bold text-lg group-hover:scale-110 transition-transform duration-300">
                        {{ $num }}
                    </div>
                    <span class="text-3xl">{{ $emoji }}</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $title }}</h3>
                <p class="text-gray-600 text-base leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Timeline visual --}}
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">What Makes Us <span class="text-green-600">Different</span></h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach([
                ['Same-Day Delivery', 'Orders before 2 PM delivered same day in Hyderabad & Secunderabad', '🕐'],
                ['Real-Time Tracking', 'Track your orders and deliveries through your client dashboard', '📱'],
                ['Digital Ledger', 'Access your complete payment history and outstanding balance 24/7', '📊'],
                ['Dedicated Support', 'Personal sales representative assigned to every client', '🎯'],
            ] as [$title, $desc, $emoji])
            <div class="bg-white border border-gray-100 rounded-2xl p-6 flex gap-4 hover:shadow-md transition-shadow duration-300">
                <span class="text-3xl shrink-0">{{ $emoji }}</span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $title }}</h3>
                    <p class="text-gray-600 text-base">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 bg-gradient-to-r from-green-600 to-emerald-700 text-white">
    <div class="max-w-3xl mx-auto text-center px-4">
        <h2 class="text-3xl font-bold mb-4">Ready to Get Started?</h2>
        <p class="text-lg text-green-100 mb-8">Register your pharmacy today and start ordering from our catalog of 500+ medicines.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="/contact" class="inline-flex items-center justify-center px-8 py-3 rounded-xl bg-white text-green-700 font-semibold text-base hover:bg-green-50 transition">Become a Partner</a>
            <a href="/vendor-portal" class="inline-flex items-center justify-center px-8 py-3 rounded-xl border-2 border-white text-white font-semibold text-base hover:bg-white/10 transition">Client Login</a>
        </div>
    </div>
</section>
</x-layouts.marketing>
