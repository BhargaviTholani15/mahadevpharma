<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('notifications')
            ->where('user_id', auth()->id())
            ->where('channel', 'IN_APP');

        if ($request->has('read')) {
            if ($request->boolean('read')) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 25));

        return response()->json($notifications);
    }

    public function markRead(int $id): JsonResponse
    {
        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'READ',
            ]);

        if (!$updated) {
            return response()->json(['message' => 'Notification not found or already read'], 404);
        }

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllRead(): JsonResponse
    {
        $count = DB::table('notifications')
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'READ',
            ]);

        return response()->json(['message' => "{$count} notifications marked as read"]);
    }

    public function unreadCount(): JsonResponse
    {
        $count = DB::table('notifications')
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json(['message' => 'Notification deleted']);
    }
}
