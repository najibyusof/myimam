<?php

/**
 * Sidebar Menu Configuration
 *
 * Centralized, role-aware menu structure for the application.
 *
 * Each menu item supports:
 * - title: Display label
 * - route: Route name or array of route names for matching
 * - icon: Icon name (simple name, rendered by Blade component)
 * - permission: Single permission or array of permissions (uses OR logic)
 * - role: Single role or array of roles (uses OR logic, checked before permission)
 * - children: Submenu items (optional)
 * - divider: Add visual separator (optional)
 *
 * Authorization logic:
 * 1. SuperAdmin always sees all items
 * 2. If 'role' is specified, user must have at least one role
 * 3. If 'permission' is specified, user must have at least one permission
 * 4. If both are specified, role is evaluated first (OR), then permission (OR)
 * 5. If neither specified, item is always visible to authenticated users
 */

return [
    [
        'key' => 'general',
        'section' => 'General',
        'collapsible' => false,
        'default_open' => true,
        'items' => [
            [
                'title' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'home',
                'permission' => null, // Visible to all authenticated users
            ],
            [
                'title' => 'Notifications',
                'route' => 'notifications.index',
                'icon' => 'bell',
                'permission' => null,
                'badge' => [
                    'type' => 'unread_notifications',
                    'tone' => 'red',
                ],
            ],
        ],
    ],

    [
        'key' => 'finance',
        'section' => 'Finance',
        'collapsible' => true,
        'default_open' => true,
        'items' => [
            [
                'title' => 'Akaun',
                'route' => 'admin.akaun.index',
                'icon' => 'wallet',
                'permission' => 'akaun.view',
            ],
            [
                'title' => 'Hasil',
                'route' => 'admin.hasil.index',
                'icon' => 'trending-up',
                'permission' => 'hasil.view',
            ],
            [
                'title' => 'Belanja',
                'route' => 'admin.belanja.index',
                'icon' => 'trending-down',
                'permission' => 'belanja.view',
                'badge' => [
                    'type' => 'pending_baucar',
                    'tone' => 'amber',
                ],
            ],
            [
                'title' => 'Tabung Khas',
                'route' => 'admin.tabung-khas.index',
                'icon' => 'credit-card',
                'permission' => 'tabung_khas.view',
            ],
            [
                'title' => 'Program Masjid',
                'route' => 'admin.program-masjid.index',
                'icon' => 'calendar',
                'permission' => 'program_masjid.view',
            ],
        ],
    ],

    [
        'key' => 'reports',
        'section' => 'Reports',
        'collapsible' => true,
        'default_open' => true,
        'items' => [
            [
                'title' => 'Laporan',
                'route' => 'admin.reporting.index',
                'icon' => 'bar-chart-2',
                'permission' => 'reports.view',
            ],
        ],
    ],

    [
        'key' => 'administration',
        'section' => 'Administration',
        'collapsible' => true,
        'default_open' => false,
        'items' => [
            [
                'title' => 'Settings',
                'route' => 'profile.edit',
                'icon' => 'settings',
                'permission' => 'settings.manage',
            ],
            [
                'title' => 'CMS Management',
                'route' => 'admin.cms.landing.edit',
                'icon' => 'file-text',
                'permission' => 'cms.manage',
            ],
            [
                'title' => 'Subscription Management',
                'route' => 'admin.subscriptions.index',
                'icon' => 'credit-card',
                'permission' => 'subscriptions.manage',
                'badge' => [
                    'type' => 'expired_subscriptions',
                    'tone' => 'red',
                ],
            ],
            [
                'title' => 'Users & Roles',
                'key'   => 'users_roles',
                'route' => null,
                'icon'  => 'users',
                'children' => [
                    [
                        'title'      => 'User Management',
                        'route'      => 'admin.users.index',
                        'icon'       => 'users',
                        'role'       => 'Admin',
                        'permission' => null,
                    ],
                    [
                        'title'      => 'Role Management',
                        'route'      => 'admin.roles.index',
                        'icon'       => 'shield',
                        'permission' => 'roles.assign',
                    ],
                ],
            ],
        ],
    ],
];
