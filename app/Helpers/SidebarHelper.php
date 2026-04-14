<?php

/**
 * Sidebar Helper Functions
 *
 * Convenient helpers for getting filtered sidebar menu system-wide.
 */

use App\Services\SidebarService;

if (!function_exists('sidebar')) {
    /**
     * Get the SidebarService instance.
     *
     * @return SidebarService
     */
    function sidebar(): SidebarService
    {
        return app(SidebarService::class);
    }
}

if (!function_exists('getSidebarMenu')) {
    /**
     * Get filtered sidebar menu sections.
     *
     * @return array Array of sections with authorized items
     */
    function getSidebarMenu(): array
    {
        return sidebar()->getFilteredMenu();
    }
}

if (!function_exists('getSidebarMenuFlat')) {
    /**
     * Get sidebar menu items as flat array.
     *
     * @return array Flat array of authorized menu items
     */
    function getSidebarMenuFlat(): array
    {
        return sidebar()->getMenuFlat();
    }
}

if (!function_exists('canAccessRoute')) {
    /**
     * Check if current user can access a specific route.
     *
     * @param string $route
     * @return bool
     */
    function canAccessRoute(string $route): bool
    {
        return sidebar()->hasAccessToRoute($route);
    }
}
