<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user:id,full_name,email');

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->get('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($entityType = $request->get('entity_type')) {
            $query->where('entity_type', $entityType);
        }

        if ($entityId = $request->get('entity_id')) {
            $query->where('entity_id', $entityId);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->where('created_at', '<=', $dateTo);
        }

        $logs = $query->orderByDesc('created_at')->paginate($request->get('per_page', 25));

        return response()->json($logs);
    }

    public function show(ActivityLog $log): JsonResponse
    {
        $log->load('user:id,full_name,email');

        return response()->json(['log' => $log]);
    }
}
