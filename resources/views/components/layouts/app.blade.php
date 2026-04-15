<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Mahadev Pharma</title>
    <link rel="icon" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: true, mobileMenu: false }">

{{-- Mobile overlay --}}
<div x-show="mobileMenu" x-transition.opacity @click="mobileMenu = false"
     class="fixed inset-0 z-30 bg-black/40 lg:hidden" style="display:none"></div>

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    @include('layouts.partials.sidebar')

    {{-- Main content --}}
    <div class="flex-1 transition-all duration-200" :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-[72px]'">
        {{-- Top bar --}}
        @include('layouts.partials.topbar')

        {{-- Page content --}}
        <main class="p-4 lg:p-6">
            {{-- Flash messages --}}
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mb-4 rounded-xl p-4 bg-green-50 border border-green-200 text-green-700 text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true }" x-show="show"
                 class="mb-4 rounded-xl p-4 bg-red-50 border border-red-200 text-red-700 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="text-red-500 hover:text-red-700">&times;</button>
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
