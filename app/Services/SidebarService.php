<?php

namespace App\Services;

use App\Models\BaucarBayaran;
use App\Models\TenantSubscription;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Route;

/**
 * SidebarService
 *
 * Provides role-based and permission-aware menu filtering for sidebar navigation.
 *
 * Features:
 * - Loads menu config from config/sidebar.php
 * - Filters items based on user permissions and roles
 * - SuperAdmin bypass (sees all items)
 * - Route matching for active state
 * - Multi-tenant safe (no cross-tenant data exposure)
 */
class SidebarService
{
    protected array $menuConfig = [];

    public function __construct()
    {
        $this->menuConfig = config('sidebar', []);
    }

    private function user(): ?Authenticatable
    {
        return auth()->user();
    }

    /**
     * Get filtered menu sections based on current user's permissions.
     *
     * @return array Array of sections with authorized items
     */
    public function getFilteredMenu(): array
    {
        if (!$this->user()) {
            return [];
        }

        return collect($this->menuConfig)
            ->map(fn($section) => $this->filterSection($section))
            ->filter(fn($section) => !empty($section['items']))
            ->values()
            ->toArray();
    }

    /**
     * Process a single menu item: handles parent items (with children) and regular items.
     * Returns null if the item should be hidden.
     */
    private function processItem(array $item): ?array
    {
        // Parent item with nested children
        if (!empty($item['children'])) {
            $children = collect($item['children'])
                ->filter(fn($child) => $this->isItemAuthorized($child))
                ->map(fn($child) => $this->enrichItem($child))
                ->values()
                ->toArray();

            if (empty($children)) {
                return null; // All children unauthorized → hide parent
            }

            $isActive = collect($children)->contains(fn($c) => $c['isActive'] ?? false);

            return array_merge($item, [
                'key'       => $item['key'] ?? (string) str($item['title'])->slug('_'),
                'routeName' => null,
                'isActive'  => $isActive,
                'badge'     => null,
                'children'  => $children,
            ]);
        }

        // Regular item
        if (!$this->isItemAuthorized($item)) {
            return null;
        }

        return $this->enrichItem($item);
    }

    /**
     * Filter a menu section to only include authorized items.
     */
    private function filterSection(array $section): array
    {
        $items = collect($section['items'] ?? [])
            ->map(fn($item) => $this->processItem($item))
            ->filter()
            ->values()
            ->toArray();

        $isActive = collect($items)->contains(fn($item) => $item['isActive'] ?? false);
        $sectionBadgeValue = collect($items)
            ->map(fn($item) => (int) data_get($item, 'badge.value', 0))
            ->sum();
        $sectionBadgeTone = collect($items)
            ->map(fn($item) => data_get($item, 'badge.tone'))
            ->filter()
            ->first() ?? 'slate';

        return [
            'key' => (string) ($section['key'] ?? str($section['section'] ?? 'menu')->slug('_')),
            'section' => $section['section'] ?? 'Menu',
            'collapsible' => (bool) ($section['collapsible'] ?? true),
            'default_open' => (bool) ($section['default_open'] ?? false),
            'isOpen' => $isActive || (bool) ($section['default_open'] ?? false),
            'isActive' => $isActive,
            'badge' => $sectionBadgeValue > 0 ? [
                'value' => $sectionBadgeValue,
                'tone' => $sectionBadgeTone,
            ] : null,
            'items' => $items,
        ];
    }

    /**
     * Check if a menu item is authorized for the current user.
     */
    private function isItemAuthorized(array $item): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        // Parent items (with children) are containers — children are individually authorized.
        $isParent = !empty($item['children']);

        // Never expose or render menu items with non-existent routes.
        if (!$isParent && !$this->routeExists($item['route'] ?? null)) {
            return false;
        }

        // SuperAdmin sees everything
        if ($user->hasRole('superadmin') || $user->hasRole('SuperAdmin') || $user->peranan === 'superadmin') {
            return true;
        }

        // If neither role nor permission specified, show to all authenticated users
        if (empty($item['role']) && empty($item['permission'])) {
            return true;
        }

        // Check role authorization (OR logic)
        if (!empty($item['role'])) {
            if ($this->userHasRole($item['role'])) {
                return true;
            }
        }

        // Check permission authorization (OR logic)
        if (!empty($item['permission'])) {
            if ($this->userHasPermission($item['permission'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enrich an item with computed properties (isActive, etc.).
     */
    private function enrichItem(array $item): array
    {
        $badge = $this->resolveBadge($item['badge'] ?? null);
        $routeName = $this->resolveRouteName($item['route'] ?? null);

        return array_merge($item, [
            'routeName' => $routeName,
            'isActive' => $this->isRouteActive($item['route'] ?? null),
            'badge' => $badge,
        ]);
    }

    /**
     * Check if a route is currently active.
     */
    private function isRouteActive(string|array|null $route): bool
    {
        if (!$route) {
            return false;
        }

        if (is_string($route)) {
            return request()->routeIs($route . '*');
        }

        foreach ($route as $routeName) {
            if (request()->routeIs($routeName . '*')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has one or more of the specified roles.
     */
    private function userHasRole($roles): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has one or more of the specified permissions.
     */
    private function userHasPermission($permissions): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a route actually exists in the application.
     */
    private function routeExists(string|array|null $route): bool
    {
        if (!$route) {
            return false;
        }

        if (is_string($route)) {
            return Route::has($route);
        }

        foreach ($route as $routeName) {
            if (is_string($routeName) && Route::has($routeName)) {
                return true;
            }
        }

        return false;
    }

    private function resolveRouteName(string|array|null $route): ?string
    {
        if (!$route) {
            return null;
        }

        if (is_string($route)) {
            return Route::has($route) ? $route : null;
        }

        foreach ($route as $routeName) {
            if (is_string($routeName) && Route::has($routeName)) {
                return $routeName;
            }
        }

        return null;
    }

    private function resolveBadge(array|null $badgeConfig): ?array
    {
        if (!$badgeConfig || empty($badgeConfig['type'])) {
            return null;
        }

        $type = $badgeConfig['type'];
        $tone = $badgeConfig['tone'] ?? 'slate';

        $value = match ($type) {
            'pending_baucar'        => $this->pendingBaucarCount(),
            'unread_notifications'  => $this->unreadNotificationsCount(),
            'expired_subscriptions' => $this->expiredSubscriptionsCount(),
            default                 => null,
        };

        // Always return the badge config (with type) so Alpine can poll for live updates.
        // The badge UI uses x-show to hide zero-value badges reactively.
        return [
            'type'  => $type,
            'value' => max(0, (int) ($value ?? 0)),
            'tone'  => $tone,
        ];
    }

    private function pendingBaucarCount(): int
    {
        $user = $this->user();
        if (!$user) {
            return 0;
        }

        $query = BaucarBayaran::query()->draft();

        $isSuperAdmin = $user->hasRole('superadmin') || $user->hasRole('SuperAdmin') || $user->peranan === 'superadmin';
        if (!$isSuperAdmin && !empty($user->id_masjid)) {
            $query->byMasjid((int) $user->id_masjid);
        }

        return (int) $query->count();
    }

    private function unreadNotificationsCount(): int
    {
        $user = $this->user();
        if (!$user) {
            return 0;
        }

        return (int) \App\Models\Notification::query()
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->getKey())
            ->whereNull('read_at')
            ->count();
    }

    private function expiredSubscriptionsCount(): int
    {
        $user = $this->user();
        if (!$user) {
            return 0;
        }

        // Only superadmins manage subscriptions globally.
        $isSuperAdmin = $user->hasRole('superadmin') || $user->hasRole('SuperAdmin') || $user->peranan === 'superadmin';
        if (!$isSuperAdmin) {
            return 0;
        }

        return (int) TenantSubscription::query()
            ->where(function ($q) {
                $q->where('status', 'expired')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'active')
                         ->whereDate('end_date', '<', now());
                  });
            })
            ->count();
    }

    public function iconSvg(string $iconName): string
    {
        $icons = [
            'home' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75V19.5a2.25 2.25 0 0 0 2.25 2.25h3.75v-6a1.5 1.5 0 0 1 1.5-1.5h0a1.5 1.5 0 0 1 1.5 1.5v6h3.75a2.25 2.25 0 0 0 2.25-2.25V9.75" /></svg>',
            'bell' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 1 0-12 0v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.456 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>',
            'wallet' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12V7.5A2.25 2.25 0 0 0 18.75 5.25H5.25A2.25 2.25 0 0 0 3 7.5v9A2.25 2.25 0 0 0 5.25 18.75h13.5A2.25 2.25 0 0 0 21 16.5V12Zm0 0h-3.75a1.5 1.5 0 1 0 0 3H21v-3Z" /></svg>',
            'trending-up' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.5 4.5L21.75 7.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5h5.25v5.25" /></svg>',
            'trending-down' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.5-4.5L21.75 16.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 16.5h5.25v-5.25" /></svg>',
            'file-text' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.25a2.25 2.25 0 0 0-2.25-2.25H8.25A2.25 2.25 0 0 0 6 6v12a2.25 2.25 0 0 0 2.25 2.25H15m4.5-6.75L15 18m0 0-4.5-4.5M15 18V9" /></svg>',
            'credit-card' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9.75v8.25A2.25 2.25 0 0 0 4.5 20.25h15a2.25 2.25 0 0 0 2.25-2.25V9.75M2.25 9.75A2.25 2.25 0 0 1 4.5 7.5h15a2.25 2.25 0 0 1 2.25 2.25" /></svg>',
            'calendar' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 5.25h15A1.5 1.5 0 0 1 21 6.75v12A1.5 1.5 0 0 1 19.5 20.25h-15A1.5 1.5 0 0 1 3 18.75v-12A1.5 1.5 0 0 1 4.5 5.25Z" /></svg>',
            'bar-chart-2' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7.5 15v-3m4.5 3V9m4.5 6V6" /></svg>',
            'settings' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.277a1.125 1.125 0 0 0 1.681.814l1.134-.664a1.125 1.125 0 0 1 1.39.173l1.832 1.832c.39.39.458.996.173 1.39l-.664 1.134a1.125 1.125 0 0 0 .814 1.681l1.277.213c.542.09.94.56.94 1.11v2.592c0 .55-.398 1.02-.94 1.11l-1.277.213a1.125 1.125 0 0 0-.814 1.681l.664 1.134a1.125 1.125 0 0 1-.173 1.39l-1.832 1.832a1.125 1.125 0 0 1-1.39.173l-1.134-.664a1.125 1.125 0 0 0-1.681.814l-.213 1.277a1.125 1.125 0 0 1-1.11.94h-2.592a1.125 1.125 0 0 1-1.11-.94l-.213-1.277a1.125 1.125 0 0 0-1.681-.814l-1.134.664a1.125 1.125 0 0 1-1.39-.173L3.54 18.66a1.125 1.125 0 0 1-.173-1.39l.664-1.134a1.125 1.125 0 0 0-.814-1.681L1.94 14.242a1.125 1.125 0 0 1-.94-1.11v-2.592c0-.55.398-1.02.94-1.11l1.277-.213a1.125 1.125 0 0 0 .814-1.681l-.664-1.134a1.125 1.125 0 0 1 .173-1.39L5.372 3.54a1.125 1.125 0 0 1 1.39-.173l1.134.664a1.125 1.125 0 0 0 1.681-.814l.213-1.277Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>',
            'users' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198v.001c0 .119-.005.238-.014.355M18 18.72a8.966 8.966 0 0 1-5.64 2.03 8.966 8.966 0 0 1-5.64-2.03m11.28 0a3 3 0 0 0-.94-3.197m0 0a3 3 0 0 0-4.68 2.718m5.62.479A9.094 9.094 0 0 1 12 21a9.094 9.094 0 0 1-5.742-2.28m0 0a3 3 0 0 1 .94-3.197m0 0a3 3 0 0 1 4.68 2.718M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>',
            'shield' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-3 0h15a1.5 1.5 0 0 1 1.5 1.5V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>',
        ];

        return $icons[$iconName] ?? $icons['home'];
    }

    /**
     * Get menu as flat array (useful for other components).
     */
    public function getMenuFlat(): array
    {
        return collect($this->getFilteredMenu())
            ->pluck('items')
            ->flatten(1)
            ->toArray();
    }

    /**
     * Check if user can access a specific menu item by route.
     */
    public function hasAccessToRoute(string $route): bool
    {
        $item = collect($this->menuConfig)
            ->pluck('items')
            ->flatten(1)
            ->firstWhere('route', $route);

        return $item ? $this->isItemAuthorized($item) : false;
    }

    /**
     * Get all live badge counts for the current user.
     * Used by the badge polling endpoint.
     */
    public function getBadgeCounts(): array
    {
        if (!$this->user()) {
            return [
                'pending_baucar'        => 0,
                'unread_notifications'  => 0,
                'expired_subscriptions' => 0,
            ];
        }

        return [
            'pending_baucar'        => $this->pendingBaucarCount(),
            'unread_notifications'  => $this->unreadNotificationsCount(),
            'expired_subscriptions' => $this->expiredSubscriptionsCount(),
        ];
    }
}
