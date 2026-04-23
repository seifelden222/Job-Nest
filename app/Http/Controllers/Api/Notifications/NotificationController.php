<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notifications\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate((int) ($validated['per_page'] ?? 15));

        return response()->json([
            'message' => 'Notifications fetched successfully.',
            'data' => NotificationResource::collection($notifications),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Unread notifications count fetched successfully.',
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, DatabaseNotification $notification): JsonResponse
    {
        $ownedNotification = $this->resolveOwnedNotification($request, $notification);

        if (! $ownedNotification) {
            return response()->json([
                'message' => 'Notification not found.',
            ], 404);
        }

        if ($ownedNotification->read_at === null) {
            $ownedNotification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => new NotificationResource($ownedNotification->fresh()),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $markedCount = $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'data' => [
                'marked_count' => $markedCount,
            ],
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function destroy(Request $request, DatabaseNotification $notification): JsonResponse
    {
        $ownedNotification = $this->resolveOwnedNotification($request, $notification);

        if (! $ownedNotification) {
            return response()->json([
                'message' => 'Notification not found.',
            ], 404);
        }

        $ownedNotification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully.',
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    private function resolveOwnedNotification(Request $request, DatabaseNotification $notification): ?DatabaseNotification
    {
        return $request->user()
            ->notifications()
            ->whereKey($notification->id)
            ->first();
    }
}
