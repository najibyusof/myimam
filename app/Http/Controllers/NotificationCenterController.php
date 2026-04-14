<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = (string) $request->query('status', 'all');
        $category = (string) $request->query('category', 'all');

        $notifications = $user->appNotifications()
            ->latest('created_at')
            ->get()
            ->map(function (Notification $notification) {
                return [
                    'model' => $notification,
                    'id' => $notification->id,
                    'title' => $this->notificationTitle($notification),
                    'message' => $this->notificationMessage($notification),
                    'category_key' => $this->notificationCategoryKey($notification),
                    'category_label' => $this->notificationCategoryLabel($notification),
                    'created_at_human' => $notification->created_at?->diffForHumans() ?? 'recently',
                    'read_at' => $notification->read_at,
                    'is_read' => $notification->read_at !== null,
                ];
            });

        $categories = $notifications
            ->map(fn (array $item) => [
                'key' => $item['category_key'],
                'label' => $item['category_label'],
            ])
            ->unique('key')
            ->sortBy('label')
            ->values();

        if (in_array($status, ['read', 'unread'], true)) {
            $notifications = $notifications->filter(function (array $item) use ($status) {
                return $status === 'read' ? $item['is_read'] : !$item['is_read'];
            })->values();
        }

        if ($category !== 'all') {
            $notifications = $notifications
                ->filter(fn (array $item) => $item['category_key'] === $category)
                ->values();
        }

        $paginated = $this->paginateCollection($notifications, (int) $request->query('per_page', 15), $request);

        return view('notifications.index', [
            'notifications' => $paginated,
            'status' => $status,
            'category' => $category,
            'categories' => $categories,
            'unreadCount' => $user->unreadNotificationsCount(),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorizeNotificationOwner($request, $notification);
        $notification->markAsRead();

        return redirect()->back()->with('status', 'Notification marked as read.');
    }

    public function markAsUnread(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorizeNotificationOwner($request, $notification);
        $notification->markAsUnread();

        return redirect()->back()->with('status', 'Notification marked as unread.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->markAllNotificationsAsRead();

        return redirect()->back()->with('status', 'All notifications marked as read.');
    }

    private function authorizeNotificationOwner(Request $request, Notification $notification): void
    {
        abort_unless(
            $notification->notifiable_id === $request->user()->id
                && $notification->notifiable_type === $request->user()::class,
            403,
            'Unauthorized'
        );
    }

    private function notificationTitle(Notification $notification): string
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return (string) ($data['title'] ?? $data['subject'] ?? Str::headline(class_basename($notification->type)));
    }

    private function notificationMessage(Notification $notification): string
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return (string) ($data['message'] ?? $data['body'] ?? 'No additional details.');
    }

    private function notificationCategoryLabel(Notification $notification): string
    {
        $data = is_array($notification->data) ? $notification->data : [];

        if (!empty($data['category'])) {
            return Str::headline((string) $data['category']);
        }

        $typeName = Str::of(class_basename($notification->type))
            ->replace('Notification', '')
            ->snake(' ')
            ->headline()
            ->value();

        return $typeName !== '' ? $typeName : 'General';
    }

    private function notificationCategoryKey(Notification $notification): string
    {
        return Str::slug($this->notificationCategoryLabel($notification), '-');
    }

    private function paginateCollection($collection, int $perPage, Request $request): LengthAwarePaginator
    {
        $safePerPage = min(max($perPage, 5), 50);
        $page = Paginator::resolveCurrentPage();
        $items = $collection
            ->slice(($page - 1) * $safePerPage, $safePerPage)
            ->values();

        return new Paginator(
            $items,
            $collection->count(),
            $safePerPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
