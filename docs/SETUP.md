# Setup

## Requirements

- PHP 8.2+
- Composer 2+
- Node.js 18+
- MySQL 8+

## Install

1. Copy environment file:
    - `cp .env.example .env`
2. Update DB credentials in `.env`.
3. Generate app key:
    - `php artisan key:generate`
4. Run migrations:
    - `php artisan migrate`
5. Install frontend packages:
    - `npm install`
6. Start dev servers:
    - `php artisan serve`
    - `npm run dev`

## Auth Starter

- Laravel Breeze (Blade) is installed.
- Tailwind CSS is configured via Vite.
