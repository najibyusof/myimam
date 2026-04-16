<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Route;

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

    public function getFilteredMenu(): array
    {
        $user = $this->user();
        if (!$user) {
            return [];
        }

        $items = collect($this->normalizeConfigItems())
            ->map(function (array $item) {
                if (!$this->isItemAuthorized($item)) {
                    return null;
                }

                $routeName = $this->resolveRouteName($item['route'] ?? null);
                if (!$routeName) {
                    return null;
                }

                return [
                    'groupKey' => (string) ($item['group_key'] ?? $item['group'] ?? 'menu.group_general'),
                    'group' => $this->resolveLabel($item['group_key'] ?? null, $item['group'] ?? 'menu.group_general'),
                    'title' => $this->resolveLabel($item['title_key'] ?? null, $item['title'] ?? 'menu.dashboard'),
                    'route' => $item['route'],
                    'routeName' => $routeName,
                    'icon' => $item['icon'] ?? 'home',
                    'isActive' => $this->isRouteActive($item['route'] ?? null),
                ];
            })
            ->filter()
            ->values();

        if ($items->isEmpty()) {
            return [];
        }

        $orderedGroups = [];
        foreach ($items as $item) {
            $groupKey = (string) ($item['groupKey'] ?? $item['group']);
            if (!in_array($groupKey, $orderedGroups, true)) {
                $orderedGroups[] = $groupKey;
            }
        }

        return collect($orderedGroups)
            ->map(function (string $groupKey) use ($items) {
                $groupItems = $items->filter(fn(array $item) => ($item['groupKey'] ?? $item['group']) === $groupKey)->values()->toArray();
                $isActive = collect($groupItems)->contains(fn(array $item) => $item['isActive']);
                $groupLabel = (string) (collect($groupItems)->first()['group'] ?? 'Menu');

                return [
                    'key' => (string) str($groupKey)->slug('_'),
                    'section' => $groupLabel,
                    'collapsible' => $groupKey !== 'menu.group_general',
                    'default_open' => true,
                    'isOpen' => true,
                    'isActive' => $isActive,
                    'items' => $groupItems,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getMenuFlat(): array
    {
        return collect($this->getFilteredMenu())
            ->pluck('items')
            ->flatten(1)
            ->values()
            ->toArray();
    }

    public function hasAccessToRoute(string $route): bool
    {
        $item = collect($this->normalizeConfigItems())
            ->first(function (array $candidate) use ($route) {
                $routeConfig = $candidate['route'] ?? null;
                if (is_string($routeConfig)) {
                    return $routeConfig === $route;
                }

                return is_array($routeConfig) && in_array($route, $routeConfig, true);
            });

        return $item ? $this->isItemAuthorized($item) : false;
    }

    private function normalizeConfigItems(): array
    {
        if (empty($this->menuConfig)) {
            return [];
        }

        $first = $this->menuConfig[0] ?? [];
        if (!is_array($first)) {
            return [];
        }

        if (array_key_exists('items', $first)) {
            return collect($this->menuConfig)
                ->flatMap(function (array $section) {
                    $group = $section['section'] ?? 'Menu';
                    return collect($section['items'] ?? [])
                        ->map(function (array $item) use ($group) {
                            $item['group'] = $item['group'] ?? $group;
                            return $item;
                        });
                })
                ->values()
                ->toArray();
        }

        return collect($this->menuConfig)
            ->map(function (array $item) {
                $item['group'] = $item['group'] ?? 'Menu';
                return $item;
            })
            ->values()
            ->toArray();
    }

    private function resolveLabel(?string $key, string $fallback): string
    {
        if ($key && __($key) !== $key) {
            return __($key);
        }

        if (__($fallback) !== $fallback) {
            return __($fallback);
        }

        return $fallback;
    }

    private function isItemAuthorized(array $item): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Items flagged as superadmin_only are never shown to regular users
        if (!empty($item['superadmin_only']) && !$this->isSuperAdmin($user)) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (!$this->isTenantAllowed($item)) {
            return false;
        }

        $permissions = $item['permission'] ?? null;
        if (empty($permissions)) {
            return true;
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];
        foreach ($permissions as $permission) {
            if (is_string($permission) && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    private function isTenantAllowed(array $item): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $tenantScoped = $item['tenant_scoped'] ?? true;
        if (!$tenantScoped) {
            return true;
        }

        return !empty($user->id_masjid);
    }

    private function isSuperAdmin(Authenticatable $user): bool
    {
        return method_exists($user, 'hasRole')
            && ($user->hasRole('Superadmin') || $user->hasRole('SuperAdmin') || $user->hasRole('superadmin'));
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

    private function isRouteActive(string|array|null $route): bool
    {
        if (!$route) {
            return false;
        }

        if (is_string($route)) {
            return request()->routeIs($route) || request()->routeIs($route . '.*');
        }

        foreach ($route as $routeName) {
            if (!is_string($routeName)) {
                continue;
            }

            if (request()->routeIs($routeName) || request()->routeIs($routeName . '.*')) {
                return true;
            }
        }

        return false;
    }

    public function iconSvg(string $iconName): string
    {
        $icons = [
            'home' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75V19.5a2.25 2.25 0 0 0 2.25 2.25h3.75v-6a1.5 1.5 0 0 1 1.5-1.5h0a1.5 1.5 0 0 1 1.5 1.5v6h3.75a2.25 2.25 0 0 0 2.25-2.25V9.75" /></svg>',
            'wallet' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12V7.5A2.25 2.25 0 0 0 18.75 5.25H5.25A2.25 2.25 0 0 0 3 7.5v9A2.25 2.25 0 0 0 5.25 18.75h13.5A2.25 2.25 0 0 0 21 16.5V12Zm0 0h-3.75a1.5 1.5 0 1 0 0 3H21v-3Z" /></svg>',
            'arrow-down' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0 6-6m-6 6-6-6" /></svg>',
            'arrow-up' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0-6 6m6-6 6 6" /></svg>',
            'file' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.25a2.25 2.25 0 0 0-2.25-2.25H8.25A2.25 2.25 0 0 0 6 6v12a2.25 2.25 0 0 0 2.25 2.25H15m4.5-6.75L15 18m0 0-4.5-4.5M15 18V9" /></svg>',
            'chart' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7.5 15v-3m4.5 3V9m4.5 6V6" /></svg>',
            'users' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198v.001c0 .119-.005.238-.014.355M18 18.72a8.966 8.966 0 0 1-5.64 2.03 8.966 8.966 0 0 1-5.64-2.03m11.28 0a3 3 0 0 0-.94-3.197m0 0a3 3 0 0 0-4.68 2.718m5.62.479A9.094 9.094 0 0 1 12 21a9.094 9.094 0 0 1-5.742-2.28m0 0a3 3 0 0 1 .94-3.197m0 0a3 3 0 0 1 4.68 2.718M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>',
        ];

        return $icons[$iconName] ?? $icons['home'];
    }

    public function getBadgeCounts(): array
    {
        return [];
    }
}
