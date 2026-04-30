# IMAM - Mosque Financial Management System

IMAM is a Laravel-based financial management system focused on mosque administration workflows.
It includes modular features for account management, income and expenses, transfers, vouchers,
notifications, role-based access control, and reporting support.

## Tech Stack

- PHP / Laravel
- MySQL (or compatible relational database)
- Vite + Node.js frontend tooling
- PHPUnit for automated tests

## Project Structure

- `app/Modules/` - domain modules and feature boundaries
- `app/Models/` - core Eloquent models
- `routes/` - web, api, auth, and console routes
- `database/migrations/` and `database/seeders/` - schema and seed data
- `docs/` - architecture, auth, RBAC, notification, and setup guides

## Local Setup

1. Install dependencies:

```bash
composer install
npm install
```

2. Copy environment file and generate key:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure database credentials in `.env`.

4. Run migrations and seeders:

```bash
php artisan migrate --seed
```

5. Start the app:

```bash
php artisan serve
npm run dev
```

## Common Commands

```bash
php artisan optimize:clear
php artisan test
php artisan route:list
npm run build
```

## API and Integration References

- `API_DOCUMENTATION.md`
- `API_QUICK_REFERENCE.md`
- `POSTMAN_COLLECTION.json`
- `SETUP_AND_INTEGRATION_GUIDE.md`

## Internal Documentation

- `docs/ARCHITECTURE.md`
- `docs/SETUP.md`
- `docs/RBAC.md`
- `docs/NOTIFICATION_SYSTEM.md`
- `docs/IMPLEMENTATION_REPORT.md`
- `docs/SUBSCRIPTION_PAYMENT_CRON_SETUP.md`

## Testing

Run the full test suite:

```bash
php artisan test
```

Run only feature tests:

```bash
php artisan test --testsuite=Feature
```

Run only unit tests:

```bash
php artisan test --testsuite=Unit
```

## Notes

- Keep module routes registered in `routes/web.php`.
- Ensure module service providers are registered in `bootstrap/providers.php`.
- If Blade output looks stale, run `php artisan optimize:clear`.

## Deployment

### Prerequisites

- PHP and required extensions installed on the server
- Database created and reachable from the app server
- Queue worker process manager configured (for example Supervisor)
- Web server configured to point to `public/`

### Release Steps

1. Pull the latest code on the server.
2. Install PHP dependencies without development packages:

    composer install --no-dev --optimize-autoloader

3. Install frontend dependencies and build assets:

    npm ci
    npm run build

4. Run database migrations:

    php artisan migrate --force

5. Cache framework metadata:

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

6. Restart queue workers:

    php artisan queue:restart

### Scheduler and Queues

- See `docs/SUBSCRIPTION_PAYMENT_CRON_SETUP.md` for full subscription/payment gateway cron setup and verification steps.

- Add scheduler cron entry (runs every minute):

    ```bash
    * * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
    ```

- Keep at least one queue worker running for asynchronous jobs.

### Windows (IIS and Task Scheduler)

- Configure IIS site root to the `public/` folder.
- Ensure PHP is available to IIS and command-line sessions.
- Create a scheduled task that runs every minute with this action:

    Program/script: `cmd.exe`
    Arguments: `/c cd /d D:\path\to\project && php artisan schedule:run >> NUL 2>&1`

- Keep a queue worker alive using a service wrapper (for example NSSM) with:

    `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`

- Restart queue workers after each deployment:

    `php artisan queue:restart`

### IIS Rewrite and Permissions Checklist

- Enable the IIS URL Rewrite module.
- In IIS, ensure requests are routed through `public/index.php`.
- Confirm `storage/` and `bootstrap/cache/` are writable by the IIS application pool identity.
- Keep `APP_ENV`, `APP_DEBUG`, `APP_URL`, database, mail, queue, and cache values correct in `.env`.
- Confirm PHP limits are suitable for production (memory_limit, max_execution_time, upload_max_filesize, post_max_size).
- Validate HTTPS redirection and trusted proxy settings if the app is behind a load balancer.
- After any config or env update, run:

    `php artisan optimize:clear`
    `php artisan config:cache`

### Rollback Basics

- Keep database backups before migration-heavy releases.
- If rollback is needed, deploy the previous release and run rollback migration carefully:

    php artisan migrate:rollback --step=1

## License

This project is proprietary unless otherwise stated by repository owners.
