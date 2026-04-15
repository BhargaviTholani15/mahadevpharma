<x-layouts.marketing title="Blog — Mahadev Pharma">

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-green-700 via-green-800 to-emerald-900 overflow-hidden">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyem0wLTRWMjhIMjR2MmgxMnptMC00VjI0SDI0djJoMTJ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight">Our <span class="text-green-300">Blog</span></h1>
            <p class="mt-6 text-xl text-green-100/80 leading-relaxed">Industry insights, tips, and updates for pharmacy professionals.</p>
        </div>
    </div>
</section>

{{-- Blog Posts --}}
<section class="py-16 lg:py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @php
        $posts = [
            [
                'slug' => 'understanding-gst-pharmaceutical-products',
                'title' => 'Understanding GST on Pharmaceutical Products',
                'excerpt' => 'A comprehensive guide to GST rates, HSN codes, and compliance requirements for pharma distributors and retailers in India.',
                'date' => '15 Mar 2025',
                'category' => 'Compliance',
                'catColor' => 'bg-blue-100 text-blue-700',
                'readTime' => '8 min read',
            ],
            [
                'slug' => 'manage-pharmacy-inventory-effectively',
                'title' => 'How to Manage Pharmacy Inventory Effectively',
                'excerpt' => 'Best practices for stock management, expiry tracking, and reorder planning to minimize waste and maximize availability.',
                'date' => '28 Feb 2025',
                'category' => 'Operations',
                'catColor' => 'bg-green-100 text-green-700',
                'readTime' => '6 min read',
            ],
            [
                'slug' => 'digital-transformation-pharma-distribution',
                'title' => 'Digital Transformation in Pharma Distribution',
                'excerpt' => 'How technology is changing the pharmaceutical supply chain — from online ordering to real-time delivery tracking.',
                'date' => '10 Feb 2025',
                'category' => 'Technology',
                'catColor' => 'bg-purple-100 text-purple-700',
                'readTime' => '5 min read',
            ],
            [
                'slug' => 'drug-schedule-classification-india',
                'title' => 'Drug Schedule Classification in India',
                'excerpt' => 'Understanding Schedule H, H1, X, and G — what pharmacists need to know about drug scheduling and dispensing rules.',
                'date' => '25 Jan 2025',
                'category' => 'Regulations',
                'catColor' => 'bg-amber-100 text-amber-700',
                'readTime' => '7 min read',
            ],
            [
                'slug' => 'building-strong-distributor-pharmacy-relationships',
                'title' => 'Building Strong Distributor-Pharmacy Relationships',
                'excerpt' => 'Key factors that make a pharma distributor reliable — credit terms, delivery consistency, and product availability.',
                'date' => '10 Jan 2025',
                'category' => 'Business',
                'catColor' => 'bg-teal-100 text-teal-700',
                'readTime' => '4 min read',
            ],
            [
                'slug' => 'expiry-date-management-pharmacies',
                'title' => 'Expiry Date Management for Pharmacies',
                'excerpt' => 'Strategies to track near-expiry medicines, manage returns, and reduce financial loss from expired stock.',
                'date' => '28 Dec 2024',
                'category' => 'Operations',
                'catColor' => 'bg-green-100 text-green-700',
                'readTime' => '5 min read',
            ],
        ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
            <a href="/blog/{{ $post['slug'] }}" class="block">
                <article class="bg-white border border-gray-100 rounded-2xl overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full">
                    <div class="h-2 bg-gradient-to-r from-green-500 to-emerald-500"></div>
                    <div class="p-6 lg:p-8 flex flex-col flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $post['catColor'] }}">{{ $post['category'] }}</span>
                            <span class="text-sm text-gray-400">{{ $post['readTime'] }}</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-green-600 transition-colors">{{ $post['title'] }}</h3>
                        <p class="text-gray-600 text-base leading-relaxed mb-6 flex-1">{{ $post['excerpt'] }}</p>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <span class="text-sm text-gray-400">{{ $post['date'] }}</span>
                            <span class="text-green-600 font-semibold text-sm group-hover:translate-x-1 transition-transform">Read more &rarr;</span>
                        </div>
                    </div>
                </article>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Newsletter --}}
<section class="py-16 bg-gray-50">
    <div class="max-w-2xl mx-auto text-center px-4">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Stay Updated</h2>
        <p class="text-lg text-gray-500 mb-8">Get the latest pharma industry insights and company updates delivered to your inbox.</p>
        <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            <input type="email" placeholder="Enter your email" class="form-input flex-1 text-base py-3">
            <button type="submit" class="btn-primary text-base px-6 py-3">Subscribe</button>
        </form>
    </div>
</section>
</x-layouts.marketing>
