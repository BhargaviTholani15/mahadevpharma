<x-layouts.marketing title="Mahadev Pharma — Trusted Pharmaceutical Distribution">
{{-- Hero --}}
<section class="bg-gradient-to-br from-green-50 to-emerald-50 py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900 leading-tight">Your Trusted Pharmaceutical Distribution Partner</h1>
            <p class="mt-6 text-lg text-gray-600 max-w-xl">Mahadev Pharma delivers quality medicines from top pharmaceutical brands to pharmacies across Telangana and Andhra Pradesh. Fast, reliable, GST-compliant.</p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="/contact" class="btn-primary text-base px-8 py-3">Become a Partner</a>
                <a href="/about" class="btn-secondary text-base px-8 py-3">Learn More</a>
            </div>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
            @foreach([['500+', 'Medicines'], ['100+', 'Partner Pharmacies'], ['12+', 'Top Brands'], ['2', 'States Covered']] as [$val, $label])
            <div><p class="text-3xl lg:text-4xl font-extrabold text-green-600">{{ $val }}</p><p class="mt-2 text-gray-500 font-medium">{{ $label }}</p></div>
            @endforeach
        </div>
    </div>
</section>

{{-- Categories --}}
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Products We <span class="text-green-600">Distribute</span></h2>
            <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto">From everyday medicines to specialty drugs — we stock everything your pharmacy needs.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
            @php
            $categories = [
                ['Tablets & Capsules', '💊', 'from-blue-50 to-blue-100', 'border-blue-200', 'text-blue-700'],
                ['Syrups & Suspensions', '🧴', 'from-purple-50 to-purple-100', 'border-purple-200', 'text-purple-700'],
                ['Injections & Vials', '💉', 'from-red-50 to-red-100', 'border-red-200', 'text-red-700'],
                ['Creams & Ointments', '🧪', 'from-amber-50 to-amber-100', 'border-amber-200', 'text-amber-700'],
                ['Eye & Ear Drops', '👁️', 'from-cyan-50 to-cyan-100', 'border-cyan-200', 'text-cyan-700'],
                ['Powders & Sachets', '📦', 'from-orange-50 to-orange-100', 'border-orange-200', 'text-orange-700'],
                ['Inhalers', '🌬️', 'from-indigo-50 to-indigo-100', 'border-indigo-200', 'text-indigo-700'],
                ['Surgical Supplies', '🩹', 'from-teal-50 to-teal-100', 'border-teal-200', 'text-teal-700'],
                ['Vitamins & Supplements', '💪', 'from-green-50 to-green-100', 'border-green-200', 'text-green-700'],
                ['OTC & Personal Care', '🏥', 'from-pink-50 to-pink-100', 'border-pink-200', 'text-pink-700'],
            ];
            @endphp
            @foreach($categories as [$name, $emoji, $bg, $border, $textColor])
            <div class="bg-gradient-to-br {{ $bg }} border {{ $border }} rounded-2xl p-6 text-center hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                <span class="text-4xl block mb-3 group-hover:scale-110 transition-transform duration-300">{{ $emoji }}</span>
                <p class="font-semibold {{ $textColor }} text-base">{{ $name }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Brands --}}
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Authorized Stockist of <span class="text-green-600">Top Brands</span></h2>
            <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto">We source directly from leading pharmaceutical manufacturers — ensuring 100% authentic medicines.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @php
            $brands = [
                ['Cipla', 'Leading global pharma', 'from-blue-500 to-blue-600'],
                ['Sun Pharma', 'India\'s largest pharma company', 'from-orange-500 to-orange-600'],
                ['Dr. Reddy\'s', 'Innovation-driven pharma', 'from-purple-500 to-purple-600'],
                ['Lupin', 'Trusted generics leader', 'from-teal-500 to-teal-600'],
                ['Mankind', 'Healthcare for all', 'from-red-500 to-red-600'],
                ['Zydus', 'Science-driven lifesciences', 'from-indigo-500 to-indigo-600'],
                ['Torrent', 'Quality pharmaceuticals', 'from-cyan-500 to-cyan-600'],
                ['Alkem', 'Reliable healthcare partner', 'from-green-500 to-green-600'],
                ['Abbott', 'Global healthcare leader', 'from-sky-500 to-sky-600'],
                ['GSK', 'Science-led global company', 'from-amber-500 to-amber-600'],
                ['Biocon', 'Biopharma innovator', 'from-emerald-500 to-emerald-600'],
                ['Glenmark', 'Research-driven pharma', 'from-pink-500 to-pink-600'],
            ];
            @endphp
            @foreach($brands as [$name, $tagline, $gradient])
            <div class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <span class="text-white font-bold text-lg">{{ substr($name, 0, 1) }}</span>
                </div>
                <h3 class="font-bold text-gray-900 text-lg">{{ $name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $tagline }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Why Choose --}}
<section class="py-16 bg-green-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12"><h2 class="text-3xl font-bold">Why Choose Mahadev Pharma?</h2></div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['Same-Day Delivery', 'Orders placed before 2 PM are delivered the same day within Secunderabad and Hyderabad.'],
                ['100% GST Compliant', 'All invoices are GST-compliant with proper HSN codes, CGST/SGST breakdowns, and digital records.'],
                ['Wide Product Range', 'Access to 500+ medicines from 12+ top pharmaceutical brands at competitive wholesale prices.'],
            ] as [$title, $desc])
            <div class="bg-white/10 backdrop-blur rounded-2xl p-6">
                <h3 class="text-xl font-bold mb-3">{{ $title }}</h3>
                <p class="text-green-100">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-3xl mx-auto text-center px-4">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Ready to Partner With Us?</h2>
        <p class="text-gray-500 mb-8">Join 100+ pharmacies that trust Mahadev Pharma for their pharmaceutical supply needs.</p>
        <a href="/contact" class="btn-primary text-base px-8 py-3">Get Started Today</a>
    </div>
</section>
</x-layouts.marketing>
