<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MarkNotificationsReadRequest;
use App\Models\ClientNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ClientNotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = ClientNotification::forUser(Auth::id())
            ->latest()
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'unread_count' => ClientNotification::forUser(Auth::id())->unread()->count(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => ClientNotification::forUser(Auth::id())->unread()->count(),
            ],
        ]);
    }

    public function markRead(MarkNotificationsReadRequest $request): JsonResponse
    {
        $query = ClientNotification::forUser(Auth::id())->unread();

        $ids = $request->validated('ids');
        if (! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $query->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
