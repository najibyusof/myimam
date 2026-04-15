<?php

return [
    [
        'group_key' => 'menu.group_general',
        'title_key' => 'menu.dashboard',
        'route' => 'dashboard',
        'icon' => 'home',
        'permission' => ['view dashboard', 'dashboard.view'],
        'tenant_scoped' => false,
    ],
    [
        'group_key' => 'menu.group_finance',
        'title_key' => 'menu.akaun',
        'route' => ['akaun.index', 'admin.akaun.index'],
        'icon' => 'wallet',
        'permission' => ['manage akaun', 'akaun.view'],
    ],
    [
        'group_key' => 'menu.group_finance',
        'title_key' => 'menu.hasil',
        'route' => ['hasil.index', 'admin.hasil.index'],
        'icon' => 'arrow-down',
        'permission' => ['manage hasil', 'hasil.view'],
    ],
    [
        'group_key' => 'menu.group_finance',
        'title_key' => 'menu.belanja',
        'route' => ['belanja.index', 'admin.belanja.index'],
        'icon' => 'arrow-up',
        'permission' => ['manage belanja', 'belanja.view'],
    ],
    [
        'group_key' => 'menu.group_finance',
        'title_key' => 'menu.baucar',
        'route' => ['baucar.index'],
        'icon' => 'file',
        'permission' => ['approve baucar'],
    ],
    [
        'group_key' => 'menu.group_finance',
        'title_key' => 'menu.laporan',
        'route' => ['report.index', 'admin.reporting.index'],
        'icon' => 'chart',
        'permission' => ['view report', 'reports.view'],
    ],
    [
        'group_key' => 'menu.group_administration',
        'title_key' => 'menu.pengguna',
        'route' => ['user.index', 'admin.users.index'],
        'icon' => 'users',
        'permission' => ['manage users', 'users.view'],
    ],
    [
        'group_key' => 'menu.group_administration',
        'title_key' => 'menu.roles',
        'route' => ['admin.roles.index'],
        'icon' => 'shield',
        'permission' => ['roles.assign', 'roles.view'],
    ],
    [
        'group_key' => 'menu.group_administration',
        'title_key' => 'menu.langganan',
        'route' => ['admin.subscriptions.index'],
        'icon' => 'credit-card',
        'permission' => ['subscriptions.manage'],
        'tenant_scoped' => false,
    ],
];
