# Role: Auditor

**Level:** 3 — Tenant User  
**Scope:** Single mosque (tenant)  
**Description:** External or internal auditor. Read-only access focused on audit trails, all financial reports (with export), and financial data overview. Cannot create, edit, or delete any records. Cannot access reference data management.

---

## Access Summary

| Module | View | Create | Edit | Delete | Approve | Export |
|--------|------|--------|------|--------|---------|--------|
| Dashboard | ✅ | — | — | — | — | — |
| Profile | ✅ | — | ✅ | — | — | — |
| Notifications | ✅ | — | ✅ | ✅ | — | — |
| Masjid Profile | ✅ | — | — | — | — | — |
| User Management | ✅ (view) | — | — | — | — | — |
| Akaun | ❌ | — | — | — | — | — |
| Hasil (Income) | ✅ (via finance.view) | — | — | — | — | — |
| Belanja (Expense) | ✅ (via finance.view) | — | — | — | — | — |
| Baucar Bayaran | ❌ | — | — | — | — | — |
| Pindahan Akaun | ❌ | — | — | — | — | — |
| Sumber Hasil | ❌ | — | — | — | — | — |
| Kategori Belanja | ❌ | — | — | — | — | — |
| Tabung Khas | ❌ | — | — | — | — | — |
| Program Masjid | ❌ | — | — | — | — | — |
| Running No | ❌ | — | — | — | — | — |
| Bank Import | ❌ | — | — | — | — | — |
| **Log Aktiviti (Audit)** | ✅ | — | — | — | — | — |
| All Reports | ✅ | — | — | — | — | ✅ |

---

## Screens & Fields

### 1. Dashboard

| Field | Description |
|-------|-------------|
| Finance summary | Overview of income and expense totals |
| Notification bell | Unread count |

---

### 2. Masjid Profile — View Only

| Field | Description |
|-------|-------------|
| `nama` | Mosque name |
| `alamat` | Address |
| `negeri` | State |
| `status` | Active / Suspended |

---

### 3. User List — View Only

| Field | Description |
|-------|-------------|
| `name` | Full name |
| `email` | Email |
| `peranan` | Role label |
| `aktif` | Active / Inactive |

---

### 4. Finance Overview (via `finance.view`)

Read-only access to financial transaction data. Accessible through the dashboard or report context — not through dedicated CRUD screens.

| Data | Description |
|------|-------------|
| Hasil records | Income transactions |
| Belanja records | Expense transactions |
| Transaction status | pending / approved |
| Linked accounts | Account per transaction |
| Linked funds/programs | Tabung and program references |

---

### 5. Log Aktiviti (Audit Log) — View Only

**Primary screen for Auditor role.**

| Field | Description |
|-------|-------------|
| `created_at` | Timestamp of action |
| `user.name` | Who performed the action |
| `action` | create / update / delete / approve / login / logout |
| `module` | Module affected (Hasil, Belanja, Akaun, etc.) |
| `description` | Human-readable description of the change |
| `ip_address` | IP address of actor |

Filters available:

| Filter | Type | Description |
|--------|------|-------------|
| `tarikh_mula` | Date | Start date |
| `tarikh_tamat` | Date | End date |
| `user_id` | Select | Filter by user |
| `module` | Select | Filter by module |
| `action` | Select | Filter by action type |

---

### 6. Reports — View + Export

All 6 reports are accessible with full export capability.

| Filter | Type | Description |
|--------|------|-------------|
| `tarikh_mula` | Date | Start date |
| `tarikh_tamat` | Date | End date |
| `id_akaun` | Select | Filter by account |

| Report | Description | Export |
|--------|-------------|--------|
| Laporan Buku Tunai | Cash book — all income and expense by account | ✅ PDF / Excel |
| Laporan Jumaat | Friday collection summary by week | ✅ PDF / Excel |
| Laporan Derma | Donation income breakdown | ✅ PDF / Excel |
| Laporan Belanja | Expense breakdown by category | ✅ PDF / Excel |
| Laporan Penyata | Full financial statement (opening + closing balance) | ✅ PDF / Excel |
| Laporan Tabung | Special fund balance and movements | ✅ PDF / Excel |

---

### 7. Profile

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Display name |
| `email` | Email | ✅ | Login email |
| `current_password` | Password | ✅ (if changing) | Verification |
| `new_password` | Password | — | New password |
| `new_password_confirmation` | Password | — | Confirm |

---

### 8. Notifications

| Field | Description |
|-------|-------------|
| `title` | Title |
| `message` | Body |
| `type` | Category |
| `read_at` | Read timestamp |
| Actions | Mark read, Mark unread, Delete |
