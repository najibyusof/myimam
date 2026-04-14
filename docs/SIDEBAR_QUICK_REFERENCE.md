# Sidebar System - Quick Reference

## What Was Created

### Configuration Files

- **config/sidebar.php** - Centralized menu configuration with sections

### Service Classes

- **app/Services/SidebarService.php** - Filters menu based on permissions/roles

### Helper Functions

- **app/Helpers/SidebarHelper.php** - `getSidebarMenu()`, `sidebar()`, etc.
- **app/Helpers/IconHelper.php** - `getMenuIcon()` for SVG icons

### Views

- **resources/views/components/sidebar.blade.php** (refactored) - Clean, dynamic sidebar

### Documentation

- **docs/SIDEBAR_SYSTEM.md** - Complete guide

## Quick Start

### 1. Run Composer Update

```bash
composer dump-autoload
```

### 2. Add Menu Item to config/sidebar.php

```php
[
    'title' => 'New Feature',
    'route' => 'admin.feature.index',
    'icon' => 'home',
    'permission' => 'feature.view',
]
```

### 3. Use in Blade

```blade
@php
    $menus = getSidebarMenu(); // Get filtered menu
@endphp

@foreach($menus as $section)
    <p>{{ $section['section'] }}</p>
    @foreach($section['items'] as $item)
        <a href="{{ route($item['route']) }}">
            {{ $item['title'] }}
        </a>
    @endforeach
@endforeach
```

## API Overview

### Functions (in Blade)

```php
getSidebarMenu()         // Get filtered menu sections
getSidebarMenuFlat()     // Get flat array of items
canAccessRoute('route')  // Check if user can access route
sidebar()                // Get SidebarService instance
getMenuIcon('icon-name') // Get SVG icon by name
```

### SidebarService Methods

```php
$service = sidebar();

$service->getFilteredMenu()         // Array of authorized sections
$service->getMenuFlat()             // Flat array of authorized items
$service->hasAccessToRoute('route') // Boolean check
```

## Authorization

Menu items show based on:

1. **SuperAdmin** - Always sees everything
2. **Role** - User must have specified role(s)
3. **Permission** - User must have specified permission(s)
4. **Route Check** - Route must exist in application
5. **Default** - If no role/permission, visible to all authenticated users

## Icon Names

```
home
bell
wallet
trending-up
trending-down
file-text
credit-card
calendar
bar-chart-2
settings
users
shield
```

## Common Tasks

### Add New Menu Item

Edit `config/sidebar.php` and add to appropriate section

### Add New Icon

Edit `app/Helpers/IconHelper.php` and add SVG markup

### Check User Access

```php
if (canAccessRoute('admin.users.index')) {
    // User can access
}
```

### Get All Menus for User

```php
$menus = getSidebarMenu();
```

### Debug Permissions

```php
$user = auth()->user();
dd($user->getAllPermissions());
dd($user->getRoleNames());
```

## Service Provider

SidebarService is registered as singleton in AppServiceProvider:

```php
$this->app->singleton(SidebarService::class, function () {
    return new SidebarService();
});
```

## Auto-loaded Helpers

The following helpers are auto-loaded via composer.json:

- app/Helpers/SidebarHelper.php
- app/Helpers/IconHelper.php

No manual requires needed.

## Blade Component Usage

The sidebar is automatically included in the app layout:

```blade
<!-- resources/views/components/layout.blade.php -->
<x-sidebar />
```

This component:

- Loads filtered menu using getSidebarMenu()
- Loops through sections and items
- Renders menu with icons
- Highlights active route with request()->routeIs()

## Testing

```bash
# Check if service loads
php artisan tinker
>>> sidebar()
>>> getSidebarMenu()
>>> canAccessRoute('dashboard')

# Test permissions
>>> auth()->user()->can('akaun.view')
>>> auth()->user()->hasRole('Admin')
```

## Performance Tips

1. Use single permission instead of multiple for simpler logic
2. Cache sidebar menu in production if needed:
    ```php
    cache()->remember('sidebar_' . auth()->id(), 3600, fn() => getSidebarMenu())
    ```
3. Avoid complex queries in config/sidebar.php (it's just config)

## Troubleshooting

| Issue                 | Solution                                         |
| --------------------- | ------------------------------------------------ |
| Menu item not showing | Check permission with `php artisan tinker`       |
| Icon not displaying   | Verify icon name in config/sidebar.php           |
| Route not working     | Check route exists with `php artisan route:list` |
| Helpers not loading   | Run `composer dump-autoload`                     |
| Active state wrong    | Check route name matches config exactly          |

## Files Modified

1. **config/sidebar.php** (created)
2. **app/Services/SidebarService.php** (created)
3. **app/Helpers/SidebarHelper.php** (created)
4. **app/Helpers/IconHelper.php** (created)
5. **resources/views/components/sidebar.blade.php** (refactored)
6. **app/Providers/AppServiceProvider.php** (updated)
7. **composer.json** (updated)
8. **docs/SIDEBAR_SYSTEM.md** (created)

## Next Steps

1. Run: `composer dump-autoload`
2. Test sidebar loads: Visit any protected page
3. Test permissions: Try different user roles
4. Add custom menu items as needed
