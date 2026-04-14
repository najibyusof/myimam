# Dynamic Sidebar Navigation System

## Overview

The sidebar navigation system is fully dynamic, role-based, and permission-aware. It uses **spatie/laravel-permission** to control menu visibility based on user roles and permissions.

## Architecture

### Components

1. **config/sidebar.php** - Centralized menu configuration
2. **SidebarService** - Filters menu based on user permissions/roles
3. **Helper Functions** - Easy access to sidebar data from Blade
4. **Icon Helper** - Maps icon names to SVG markup
5. **Blade Component** - Clean, semantic sidebar rendering

### Data Flow

```
config/sidebar.php (menu definition)
    ↓
SidebarService (loads & filters)
    ↓
Helper Functions (getSidebarMenu())
    ↓
Blade Component (renders)
```

## Configuration

### Menu Structure

Edit `config/sidebar.php` to define your menu:

```php
return [
    [
        'section' => 'General',
        'items' => [
            [
                'title' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'home',
                'permission' => null, // Visible to all authenticated users
            ],
        ],
    ],

    [
        'section' => 'Finance',
        'items' => [
            [
                'title' => 'Akaun',
                'route' => 'admin.akaun.index',
                'icon' => 'wallet',
                'permission' => 'akaun.view',
            ],
        ],
    ],
];
```

### Menu Item Properties

| Property     | Type                | Description                                       |
| ------------ | ------------------- | ------------------------------------------------- |
| `section`    | string              | Section label (e.g., "Finance", "Administration") |
| `title`      | string              | Display text in menu                              |
| `route`      | string              | Laravel route name                                |
| `icon`       | string              | Icon identifier (see Icon Helper)                 |
| `permission` | string\|array\|null | Permission name(s) required to see item           |
| `role`       | string\|array\|null | Role name(s) required to see item                 |

## Authorization Logic

The SidebarService uses this authorization hierarchy:

1. **SuperAdmin Bypass** - Users with `superadmin` role always see all items
2. **Role Check** - If `role` is specified, user must have at least one role (OR logic)
3. **Permission Check** - If `permission` is specified, user must have at least one permission (OR logic)
4. **Fallback** - If neither role nor permission specified, item visible to all authenticated users

### Examples

**Everyone sees this:**

```php
[
    'title' => 'Dashboard',
    'route' => 'dashboard',
    'icon' => 'home',
    'permission' => null,
]
```

**Only users with 'akaun.view' permission:**

```php
[
    'title' => 'Akaun',
    'route' => 'admin.akaun.index',
    'icon' => 'wallet',
    'permission' => 'akaun.view',
]
```

**Only Admin or Manager roles:**

```php
[
    'title' => 'User Management',
    'route' => 'admin.users.index',
    'icon' => 'users',
    'role' => ['Admin', 'Manager'],
]
```

**Admin role OR manage.users permission:**

```php
[
    'title' => 'Users',
    'route' => 'admin.users.index',
    'icon' => 'users',
    'role' => 'Admin',
    'permission' => 'manage.users',
]
```

## Using the Sidebar

### In Blade Templates

The sidebar component is automatically rendered in the app layout:

```php
<!-- resources/views/components/layout.blade.php -->
<x-sidebar />
```

### Helper Functions

#### Get Filtered Menu

```php
$menus = getSidebarMenu(); // Returns array of sections with authorized items

// Example output:
[
    [
        'section' => 'General',
        'items' => [
            [
                'title' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'home',
                'isActive' => true,
            ],
        ],
    ],
    // ... more sections
]
```

#### Get Flat Menu

```php
$items = getSidebarMenuFlat(); // Returns flat array of all authorized items
```

#### Check Route Access

```php
if (canAccessRoute('admin.users.index')) {
    // User can access this route
}
```

#### Get Service Instance

```php
$sidebar = sidebar(); // Returns SidebarService instance
$menus = $sidebar->getFilteredMenu();
```

## Icon Helper

### Supported Icons

| Icon Name       | Usage             |
| --------------- | ----------------- |
| `home`          | Dashboard         |
| `bell`          | Notifications     |
| `wallet`        | Akaun             |
| `trending-up`   | Hasil (Income)    |
| `trending-down` | Belanja (Expense) |
| `file-text`     | Baucar Bayaran    |
| `credit-card`   | Tabung Khas       |
| `calendar`      | Program Masjid    |
| `bar-chart-2`   | Reports           |
| `settings`      | Settings          |
| `users`         | User Management   |
| `shield`        | Role Management   |

### Adding New Icons

Edit `app/Helpers/IconHelper.php`:

```php
if (!function_exists('getMenuIcon')) {
    function getMenuIcon(string $iconName): string
    {
        $icons = [
            'my-icon' => '<svg>...</svg>', // Add here
            // ... existing icons
        ];

        return $icons[$iconName] ?? '<svg>...</svg>'; // Fallback
    }
}
```

## SidebarService API

### Public Methods

```php
// Get filtered sections with authorized items
$menus = $service->getFilteredMenu();

// Get flat array of authorized items
$items = $service->getMenuFlat();

// Check if user can access a specific route
$canAccess = $service->hasAccessToRoute('admin.users.index');
```

## Multi-Tenant Safety

The sidebar system respects multi-tenant architecture:

- **No hardcoded routes** - All routes checked for existence via `Route::has()`
- **Permission isolation** - Each user only sees routes they're authorized for
- **Scope enforcement** - Combine with model scopes (`.byMasjid()`) for full isolation

## Best Practices

### 1. Centralize Configuration

Always update `config/sidebar.php` instead of hardcoding menu items in Blade.

```php
// ✅ Good
// config/sidebar.php
[
    'title' => 'Reports',
    'route' => 'admin.reporting.index',
    'icon' => 'bar-chart-2',
    'permission' => 'reports.view',
]

// ❌ Bad
// In Blade component
@can('reports.view')
    <a href="{{ route('admin.reporting.index') }}">Reports</a>
@endcan
```

### 2. Use Consistent Permission Names

Follow naming convention: `resource.action`

```php
'permission' => 'akaun.view'      // Good
'permission' => 'canViewAkaun'    // Avoid
```

### 3. Check Route Existence

The SidebarService automatically verifies routes exist. If a route doesn't exist in `routes/web.php`, the menu item won't show.

### 4. SuperAdmin Bypass

SuperAdmin users see everything. Use this for admin-level operations.

```php
// In SidebarService
if ($this->user->hasRole('superadmin') || $this->user->peranan === 'superadmin') {
    return true; // Show all items
}
```

### 5. Cache for Production

For performance, consider caching the filtered menu:

```php
$menus = cache()->remember('sidebar_' . auth()->id(), 3600, function () {
    return getSidebarMenu();
});
```

## Troubleshooting

### Menu Items Not Showing

1. **Check permission** - User must have the specified permission

```bash
php artisan tinker
>>> auth()->user()->can('akaun.view')
```

2. **Check role** - User must have the specified role

```bash
php artisan tinker
>>> auth()->user()->hasRole('Admin')
```

3. **Check route exists** - Route must be registered in `routes/web.php`

```bash
php artisan route:list | grep admin.akaun.index
```

### Wrong Active State

The active state is determined by `request()->routeIs($route . '*')`. Ensure your route names match:

```php
// For routes: admin.akaun.index, admin.akaun.show, admin.akaun.edit
// Menu route should be: admin.akaun.index
// This will highlight on all admin.akaun.* routes
```

## Adding New Menu Items

### Step 1: Define Permission (Optional)

In your seeder or migration:

```php
Permission::create(['name' => 'my-feature.view']);
```

### Step 2: Add to config/sidebar.php

```php
[
    'section' => 'Finance',
    'items' => [
        // ... existing items
        [
            'title' => 'My New Feature',
            'route' => 'admin.my-feature.index',
            'icon' => 'my-icon',
            'permission' => 'my-feature.view',
        ],
    ],
]
```

### Step 3: Add Icon (If New)

In `app/Helpers/IconHelper.php`:

```php
'my-icon' => '<svg>...</svg>',
```

### Step 4: Clear Cache (If Using)

```bash
php artisan config:cache
```

## Testing

### Test Menu Filtering

```php
// Test that user with specific role sees menu item
$user = User::factory()->create();
$user->assignRole('Admin');

$menus = sidebar()->getFilteredMenu();
$this->assertTrue(collect($menus)
    ->pluck('items')
    ->flatten(1)
    ->where('title', 'User Management')
    ->count() > 0);
```

### Test Permission Check

```php
$user = User::factory()->create();
$user->givePermissionTo('akaun.view');

$this->assertTrue(sidebar()->hasAccessToRoute('admin.akaun.index'));
```

## Migration from Old System

If upgrading from the previous hardcoded sidebar:

1. Map all `@can()` checks to permission names
2. Create `config/sidebar.php` with menu structure
3. Update `resources/views/components/sidebar.blade.php`
4. Test all user roles see correct menus
5. Run `composer dump-autoload` to include helper files

## Further Reading

- Laravel Permissions: https://spatie.be/docs/laravel-permission/
- Blade Components: https://laravel.com/docs/11.x/blade#components
- Service Providers: https://laravel.com/docs/11.x/providers
