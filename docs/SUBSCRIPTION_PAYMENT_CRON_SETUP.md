# Subscription and Payment Gateway Cron Setup Guide

This document explains how to set up and verify scheduler/cron jobs for subscription and payment-related automation.

## 1. What this cron setup handles

The Laravel scheduler in this project runs these jobs:

1. `subscriptions:sync-status` (hourly)
   - Sync expired subscriptions.
   - Update tenant subscription snapshot fields.

2. `subscriptions:process-lifecycle --days=3` (daily at 08:00)
   - Process auto-renewal logic.
   - Send WhatsApp reminders before expiry.

Source of schedule definitions:
- `routes/console.php`

## 2. Prerequisites

1. App is deployed and running from project root.
2. Environment variables are configured (`.env`), especially:
   - Payment:
     - `PAYMENT_GATEWAY`
     - `TOYYIBPAY_BASE_URL`
     - `TOYYIBPAY_SECRET_KEY`
     - `TOYYIBPAY_CATEGORY_CODE`
     - `TOYYIBPAY_CALLBACK_TOKEN`
     - `BILLPLZ_BASE_URL`
     - `BILLPLZ_API_KEY`
     - `BILLPLZ_COLLECTION_ID`
   - WhatsApp reminder:
     - `WHATSAPP_PHONE_NUMBER_ID`
     - `WHATSAPP_ACCESS_TOKEN`
     - `WHATSAPP_FALLBACK_TO` (optional)
3. Configuration cache is refreshed after `.env` changes:

```powershell
php artisan config:clear
```

## 3. Confirm the scheduler jobs exist

Run:

```powershell
php artisan schedule:list
```

Expected entries include:
- `subscriptions:sync-status`
- `subscriptions:process-lifecycle --days=3`

## 4. Server cron setup (Linux production)

Laravel requires the scheduler runner every minute.

1. Open crontab:

```bash
crontab -e
```

2. Add this line (adjust path and PHP binary if needed):

```bash
* * * * * cd /path/to/myimam && php artisan schedule:run >> /dev/null 2>&1
```

3. Save and verify:

```bash
crontab -l
```

## 5. Task Scheduler setup (Windows server)

If deployed on Windows server, create a scheduled task:

1. Open Task Scheduler.
2. Create Task -> General:
   - Name: `myimam-laravel-scheduler`
   - Run whether user is logged on or not.
3. Triggers:
   - New trigger -> Daily -> Repeat task every `1 minute` indefinitely.
4. Actions:
   - Program/script: path to `php.exe`
   - Add arguments: `artisan schedule:run`
   - Start in: project folder (example: `D:\laravel\myimam`)
5. Save and run once manually to confirm no errors.

## 6. Manual dry-run checks (recommended)

Run each job manually to validate business logic and credentials:

```powershell
php artisan subscriptions:sync-status
php artisan subscriptions:process-lifecycle --days=3
```

Look for output like:
- `Subscription sync completed.`
- `Subscription lifecycle completed.`

## 7. Payment callback requirement (non-cron but critical)

Payment callback route used by gateway:
- `POST /payment/callback`
- Route name: `payment.callback`
- Defined in `routes/web.php`.

Important:
1. Callback URL must be publicly reachable by payment gateway.
2. Localhost callbacks usually fail from external gateways.
3. For local testing, use a tunnel (for example ngrok) and update gateway callback URL.

## 8. Operational verification checklist

After cron setup:

1. `php artisan schedule:list` shows both subscription jobs.
2. Cron/task runs every minute without permission errors.
3. Subscription lifecycle command updates renew/reminder records.
4. Payment callback endpoint is reachable from gateway side.
5. Check logs if needed:

```powershell
Get-Content storage\logs\laravel.log -Tail 200
```

## 9. Common issues and fixes

1. Jobs not running:
   - Cause: cron entry missing/wrong path.
   - Fix: correct `cd` path and PHP executable.

2. Env changes not applied:
   - Cause: cached config.
   - Fix:

```powershell
php artisan config:clear
```

3. Auto-renew/reminder does not execute:
   - Cause: scheduler never triggered.
   - Fix: ensure `schedule:run` is called every minute by OS scheduler.

4. Gateway callback never updates payment status:
   - Cause: callback URL inaccessible publicly.
   - Fix: use public URL and verify gateway callback configuration/token settings.

## 10. Useful commands summary

```powershell
php artisan schedule:list
php artisan schedule:run
php artisan subscriptions:sync-status
php artisan subscriptions:process-lifecycle --days=3
php artisan config:clear
```
