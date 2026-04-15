<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Mahadev Pharma — Pharmaceutical Distribution' }}</title>
    <link rel="icon" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans antialiased">

{{-- Header --}}
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-gray-100 shadow-sm" x-data="{ menuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <a href="/" class="flex items-center gap-3">
                <img src="/logo.png" alt="Mahadev Pharma" class="h-14 object-contain">
            </a>
            <nav class="hidden md:flex items-center gap-8">
                <a href="/" class="text-sm font-bold text-gray-700 hover:text-green-600 transition">Home</a>
                <a href="/about" class="text-sm font-bold text-gray-700 hover:text-green-600 transition">About</a>
                <a href="/how-we-work" class="text-sm font-bold text-gray-700 hover:text-green-600 transition">How We Work</a>
                <a href="/blog" class="text-sm font-bold text-gray-700 hover:text-green-600 transition">Blog</a>
                <a href="/contact" class="text-sm font-bold text-gray-700 hover:text-green-600 transition">Contact</a>
            </nav>
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('vendor.login') }}" class="btn-primary text-sm">Client Login</a>
            </div>
            <button @click="menuOpen = !menuOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        {{-- Mobile menu --}}
        <div x-show="menuOpen" x-transition class="md:hidden py-4 border-t" style="display:none">
            <div class="flex flex-col gap-3">
                <a href="/" class="text-sm font-bold text-gray-700 py-2">Home</a>
                <a href="/about" class="text-sm font-bold text-gray-700 py-2">About</a>
                <a href="/how-we-work" class="text-sm font-bold text-gray-700 py-2">How We Work</a>
                <a href="/blog" class="text-sm font-bold text-gray-700 py-2">Blog</a>
                <a href="/contact" class="text-sm font-bold text-gray-700 py-2">Contact</a>
                <a href="{{ route('vendor.login') }}" class="btn-primary text-sm text-center">Client Login</a>
            </div>
        </div>
    </div>
</header>

{{ $slot }}

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            <div>
                <img src="/logo.png" alt="Mahadev Pharma" class="h-12 brightness-0 invert mb-4">
                <p class="text-sm">Trusted pharmaceutical distributor serving pharmacies across Telangana and Andhra Pradesh with quality medicines.</p>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Quick Links</h4>
                <div class="flex flex-col gap-2 text-sm">
                    <a href="/about" class="hover:text-white transition">About Us</a>
                    <a href="/how-we-work" class="hover:text-white transition">How We Work</a>
                    <a href="/blog" class="hover:text-white transition">Blog</a>
                    <a href="/contact" class="hover:text-white transition">Contact</a>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Contact</h4>
                <div class="flex flex-col gap-2 text-sm">
                    <p>2-3-166 & 17, 1st Floor, Taj Plaza</p>
                    <p>Nallagopalpet Main Road</p>
                    <p>Secunderabad, Telangana 500003</p>
                    <p class="mt-2">Phone: 8919383362</p>
                    <p>Email: info@mahadevpharma.in</p>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Legal</h4>
                <div class="flex flex-col gap-2 text-sm">
                    <p>DL No: 345/HD/AP/2002</p>
                    <p>DL No: 346/HD/AP/2002</p>
                    <p>GSTIN: 36ABBPT6277A1ZN</p>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-12 pt-8 text-center text-sm">
            <p>&copy; {{ date('Y') }} Mahadev Pharma. All rights reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>
