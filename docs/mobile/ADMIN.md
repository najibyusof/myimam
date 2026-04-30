# Role: Admin

**Level:** 2 — Tenant Administrator  
**Scope:** Single mosque (tenant)  
**Description:** Mosque administrator. Full control over their mosque's data — users, finance, reports, and CMS. Cannot access other mosques, system settings, or subscription management.

---

## Access Summary

| Module | View | Create | Edit | Delete | Approve | Export |
|--------|------|--------|------|--------|---------|--------|
| Dashboard | ✅ | — | — | — | — | — |
| Profile | ✅ | — | ✅ | — | — | — |
| Notifications | ✅ | — | ✅ | ✅ | — | — |
| Masjid Profile (own) | ✅ | — | ✅ | — | — | — |
| CMS Builder | ✅ | — | ✅ | — | — | — |
| User Management | ✅ | ✅ | ✅ | ✅ | — | — |
| Roles & Permissions | ✅ | ✅ | ✅ | ✅ | — | — |
| Akaun (Accounts) | ✅ | ✅ | ✅ | ✅ | — | — |
| Hasil (Income) | ✅ | ✅ | ✅ | ✅ | — | ✅ |
| Belanja (Expense) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Baucar Bayaran | ✅ | ✅ | ✅ | — | ✅ | — |
| Pindahan Akaun | ✅ | ✅ | ✅ | ✅ | — | — |
| Sumber Hasil | ✅ | ✅ | ✅ | ✅ | — | — |
| Kategori Belanja | ✅ | ✅ | ✅ | ✅ | — | — |
| Tabung Khas | ✅ | ✅ | ✅ | ✅ | — | — |
| Program Masjid | ✅ | ✅ | ✅ | ✅ | — | — |
| Running No | ✅ | — | ✅ | — | — | — |
| Bank Import (CSV/PDF) | ✅ | ✅ | — | — | — | — |
| Log Aktiviti (Audit) | ✅ | — | — | — | — | — |
| All Reports | ✅ | — | — | — | — | ✅ |

> **Not accessible to Admin:** System Settings, Subscription Management, global Masjid CRUD (create/delete mosques)

---

## Screens & Fields

### 1. Dashboard

| Field | Description |
|-------|-------------|
| Total hasil (this month) | Sum of all income this month |
| Total belanja (this month) | Sum of all expenses this month |
| Akaun balances | Balance per account |
| Recent transactions | Last 5–10 hasil and belanja entries |
| Pending approvals | Count of belanja/baucar awaiting approval |
| Notification bell | Unread count |

---

### 2. Masjid Profile (Own Mosque)

**Read + Edit own mosque info**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama` | Text | ✅ | Mosque name |
| `code` | Text | — | Slug (read-only) |
| `alamat` | Textarea | ✅ | Address |
| `daerah` | Text | — | District |
| `negeri` | Text | ✅ | State |
| `no_pendaftaran` | Text | — | Registration number |
| `tarikh_daftar` | Date | — | Registration date |
| `status` | Display | — | Read-only (managed by Superadmin) |
| `subscription_status` | Display | — | Read-only |
| `subscription_expiry` | Display | — | Read-only |

---

### 3. CMS Builder

Manage the mosque's public landing page and login page.

| Field | Type | Description |
|-------|------|-------------|
| `slug` | Select | Page: `home` or `login` |
| Components | Drag-and-drop | Hero, Text, Card, Grid, Button, Image, Login Form |
| Component `props` | Mixed | title, subtitle, text, image_url, button_text, button_link, align, padding, columns |
| Media upload | File | Upload images to media library |
| Version history | List | Restore a previous saved version |
| AI Generate | Text prompt | Generate layout from a text description |

---

### 4. User Management

#### User List

| Field | Description |
|-------|-------------|
| `name` | Full name |
| `email` | Email address |
| `peranan` | Role label |
| `aktif` | Active / Inactive |
| Actions | Edit, Toggle Status, Assign Roles, Delete |

#### Create / Edit User

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Full name |
| `email` | Email | ✅ | Login email |
| `password` | Password | ✅ (create) | Password |
| `peranan` | Text | — | Position/role label |
| `aktif` | Toggle | ✅ | Account active status |
| `roles` | Multi-select | ✅ | System roles (Admin, Bendahari, AJK, etc.) |

> `id_masjid` is automatically set to the Admin's mosque — users cannot be assigned to a different mosque.

#### Assign Roles Screen

| Field | Description |
|-------|-------------|
| `user.name` | User being modified |
| `roles` | Multi-select checkboxes of available roles |

---

### 5. Roles & Permissions

| Field | Description |
|-------|-------------|
| `role.name` | Role name |
| `permissions` | Checkboxes grouped by module |

---

### 6. Akaun (Accounts)

#### List

| Field | Description |
|-------|-------------|
| `nama_akaun` | Account name |
| `jenis` | `tunai` (cash) / `bank` |
| `nama_bank` | Bank name |
| `no_akaun` | Account number |
| `status_aktif` | Active / Inactive |
| Current balance | Computed net balance |
| Actions | Edit, Delete |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama_akaun` | Text | ✅ | Account name |
| `jenis` | Select | ✅ | `tunai` or `bank` |
| `no_akaun` | Text | — | Bank account number (required if bank) |
| `nama_bank` | Text | — | Bank name (required if bank) |
| `status_aktif` | Toggle | ✅ | Active status |

---

### 7. Hasil (Income)

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `no_resit` | Receipt number |
| `sumberHasil.nama_sumber` | Revenue source |
| `akaun.nama_akaun` | Account |
| `amaun_tunai` | Cash amount |
| `amaun_online` | Online amount |
| `jumlah` | Total |
| `tabungKhas.nama_tabung` | Special fund (if any) |
| `program.nama_program` | Program (if any) |
| Actions | View Receipt, Edit, Delete |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Collection date |
| `id_akaun` | Select | ✅ | Account to credit |
| `id_sumber_hasil` | Select | ✅ | Revenue source |
| `amaun_tunai` | Decimal | — | Cash component |
| `amaun_online` | Decimal | — | Online transfer component |
| `jumlah` | Decimal | ✅ | Total (auto-calculates from above) |
| `id_tabung_khas` | Select | — | Assign to special fund |
| `id_program` | Select | — | Assign to program |
| `jenis_jumaat` | Select | — | Friday collection type (if source is Jumaat) |
| `catatan` | Textarea | — | Notes |

#### Receipt Screen

| Field | Description |
|-------|-------------|
| `no_resit` | Receipt number |
| `tarikh` | Date |
| `masjid.nama` | Mosque name |
| `sumberHasil.nama_sumber` | Source |
| `jumlah` | Amount |
| `amaun_tunai` / `amaun_online` | Breakdown |
| `catatan` | Notes |

---

### 8. Belanja (Expense)

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `kategoriBelanja.nama_kategori` | Category |
| `penerima` | Recipient |
| `amaun` | Amount |
| `status` | pending / approved / rejected |
| `akaun.nama_akaun` | Account debited |
| `baucar.no_baucar` | Voucher number (if any) |
| Actions | View Attachment, Edit, Delete, Approve |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Expense date |
| `id_akaun` | Select | ✅ | Account to debit |
| `id_kategori_belanja` | Select | ✅ | Expense category |
| `amaun` | Decimal | ✅ | Amount |
| `penerima` | Text | ✅ | Recipient / payee |
| `id_tabung_khas` | Select | — | Special fund |
| `id_program` | Select | — | Program |
| `id_baucar` | Select | — | Link to payment voucher |
| `bukti_fail` | File | — | Proof/receipt document (image or PDF) |
| `catatan` | Textarea | — | Notes |

#### Approve Action

| Field | Description |
|-------|-------------|
| `status` | Change to `approved` |
| `dilulus_oleh` | Auto-set to current user ID |
| `tarikh_lulus` | Auto-set to current timestamp |

---

### 9. Baucar Bayaran (Payment Voucher)

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `no_baucar` | Voucher number |
| `akaun.nama_akaun` | Account |
| `kaedah` | Payment method |
| `jumlah` | Amount |
| `status` | draft / approved |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Voucher date |
| `id_akaun` | Select | ✅ | Account |
| `kaedah` | Select | ✅ | Payment method (cash / cheque / online) |
| `no_rujukan` | Text | — | Reference / cheque number |
| `jumlah` | Decimal | ✅ | Total amount |
| `catatan` | Textarea | — | Purpose / notes |

---

### 10. Pindahan Akaun (Account Transfer)

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Transfer date |
| `dariAkaun.nama_akaun` | From account |
| `keAkaun.nama_akaun` | To account |
| `amaun` | Amount |
| `catatan` | Notes |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Transfer date |
| `dari_akaun_id` | Select | ✅ | Source account |
| `ke_akaun_id` | Select | ✅ | Destination account |
| `amaun` | Decimal | ✅ | Amount |
| `catatan` | Textarea | — | Notes |

---

### 11. Sumber Hasil (Revenue Source)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `kod` | Text | ✅ | Short code |
| `nama_sumber` | Text | ✅ | Source name |
| `jenis` | Select | ✅ | Type (jumaat / derma / tabung / lain) |
| `aktif` | Toggle | ✅ | Active status |
| `is_baseline` | Toggle | — | System baseline (cannot delete) |

---

### 12. Kategori Belanja (Expense Category)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `kod` | Text | ✅ | Short code |
| `nama_kategori` | Text | ✅ | Category name |
| `aktif` | Toggle | ✅ | Active status |

---

### 13. Tabung Khas (Special Fund)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama_tabung` | Text | ✅ | Fund name |
| `aktif` | Toggle | ✅ | Active status |
| Current balance | Computed | — | Net allocation vs usage |

---

### 14. Program Masjid (Mosque Program)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama_program` | Text | ✅ | Program name |
| `aktif` | Toggle | ✅ | Active status |

---

### 15. Running No (Document Numbers)

| Field | Description |
|-------|-------------|
| `type` | `hasil` or `belanja` |
| `prefix` | Prefix string |
| `current_no` | Current sequence |
| `format` | Full format string |
| Actions | Generate Next, Edit prefix/format |

---

### 16. Bank Import

**CSV/Excel Import**

| Step | Fields |
|------|--------|
| Upload | File (`.xlsx`/`.csv`), Account selector |
| Preview | Parsed rows: tarikh, description, debit amount, credit amount |
| Confirm import | Select rows, confirm account mapping |

**PDF Import**

| Step | Fields |
|------|--------|
| Upload | PDF bank statement file |
| Preview | Extracted rows from PDF |
| Confirm import | Select rows to import |

---

### 17. Reports

All reports have shared filters:

| Filter | Type | Description |
|--------|------|-------------|
| `tarikh_mula` | Date | Start date |
| `tarikh_tamat` | Date | End date |
| `id_akaun` | Select | Filter by account (optional) |

| Report | Key Fields Displayed |
|--------|---------------------|
| Laporan Buku Tunai | Date, description, debit, credit, balance per account |
| Laporan Jumaat | Week, collection amounts, account |
| Laporan Derma | Date, donor type, amount, source |
| Laporan Belanja | Date, category, recipient, amount |
| Laporan Penyata | Opening balance, all transactions, closing balance |
| Laporan Tabung | Fund name, contributions, withdrawals, balance |

All reports have **Export to PDF/Excel** option.

---

### 18. Log Aktiviti (Audit Log)

| Field | Description |
|-------|-------------|
| `created_at` | Timestamp |
| `user.name` | Who acted |
| `action` | create / update / delete / approve |
| `module` | Module affected |
| `description` | Summary of change |

---

### 19. Profile

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Display name |
| `email` | Email | ✅ | Login email |
| `current_password` | Password | ✅ (if changing password) | Verification |
| `new_password` | Password | — | New password |
| `new_password_confirmation` | Password | — | Confirm new password |

---

### 20. Notifications

| Field | Description |
|-------|-------------|
| `title` | Title |
| `message` | Body text |
| `type` | Category |
| `read_at` | Read timestamp (null = unread) |
| `created_at` | Sent timestamp |
| Actions | Mark read, Mark unread, Delete, Delete all |
