@php
    $user = auth()->user();
    $role = $user->role?->slug ?? '';
    $isAdmin = $role === 'admin';
    $isStaff = $role === 'staff';
    $isClient = $role === 'client';
    $isAdminOrStaff = $isAdmin || $isStaff;
    $portal = session('portal', 'admin');

    $navSections = [];

    if ($portal === 'vendor') {
        $navSections = [
            ['items' => [
                ['label' => 'Dashboard', 'href' => route('vendor.dashboard'), 'icon' => 'home'],
                ['label' => 'Place Order', 'href' => route('vendor.place-order'), 'icon' => 'shopping-cart'],
                ['label' => 'My Orders', 'href' => route('vendor.orders'), 'icon' => 'clipboard-list'],
                ['label' => 'My Invoices', 'href' => route('vendor.invoices'), 'icon' => 'file-text'],
                ['label' => 'My Payments', 'href' => route('vendor.payments'), 'icon' => 'credit-card'],
                ['label' => 'My Deliveries', 'href' => route('vendor.deliveries'), 'icon' => 'truck'],
                ['label' => 'My Returns', 'href' => route('vendor.returns'), 'icon' => 'rotate-ccw'],
                ['label' => 'My Ledger', 'href' => route('vendor.ledger'), 'icon' => 'receipt'],
                ['label' => 'Notifications', 'href' => route('vendor.notifications'), 'icon' => 'bell'],
            ]],
        ];
    } else {
        $navSections = [
            ['items' => [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'layout-dashboard'],
            ]],
            ['title' => 'Product Management', 'roles' => ['admin','staff'], 'items' => [
                ['label' => 'Products', 'href' => route('products.index'), 'icon' => 'pill'],
                ['label' => 'Categories', 'href' => route('categories.index'), 'icon' => 'folder-tree'],
                ['label' => 'Brands', 'href' => route('brands.index'), 'icon' => 'tags'],
                ['label' => 'HSN Codes', 'href' => route('hsn-codes.index'), 'icon' => 'hash'],
                ['label' => 'Dosage Forms', 'href' => route('dosage-forms.index'), 'icon' => 'pill'],
                ['label' => 'Strengths', 'href' => route('strengths.index'), 'icon' => 'flask-conical'],
                ['label' => 'Pack Sizes', 'href' => route('pack-sizes.index'), 'icon' => 'package'],
                ['label' => 'Drug Schedules', 'href' => route('drug-schedules.index'), 'icon' => 'shield'],
                ['label' => 'Storage Conditions', 'href' => route('storage-conditions.index'), 'icon' => 'thermometer'],
                ['label' => 'Inventory', 'href' => route('inventory.index'), 'icon' => 'warehouse'],
                ['label' => 'Clients', 'href' => route('clients.index'), 'icon' => 'users'],
            ]],
            ['title' => 'Sales & Orders', 'roles' => ['admin','staff'], 'items' => [
                ['label' => 'Orders', 'href' => route('orders.index'), 'icon' => 'shopping-cart'],
                ['label' => 'Invoices', 'href' => route('invoices.index'), 'icon' => 'file-text'],
                ['label' => 'Payments', 'href' => route('payments.index'), 'icon' => 'credit-card'],
                ['label' => 'Deliveries', 'href' => route('deliveries.index'), 'icon' => 'truck'],
                ['label' => 'Delivery Agents', 'href' => route('delivery-agents.index'), 'icon' => 'user-plus'],
                ['label' => 'Returns', 'href' => route('returns.index'), 'icon' => 'rotate-ccw'],
            ]],
            ['title' => 'Supply Chain', 'roles' => ['admin','staff'], 'items' => [
                ['label' => 'Suppliers', 'href' => route('suppliers.index'), 'icon' => 'users'],
                ['label' => 'Purchase Orders', 'href' => route('purchase-orders.index'), 'icon' => 'clipboard-list'],
                ['label' => 'Stock Transfers', 'href' => route('stock-transfers.index'), 'icon' => 'rotate-ccw'],
                ['label' => 'Warehouses', 'href' => route('warehouses.index'), 'icon' => 'warehouse'],
            ]],
            ['title' => 'Reports', 'roles' => ['admin','staff'], 'items' => [
                ['label' => 'Reports', 'href' => route('reports.index'), 'icon' => 'bar-chart-3'],
                ['label' => 'GST Reports', 'href' => route('gst-reports.index'), 'icon' => 'indian-rupee'],
                ['label' => 'Bulk Operations', 'href' => route('bulk.index'), 'icon' => 'upload'],
            ]],
            ['title' => 'Administration', 'roles' => ['admin'], 'items' => [
                ['label' => 'User Management', 'href' => route('users.index'), 'icon' => 'users'],
                ['label' => 'Roles & Permissions', 'href' => route('roles.index'), 'icon' => 'shield'],
                ['label' => 'Notifications', 'href' => route('notifications.index'), 'icon' => 'bell'],
                ['label' => 'Audit Logs', 'href' => route('audit-logs.index'), 'icon' => 'receipt'],
                ['label' => 'Settings', 'href' => route('settings.index'), 'icon' => 'settings'],
            ]],
        ];
    }

    $iconMap = [
        'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'layout-dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
        'pill' => '<path d="m10.5 1.5 3 3L5.5 12.5l-3-3z"/><path d="m13.5 4.5 3 3"/><path d="m2.5 9.5 3 3"/>',
        'folder-tree' => '<path d="M13 10h7a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2.5a1 1 0 0 1-.8-.4l-.9-1.2A1 1 0 0 0 15 3h-2a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1z"/><path d="M13 21h7a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-2.9a1 1 0 0 1-.8-.4l-.9-1.2a1 1 0 0 0-.8-.4H13a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1z"/><path d="M3 3v2"/><path d="M3 3v15a1 1 0 0 0 1 1h6"/><path d="M3 8h7"/>',
        'tags' => '<path d="M9 5H2v7l6.3 6.3a2.1 2.1 0 0 0 3 0L15 14.6a2.1 2.1 0 0 0 0-3L9 5z"/><path d="M6 9.5a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1z"/>',
        'hash' => '<line x1="4" x2="20" y1="9" y2="9"/><line x1="4" x2="20" y1="15" y2="15"/><line x1="10" x2="8" y1="3" y2="21"/><line x1="16" x2="14" y1="3" y2="21"/>',
        'flask-conical' => '<path d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/><path d="M8.5 2h7"/><path d="M7 16.5h10"/>',
        'package' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'shield' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>',
        'thermometer' => '<path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>',
        'warehouse' => '<path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/><rect width="12" height="12" x="6" y="10"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'shopping-cart' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.66-7.88H5.12"/>',
        'clipboard-list' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
        'credit-card' => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
        'truck' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 13.52 9H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
        'user-plus' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/>',
        'rotate-ccw' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
        'receipt' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/>',
        'bar-chart-3' => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
        'indian-rupee' => '<path d="M6 3h12"/><path d="M6 8h12"/><path d="m6 13 8.5 8"/><path d="M6 13h3"/><path d="M9 13c6.667 0 6.667-10 0-10"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>',
        'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
        'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
    ];
@endphp

<aside class="fixed left-0 top-0 h-screen z-40 flex flex-col bg-white/90 backdrop-blur-xl border-r border-gray-200/50 shadow-[4px_0_24px_-2px_rgba(0,0,0,0.05)] transition-all duration-200 overflow-hidden"
       :class="[
           sidebarOpen ? 'w-64' : 'w-[72px]',
           mobileMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
       ]">

    {{-- Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-gray-200/50 flex-shrink-0">
        <img src="/logo.png" alt="Mahadev Pharma" class="object-contain transition-all" :class="sidebarOpen ? 'h-10' : 'h-9 w-9'">
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        @foreach($navSections as $section)
            @if(isset($section['roles']) && !in_array($role, $section['roles']))
                @continue
            @endif

            @if(isset($section['title']))
                <div x-show="sidebarOpen" class="pt-4 pb-1.5 px-3">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $section['title'] }}</span>
                </div>
                <div x-show="!sidebarOpen" class="pt-3 pb-1"></div>
            @endif

            @foreach($section['items'] as $item)
                @php
                    $isActive = request()->url() === $item['href'] || (str_starts_with(request()->url(), $item['href']) && $item['href'] !== route('dashboard') && $item['href'] !== route('vendor.dashboard', [], false));
                    $icon = $iconMap[$item['icon']] ?? '';
                @endphp
                <a href="{{ $item['href'] }}"
                   class="sidebar-link {{ $isActive ? 'sidebar-link-active' : 'sidebar-link-inactive' }}"
                   @if(!$isActive) title="{{ $item['label'] }}" @endif>
                    <svg class="w-5 h-5 flex-shrink-0 {{ $isActive ? 'text-green-600' : '' }}" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">{{ $item['label'] }}</span>
                    @if($isActive)
                    <span class="absolute left-0 w-1 h-8 bg-green-600 rounded-r-full"></span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    {{-- Bottom actions --}}
    <div class="border-t border-gray-200/50 p-3 space-y-1 flex-shrink-0">
        <button @click="sidebarOpen = !sidebarOpen"
                class="sidebar-link sidebar-link-inactive w-full">
            <svg class="w-5 h-5 transition-transform" :class="!sidebarOpen && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            <span x-show="sidebarOpen">Collapse</span>
        </button>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link text-red-600 hover:bg-red-50 w-full">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                <span x-show="sidebarOpen">Logout</span>
            </button>
        </form>
    </div>
</aside>
