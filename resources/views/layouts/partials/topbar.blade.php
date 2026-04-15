<header class="sticky top-0 z-20 bg-white/80 backdrop-blur-xl border-b border-gray-200/50 h-16 flex items-center justify-between px-4 lg:px-6">
    <div class="flex items-center gap-3">
        <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        </button>
        <h2 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Dashboard' }}</h2>
    </div>
    <div class="flex items-center gap-4">
        <a href="{{ session('portal') === 'vendor' ? route('vendor.notifications') : route('notifications.index') }}" class="relative p-2 rounded-lg hover:bg-gray-100 text-gray-500">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        </a>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-semibold text-sm">
                {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
            </div>
            <div class="hidden sm:block">
                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->full_name }}</p>
                <p class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role?->name ?? '') }}</p>
            </div>
        </div>
    </div>
</header>
