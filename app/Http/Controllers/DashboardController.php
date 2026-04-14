<?php

namespace App\Http\Controllers;

use App\Models\BaucarBayaran;
use App\Models\LogAktiviti;
use App\Models\Masjid;
use App\Models\Notification;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $masjidId = $user->hasRole('Admin') ? null : $user->id_masjid;
        $dashboardRole = $this->resolveDashboardRole($user);

        return view('dashboard', [
            'stats' => $this->buildStats($user, $dashboardRole, $masjidId),
            'recentActivities' => $this->buildRecentActivities($masjidId),
            'notificationPreview' => $this->buildNotificationPreview($masjidId),
            'activityChart' => $this->buildActivityChart($masjidId),
            'dashboardRole' => $dashboardRole,
            'contextLabel' => $masjidId ? (Masjid::query()->whereKey($masjidId)->value('nama') ?? 'Assigned Masjid') : 'All Masjids',
        ]);
    }

    private function buildStats(User $user, string $dashboardRole, ?int $masjidId): array
    {
        $unreadNotifications = $this->notificationQuery($masjidId)->whereNull('read_at')->count();
        $failedNotifications = $this->failedNotificationLogQuery($masjidId)->count();
        $draftVouchers = $this->draftVoucherQuery($masjidId)->count();
        $activityCount = $this->logAktivitiQuery($masjidId)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        $activeUsers = $this->userQuery($masjidId)->active()->count();
        $usersWithoutRoles = $this->userQuery($masjidId)->whereDoesntHave('roles')->count();
        $myActivityCount = LogAktiviti::query()
            ->where('id_user', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $myUnreadNotifications = Notification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return match ($dashboardRole) {
            'Admin' => [
                [
                    'label' => 'Active Users',
                    'value' => $activeUsers,
                    'hint' => 'Users currently marked active across all masjids',
                    'tone' => 'sky',
                ],
                [
                    'label' => 'Activities (24h)',
                    'value' => $activityCount,
                    'hint' => 'Actions logged in the last 24 hours',
                    'tone' => 'emerald',
                ],
                [
                    'label' => 'Open Alerts',
                    'value' => $failedNotifications + $draftVouchers,
                    'hint' => 'Failed notification deliveries and draft vouchers',
                    'tone' => 'amber',
                ],
            ],
            'Manager' => [
                [
                    'label' => 'Team Active Users',
                    'value' => $activeUsers,
                    'hint' => 'Active users in your scope',
                    'tone' => 'sky',
                ],
                [
                    'label' => 'Pending Approvals',
                    'value' => $draftVouchers,
                    'hint' => 'Draft vouchers waiting for review',
                    'tone' => 'amber',
                ],
                [
                    'label' => 'Unread Notifications',
                    'value' => $unreadNotifications,
                    'hint' => 'Operational updates requiring attention',
                    'tone' => 'emerald',
                ],
            ],
            'FinanceOfficer' => [
                [
                    'label' => 'Draft Vouchers',
                    'value' => $draftVouchers,
                    'hint' => 'Payment requests pending approval',
                    'tone' => 'amber',
                ],
                [
                    'label' => 'Failed Deliveries',
                    'value' => $failedNotifications,
                    'hint' => 'Notification deliveries that require retry',
                    'tone' => 'sky',
                ],
                [
                    'label' => 'Finance Activities (24h)',
                    'value' => $activityCount,
                    'hint' => 'Recent operations logged in your scope',
                    'tone' => 'emerald',
                ],
            ],
            'Auditor' => [
                [
                    'label' => 'Audit Events (24h)',
                    'value' => $activityCount,
                    'hint' => 'Recent actions available for audit trail review',
                    'tone' => 'emerald',
                ],
                [
                    'label' => 'Unassigned Users',
                    'value' => $usersWithoutRoles,
                    'hint' => 'Accounts without RBAC role assignment',
                    'tone' => 'amber',
                ],
                [
                    'label' => 'Alert Incidents',
                    'value' => $failedNotifications + $draftVouchers,
                    'hint' => 'Items that may impact compliance checks',
                    'tone' => 'sky',
                ],
            ],
            'MasjidOfficer' => [
                [
                    'label' => 'Masjid Active Users',
                    'value' => $activeUsers,
                    'hint' => 'Active accounts under your masjid',
                    'tone' => 'sky',
                ],
                [
                    'label' => 'Local Activities (24h)',
                    'value' => $activityCount,
                    'hint' => 'New actions logged for your masjid',
                    'tone' => 'emerald',
                ],
                [
                    'label' => 'Masjid Alerts',
                    'value' => $failedNotifications + $draftVouchers,
                    'hint' => 'Local alerts requiring immediate follow-up',
                    'tone' => 'amber',
                ],
            ],
            default => [
                [
                    'label' => 'My Unread Notifications',
                    'value' => $myUnreadNotifications,
                    'hint' => 'Updates sent directly to your account',
                    'tone' => 'sky',
                ],
                [
                    'label' => 'My Activities (7d)',
                    'value' => $myActivityCount,
                    'hint' => 'Actions you performed in the last week',
                    'tone' => 'emerald',
                ],
                [
                    'label' => 'System Alerts In Scope',
                    'value' => $failedNotifications + $draftVouchers,
                    'hint' => 'High-priority alerts visible in your context',
                    'tone' => 'amber',
                ],
            ],
        };
    }

    private function resolveDashboardRole(User $user): string
    {
        foreach (['Admin', 'Manager', 'FinanceOfficer', 'Auditor', 'MasjidOfficer', 'User'] as $roleName) {
            if ($user->hasRole($roleName)) {
                return $roleName;
            }
        }

        return 'User';
    }

    private function buildRecentActivities(?int $masjidId): array
    {
        return $this->logAktivitiQuery($masjidId)
            ->with(['user:id,name', 'masjid:id,nama'])
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(function (LogAktiviti $activity) {
                $type = match (strtoupper((string) $activity->jenis)) {
                    'CREATE', 'CIPTA' => 'success',
                    'UPDATE', 'KEMASKINI' => 'info',
                    'DELETE', 'PADAM' => 'warning',
                    default => 'neutral',
                };

                $title = trim(collect([$activity->modul, $activity->aksi])->filter()->implode(' · '));
                if ($title === '') {
                    $title = 'System activity recorded';
                }

                return [
                    'title' => $title,
                    'actor' => $activity->user?->name ?? 'System',
                    'location' => $activity->masjid?->nama,
                    'time' => $activity->created_at?->diffForHumans() ?? 'recently',
                    'type' => $type,
                ];
            })
            ->all();
    }

    private function buildNotificationPreview(?int $masjidId): array
    {
        return $this->notificationQuery($masjidId)
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(function (Notification $notification) {
                $data = is_array($notification->data) ? $notification->data : [];

                return [
                    'title' => (string) ($data['title'] ?? $data['subject'] ?? 'System Notification'),
                    'message' => (string) ($data['message'] ?? $data['body'] ?? 'No preview available.'),
                    'time' => $notification->created_at?->diffForHumans() ?? 'recently',
                    'is_read' => $notification->read_at !== null,
                ];
            })
            ->all();
    }

    private function draftVoucherQuery(?int $masjidId): Builder
    {
        $query = BaucarBayaran::query()->draft();

        return $masjidId ? $query->byMasjid($masjidId) : $query;
    }

    private function userQuery(?int $masjidId): Builder
    {
        $query = User::query();

        return $masjidId ? $query->byMasjid($masjidId) : $query;
    }

    private function logAktivitiQuery(?int $masjidId): Builder
    {
        $query = LogAktiviti::query();

        return $masjidId ? $query->byMasjid($masjidId) : $query;
    }

    private function notificationQuery(?int $masjidId): Builder
    {
        $query = Notification::query();

        if (!$masjidId) {
            return $query;
        }

        return $query->whereHasMorph('notifiable', [User::class], function (Builder $builder) use ($masjidId) {
            $builder->where('id_masjid', $masjidId);
        });
    }

    private function failedNotificationLogQuery(?int $masjidId): Builder
    {
        $query = NotificationLog::query()->where('status', 'failed');

        if (!$masjidId) {
            return $query;
        }

        return $query->whereHasMorph('notifiable', [User::class], function (Builder $builder) use ($masjidId) {
            $builder->where('id_masjid', $masjidId);
        });
    }

    private function buildActivityChart(?int $masjidId): array
    {
        $labels = [];
        $counts = [];

        foreach (range(6, 0) as $offset) {
            $day = now()->subDays($offset);
            $labels[] = $day->format('D');
            $counts[] = $this->logAktivitiQuery($masjidId)
                ->whereDate('created_at', $day->toDateString())
                ->count();
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
        ];
    }
}
