# Seeded Test Scenarios

This project now includes realistic, repeatable seed data that supports end-to-end testing across user management, finance, masjid operations, and notifications.

## Roles Covered

- `Admin`
- `Manager`
- `FinanceOfficer`
- `Auditor`
- `MasjidOfficer`
- `User`

Each role has multiple users where practical, plus dedicated edge-case users.

## Fixed Users For Test Setup

- `admin@example.com` / `password`
- `ops.admin@example.com` / `password`
- `manager@example.com` / `password`
- `program.manager@example.com` / `password`
- `procurement.officer@finance.gov.my` / `password`
- `pegawai.akaun@finance.gov.my` / `password`
- `bank.officer@finance.gov.my` / `password`
- `audit.internal@example.com` / `password`
- `imam.hidayah@example.com` / `password`
- `committee.one@example.com` / `password`

## Workflow Scenarios Seeded

### 1. Approved Finance Flow

- Weekly `hasil` records exist for multiple masjids.
- An approved `baucar_bayaran` exists with linked approved `belanja` entries.
- A manager/admin user has approved the voucher.

Suggested checks:

- Dashboard shows non-zero collections and approved expenses.
- Finance reports include approved voucher totals.
- Activity log shows approval actions.

### 2. Draft Approval Queue

- A draft voucher exists for each masjid.
- A draft expense is linked to that voucher.
- Notification logs include a pending draft-voucher reminder.

Suggested checks:

- Dashboard queue shows pending approvals.
- Draft filters return results.
- Managers can review but standard users cannot approve.

### 3. User Management And RBAC

- Multiple users exist per role.
- One user has no assigned role.
- One finance user is inactive.
- Two admins exist to test admin-only management safely.

Suggested checks:

- Admin can create/edit/delete users.
- Non-admin cannot access `/admin/users`.
- User list shows active/inactive edge cases.
- Role assignment flow can fix the no-role user.

### 4. Profile And Verification Edge Cases

- Some users are verified.
- Some users are unverified.
- Some users are active but unverified.

Suggested checks:

- Profile page updates work for verified and unverified users.
- Verification-dependent flows can be tested without fabricating data.

### 5. Notification Delivery Edge Cases

- Notification preferences exist for seeded users.
- Notification logs include `sent`, `pending`, and `failed` statuses.
- Finance-linked users have Telegram-style preferences enabled.

Suggested checks:

- Dashboard alerts show failed deliveries.
- Retry jobs can be tested against failed notification logs.
- Preference screens have mixed enabled/disabled channels.

### 6. Deleted Record Edge Case

- A logically deleted `belanja` record exists.

Suggested checks:

- Default expense listings exclude deleted items.
- Audit or edge-case filters can still surface deleted records when needed.

### 7. Inter-Account Transfer Scenario

- `pindahan_akaun` records exist moving funds from operating to programme accounts.

Suggested checks:

- Transfers appear in summaries or reports.
- Cashflow dashboards reflect account movements.

## Recommended Manual Test Pass

1. Run `php artisan migrate:fresh --seed`
2. Login as `admin@example.com`
3. Verify `/dashboard` shows live metrics
4. Verify `/admin/users` contains mixed role and status states
5. Login as `committee.one@example.com` and confirm admin screens are blocked
6. Inspect finance records for draft and approved workflows
7. Inspect notification logs for failed and pending cases

## Seeder Classes Added

- `Database\Seeders\ReferenceDataSeeder`
- `Database\Seeders\UserScenarioSeeder`
- `Database\Seeders\WorkflowScenarioSeeder`

These run from `DatabaseSeeder` after `RolesAndPermissionsSeeder`.
