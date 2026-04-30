# Subscription Process Step-by-Step

This document explains the end-to-end subscription process in the current multi-tenant system implementation.

## 1. Goal and Scope

The subscription module ensures each tenant (masjid) has a valid subscription before accessing protected modules.

Features covered:

- Plan selection
- Trial activation (7 days)
- Payment creation and gateway redirect
- Gateway callback handling
- Subscription activation/failure handling
- Invoice PDF generation
- Auto-renew process
- WhatsApp expiry reminders

## 2. Main Routes

Tenant-facing routes:

- `GET /subscription` -> `subscription.index`
- `POST /subscription/{plan_id}/subscribe` -> `subscription.subscribe`
- `POST /subscription/{plan_id}/trial` -> `subscription.trial`
- `GET /subscription/status/{payment}` -> `subscription.status`
- `GET /subscription/invoice/{payment}/download` -> `subscription.invoice.download`

Gateway callback route:

- `POST /payment/callback` -> `payment.callback`

## 3. Core Data Tables

### plans

- `id`
- `name`
- `price`
- `duration_days`
- `features` (json)

### subscriptions

- `id`
- `tenant_id`
- `plan_id`
- `status` (`active`, `pending`, `expired`)
- `is_trial` (bool)
- `start_date`
- `end_date`
- `trial_ends_at`
- `auto_renew` (bool)
- `reminder_sent_at`
- `renewal_of_id` (self-reference)

### payments

- `id`
- `tenant_id`
- `subscription_id`
- `amount`
- `status` (`pending`, `paid`, `failed`)
- `gateway` (`billplz`, `toyyibpay`)
- `reference_id` (gateway bill code/reference)
- `invoice_no`
- `invoice_path`
- `payload` (json)

### masjid

- `subscription_status`
- `subscription_expiry`
- `whatsapp_no`

## 4. Step-by-Step: Paid Subscription Flow

### Step 1: Open subscription page

1. Tenant admin opens `GET /subscription`.
2. System loads:

- available plans
- current subscription
- recent payments

Important:

- Available plans are read from the `plans` table only.
- If `plans` is empty and you still use legacy `subscription_plans`, run:
    - `php artisan subscriptions:migrate-plans --dry-run`
    - `php artisan subscriptions:migrate-plans`

Code path:

- `SubscriptionController@index`

### Step 2: Submit subscribe request

1. Tenant chooses:

- plan
- gateway (`toyyibpay` or `billplz`)
- auto renew option

2. Form submits to `POST /subscription/{plan_id}/subscribe`.

Code path:

- `SubscriptionController@subscribe`
- `PaymentService::createPayment(...)`

### Step 3: Create pending records

Inside `PaymentService::createPayment(...)`:

1. Existing active/pending subscriptions for tenant are marked `expired`.
2. New `subscriptions` record is created with `status = pending`.
3. New `payments` record is created with `status = pending`.

### Step 4: Create bill at payment gateway

1. Gateway manager resolves driver (`billplz` or `toyyibpay`).
2. `createBill(...)` is called.
3. Gateway returns:

- `payment_url`
- `reference_id` (bill code/reference)

4. `payments.reference_id` and `payments.payload` are stored.

If bill creation fails:

- payment -> `failed`
- subscription -> `expired`
- user redirected back with error.

### Step 5: Redirect user to gateway

If successful:

- user is redirected to gateway payment page (`payment_url`).

## 5. Step-by-Step: Callback and Activation

### Step 6: Gateway callback hits system

Gateway calls:

- `POST /payment/callback`

Code path:

- `SubscriptionController@paymentCallback`
- `PaymentService::handleCallback(...)`

### Step 7: Validate and map callback

`PaymentService::handleCallback(...)`:

1. Detects gateway from payload.
2. Verifies callback signature/token using gateway driver.
3. Parses callback status + reference.
4. Finds payment by:

- `payment_id` (if present), else
- `gateway + reference_id`.

### Step 8: Update payment and subscription

If callback status is paid:

1. `payments.status = paid`
2. `subscriptions.status = active`
3. `subscriptions.start_date = now`
4. `subscriptions.end_date = now + plan.duration_days`
5. Tenant snapshot updated:

- `masjid.subscription_status = active`
- `masjid.subscription_expiry = end_date`

If callback status is failed:

- `payments.status = failed`
- `subscriptions.status = expired`

### Step 9: Generate invoice PDF

On paid callback:

- `InvoiceService::generateForPayment(...)` generates invoice PDF.
- Stores file in `storage/app/public/invoices/...`
- Saves `invoice_no` and `invoice_path` in `payments`.

## 6. Step-by-Step: Trial Flow (7 Days)

### Step 10: Start trial

1. Tenant clicks "Mulakan Trial 7 Hari".
2. Request goes to `POST /subscription/{plan_id}/trial`.

Code path:

- `SubscriptionController@startTrial`
- `PaymentService::startTrial(...)`

Trial rules:

- Allowed only if tenant has no previous subscription.
- Creates active subscription immediately:
- `is_trial = true`
- `auto_renew = false`
- `start_date = now`
- `end_date = now + 7 days`
- `trial_ends_at = end_date`
- Updates tenant snapshot in `masjid`.

## 7. Step-by-Step: Invoice Download Flow

### Step 11: Download invoice

1. Tenant opens `GET /subscription/invoice/{payment}/download`.
2. Authorization checks tenant ownership (or superadmin).
3. If invoice file missing, system generates it on demand.
4. PDF file is streamed as download.

Code path:

- `SubscriptionController@downloadInvoice`
- `InvoiceService::generateForPayment(...)`

## 8. Step-by-Step: Auto-Renew and Reminder Jobs

### Step 12: Scheduled command

Scheduled in `routes/console.php`:

- `subscriptions:process-lifecycle --days=3` daily at `08:00`

Command class:

- `ProcessSubscriptionLifecycleCommand`

Service:

- `SubscriptionLifecycleService`

### Step 13: Auto-renew processing

`processAutoRenewals()` does:

1. Find active subscriptions with:

- `auto_renew = true`
- `end_date <= now`

2. Skip if pending renewal already exists.
3. Creates new pending renewal payment via `PaymentService::createPayment(...)`.
4. Sets `renewal_of_id` to link renewal chain.

### Step 14: WhatsApp reminder processing

`sendExpiryReminders($daysBefore)` does:

1. Find active subscriptions expiring within next N days.
2. Skip records with `reminder_sent_at` already set.
3. Resolve recipient phone:

- `masjid.whatsapp_no`, else
- `services.whatsapp.fallback_to`

4. Send message via `WhatsAppService`.
5. On success, set `reminder_sent_at = now`.

## 9. Access Control Behavior

Protected app modules use tenant subscription middleware (`tenant.subscription`).
If tenant has no valid subscription, middleware redirects tenant to `/subscription` for renewal.

## 10. Required Configuration

Payment:

- `PAYMENT_GATEWAY`
- `BILLPLZ_*` or `TOYYIBPAY_*`

ToyyibPay hardening:

- `TOYYIBPAY_CALLBACK_TOKEN`
- `TOYYIBPAY_VERIFY_SSL` (set `true` for production)

ToyyibPay SSL troubleshooting (local/sandbox):

- If local SSL chain validation fails (cURL error 60), temporarily set:
    - `TOYYIBPAY_VERIFY_SSL=false`
- Apply config refresh after change:
    - `php artisan config:clear`

WhatsApp reminder:

- `WHATSAPP_PHONE_NUMBER_ID`
- `WHATSAPP_ACCESS_TOKEN`
- `WHATSAPP_FALLBACK_TO` (optional)

## 11. Operational Checklist

1. Run migrations:

- `php artisan migrate`

2. If legacy plans exist in `subscription_plans`, migrate them into `plans`:

- Preview only: `php artisan subscriptions:migrate-plans --dry-run`
- Execute: `php artisan subscriptions:migrate-plans`

3. Ensure public storage symlink:

- `php artisan storage:link`

4. Ensure scheduler is running in production.

5. Verify route availability:

- `php artisan route:list | Select-String -Pattern "subscription|payment/callback|invoice|trial"`

6. Test end-to-end:

- Create plan
- Start trial
- Paid subscription with ToyyibPay/Billplz
- Callback success
- Download invoice
- Expiry reminder and auto-renew command

## 12. Reference Classes

- `App\Http\Controllers\SubscriptionController`
- `App\Services\PaymentService`
- `App\Services\InvoiceService`
- `App\Services\SubscriptionLifecycleService`
- `App\Services\WhatsAppService`
- `App\Services\Payments\PaymentGatewayManager`
- `App\Console\Commands\MigrateLegacySubscriptionPlansCommand`
