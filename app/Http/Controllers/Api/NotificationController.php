<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * Get user's notifications (paginated)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min(max($request->integer('per_page', 20), 1), 100);

        $notifications = $user->appNotifications()
            ->latest('created_at')
            ->paginate($limit);

        return response()->json([
            'data' => NotificationResource::collection($notifications->items()),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
            'unread_count' => $user->unreadNotificationsCount(),
        ]);
    }

    /**
     * Get unread notifications only
     */
    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min(max($request->integer('per_page', 20), 1), 100);

        $notifications = $user->unreadAppNotifications()
            ->latest('created_at')
            ->paginate($limit);

        return response()->json([
            'data' => NotificationResource::collection($notifications->items()),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->notifiable_id !== $request->user()->id || $notification->notifiable_type !== $request->user()::class) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->notificationService->markAsRead($notification->id);

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->appNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->notifiable_id !== $request->user()->id || $notification->notifiable_type !== $request->user()::class) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll(Request $request): JsonResponse
    {
        $request->user()->appNotifications()->delete();

        return response()->json(['message' => 'All notifications deleted successfully']);
    }

    /**
     * Get notification preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        $preference = NotificationPreference::where('id_user', $user->id)->first()
                 ?? NotificationPreference::create(['id_user' => $user->id]);

        return response()->json($preference);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'telegram_notifications' => 'boolean',
            'fcm_token' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'notification_types' => 'nullable|array',
        ]);

        $user = $request->user();
        $preference = NotificationPreference::where('id_user', $user->id)->first();

        if (!$preference) {
            $preference = new NotificationPreference(['id_user' => $user->id]);
        }

        $preference->fill($validated);
        $preference->save();

        return response()->json([
            'message' => 'Preferences updated successfully',
            'data' => $preference,
        ]);
    }

    /**
     * Get notification statistics (admin only)
     */
    public function statistics(Request $request): JsonResponse
    {
        abort_unless($request->user()->peranan === 'superadmin', 403, 'Unauthorized');

        $from = $request->date('from');
        $to = $request->date('to');

        $stats = $this->notificationService->getStatistics($from, $to);

        return response()->json($stats);
    }
}
