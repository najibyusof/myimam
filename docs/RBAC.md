# RBAC Structure (Spatie)

## Package

- `spatie/laravel-permission`

## Core Tables

- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

## Example Roles

- `Admin`
- `Manager`
- `User`

## Example Permissions

- `dashboard.view`
- `users.view`, `users.create`, `users.update`, `users.delete`
- `roles.assign`
- `masjid.view`, `masjid.create`, `masjid.update`, `masjid.delete`
- `finance.view`, `finance.create`, `finance.update`, `finance.approve`
- `reports.view`, `reports.export`
- `settings.manage`
- `audit.view`

## Seeder

- `Database\\Seeders\\RolesAndPermissionsSeeder`
- Linked from `DatabaseSeeder`

## Middleware Examples

Routes use middleware aliases:

- `role`
- `permission`
- `role_or_permission`

Example from routes:

- `role:Admin|Manager` + `permission:masjid.view`
- `role:Admin` + `permission:masjid.update`

## Policy Example

- `App\\Policies\\MasjidPolicy`
- Registered in `AppServiceProvider` using `Gate::policy(Masjid::class, MasjidPolicy::class)`

Policy checks:

- `viewAny`, `view`, `create`, `update`, `delete`

## Controller Usage Example

- `App\\Http\\Controllers\\Admin\\MasjidAdminController`
- Uses `$this->authorize(...)`:
    - `authorize('viewAny', Masjid::class)`
    - `authorize('update', $masjid)`

## Run

1. Migrate DB:
    - `php artisan migrate`
2. Seed RBAC data:
    - `php artisan db:seed --class=RolesAndPermissionsSeeder`
