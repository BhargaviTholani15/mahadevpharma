<x-layouts.app title="Notifications">
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">Notifications</h1></div>
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">@csrf<button class="btn-secondary">Mark All Read</button></form>
    </div>
    <div class="space-y-2">
        @forelse($notifications as $n)
        <div class="card flex items-start justify-between gap-4 {{ $n->read_at ? 'opacity-60' : '' }}">
            <div>
                <p class="text-sm font-medium text-gray-900">{{ $n->title ?? 'Notification' }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $n->message ?? $n->data ?? '' }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
            </div>
            @if(!$n->read_at)
            <form method="POST" action="{{ route('notifications.mark-read', $n) }}">@csrf<button class="btn-secondary btn-sm">Read</button></form>
            @endif
        </div>
        @empty
        <div class="card text-center py-12 text-gray-400">No notifications</div>
        @endforelse
    </div>
    <div>{{ $notifications->links() }}</div>
</div>
</x-layouts.app>
