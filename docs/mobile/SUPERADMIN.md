# Role: Superadmin

**Level:** 1 — System Global  
**Scope:** All mosques / tenants  
**Description:** Full system control. Can manage all mosques, all users across all tenants, subscriptions, CMS, and system settings. This is a platform-operator role — not assigned to mosque staff.

---

## Access Summary

| Module | View | Create | Edit | Delete | Approve | Export |
|--------|------|--------|------|--------|---------|--------|
| Dashboard | ✅ | — | — | — | — | — |
| Profile | ✅ | — | ✅ | — | — | — |
| Notifications | ✅ | — | ✅ | ✅ | — | — |
| **System Settings** | ✅ | — | ✅ | — | — | — |
| **Masjid Management** | ✅ | ✅ | ✅ | ✅ | — | — |
| **Subscription Management** | ✅ | ✅ | ✅ | — | — | — |
| **CMS Builder** | ✅ | — | ✅ | — | — | — |
| User Management | ✅ | ✅ | ✅ | ✅ | — | — |
| Roles & Permissions | ✅ | ✅ | ✅ | ✅ | — | — |
| Akaun (Accounts) | ✅ | ✅ | ✅ | ✅ | — | — |
| Hasil (Income) | ✅ | ✅ | ✅ | ✅ | — | ✅ |
| Belanja (Expense) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Baucar Bayaran (Voucher) | ✅ | ✅ | ✅ | — | ✅ | — |
| Pindahan Akaun (Transfer) | ✅ | ✅ | ✅ | ✅ | — | — |
| Sumber Hasil | ✅ | ✅ | ✅ | ✅ | — | — |
| Kategori Belanja | ✅ | ✅ | ✅ | ✅ | — | — |
| Tabung Khas (Fund) | ✅ | ✅ | ✅ | ✅ | — | — |
| Program Masjid | ✅ | ✅ | ✅ | ✅ | — | — |
| Running No | ✅ | — | ✅ | — | — | — |
| Bank Import (CSV/PDF) | ✅ | ✅ | — | — | — | — |
| Log Aktiviti (Audit) | ✅ | — | — | — | — | — |
| All Reports | ✅ | — | — | — | — | ✅ |

---

## Screens & Fields

### 1. Dashboard

| Field | Description |
|-------|-------------|
| Total masjid count | Number of all registered mosques |
| Active / suspended mosque count | Status breakdown |
| Recent activity | Latest transactions across tenants |
| Notification bell | Unread count |

---

### 2. System Settings

**Screen:** `/admin/settings`

| Field | Type | Description |
|-------|------|-------------|
| `app_name` | Text | Platform name |
| `app_url` | URL | Base URL |
| `mail_from_name` | Text | Sender name for emails |
| `mail_from_address` | Email | Sender email address |
| Other system config keys | Mixed | As configured in `SystemSettings` table |

---

### 3. Masjid Management

**Screens:** List → Detail → Create / Edit

#### List Screen Fields

| Field | Description |
|-------|-------------|
| `nama` | Mosque name |
| `code` | Unique slug code |
| `negeri` | State |
| `status` | active / suspended |
| `subscription_status` | active / expired / pending |
| Actions | View, Edit, Suspend, Activate, Delete |

#### Create / Edit Form Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama` | Text | ✅ | Mosque name |
| `code` | Text | ✅ | Unique identifier slug |
| `alamat` | Textarea | ✅ | Full address |
| `daerah` | Text | — | District |
| `negeri` | Text | ✅ | State |
| `no_pendaftaran` | Text | — | Official registration number |
| `tarikh_daftar` | Date | — | Registration date |
| `status` | Select | ✅ | `active` / `suspended` |

---

### 4. Subscription Management

**Screens:** List → Assign Subscription → Create / Edit Plan

#### Subscription Plan Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Plan name |
| `price` | Decimal | ✅ | Monthly/annual price |
| `duration_months` | Number | ✅ | Plan duration |
| `features` | Textarea / list | — | Features included |
| `is_active` | Toggle | ✅ | Enable/disable plan |

#### Assign Subscription to Mosque Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `plan_id` | Select | ✅ | Subscription plan to assign |
| `start_date` | Date | ✅ | Subscription start date |
| `end_date` | Date | ✅ | Subscription expiry date |

---

### 5. CMS Builder

**Screens:** Home page editor, Login page editor, Media library

| Field | Type | Description |
|-------|------|-------------|
| `slug` | Select | Page to edit: `home` or `login` |
| Page components | Drag-and-drop | Hero, Text, Card, Grid, Button, Image, Login Form |
| Each component `props` | Mixed | title, subtitle, text, image_url, button_text, button_link, align, padding, columns |
| Media upload | File | Upload images to media library |
| Version history | List | Restore previous page versions |
| AI Generate | Text prompt | Generate page content using AI |

---

### 6. User Management

**Screens:** List → Create / Edit → Assign Roles → View Permissions

#### User List Fields

| Field | Description |
|-------|-------------|
| `name` | User's full name |
| `email` | Email address |
| `peranan` | Role label |
| `masjid.nama` | Mosque assigned to |
| `aktif` | Active / Inactive status |
| Actions | View, Edit, Toggle Status, Assign Roles, Delete |

#### Create / Edit User Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Full name |
| `email` | Email | ✅ | Login email |
| `password` | Password | ✅ (create) | Account password |
| `peranan` | Text | — | Role label / position |
| `id_masjid` | Select | ✅ | Mosque assignment |
| `aktif` | Toggle | ✅ | Account active status |
| `roles` | Multi-select | ✅ | Assigned system roles |

---

### 7. Roles & Permissions

| Field | Description |
|-------|-------------|
| `role.name` | Role name |
| `role.level` | Role hierarchy level |
| `permissions` | List of permission strings |
| Assign permissions | Checkbox list grouped by module |

---

### 8. Akaun (Accounts)

#### List Fields

| Field | Description |
|-------|-------------|
| `nama_akaun` | Account name |
| `jenis` | Type: `tunai` (cash) / `bank` |
| `nama_bank` | Bank name (if bank type) |
| `no_akaun` | Account number (if bank type) |
| `status_aktif` | Active / Inactive |
| Current balance | Computed: total hasil − total belanja for this account |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama_akaun` | Text | ✅ | Account name |
| `jenis` | Select | ✅ | `tunai` or `bank` |
| `no_akaun` | Text | — | Bank account number |
| `nama_bank` | Text | — | Bank name |
| `status_aktif` | Toggle | ✅ | Active status |

---

### 9. Hasil (Income)

#### List Fields

| Field | Description |
|-------|-------------|
| `tarikh` | Date of collection |
| `no_resit` | Receipt number |
| `sumberHasil.nama_sumber` | Revenue source name |
| `akaun.nama_akaun` | Account received into |
| `amaun_tunai` | Cash amount |
| `amaun_online` | Online transfer amount |
| `jumlah` | Total amount |
| `tabungKhas.nama_tabung` | Special fund (if applicable) |
| `program.nama_program` | Program (if applicable) |
| Actions | View Receipt, Edit, Delete |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Collection date |
| `id_akaun` | Select | ✅ | Account to credit |
| `id_sumber_hasil` | Select | ✅ | Revenue source |
| `amaun_tunai` | Decimal | — | Cash portion |
| `amaun_online` | Decimal | — | Online transfer portion |
| `jumlah` | Decimal | ✅ | Total (auto-sum) |
| `id_tabung_khas` | Select | — | Assign to special fund |
| `id_program` | Select | — | Assign to program |
| `jenis_jumaat` | Select | — | Friday collection type |
| `catatan` | Textarea | — | Notes |

---

### 10. Belanja (Expense)

#### List Fields

| Field | Description |
|-------|-------------|
| `tarikh` | Expense date |
| `akaun.nama_akaun` | Account debited |
| `kategoriBelanja.nama_kategori` | Expense category |
| `penerima` | Recipient/payee |
| `amaun` | Amount |
| `status` | pending / approved / rejected |
| `baucar.no_baucar` | Linked voucher number |
| Actions | View Attachment, Edit, Delete, Approve |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Expense date |
| `id_akaun` | Select | ✅ | Account to debit |
| `id_kategori_belanja` | Select | ✅ | Expense category |
| `amaun` | Decimal | ✅ | Expense amount |
| `penerima` | Text | ✅ | Recipient / payee name |
| `id_tabung_khas` | Select | — | Fund this expense belongs to |
| `id_program` | Select | — | Program this expense belongs to |
| `id_baucar` | Select | — | Linked payment voucher |
| `bukti_fail` | File upload | — | Receipt / proof document |
| `catatan` | Textarea | — | Notes |

---

### 11. Baucar Bayaran (Payment Voucher)

#### List Fields

| Field | Description |
|-------|-------------|
| `tarikh` | Voucher date |
| `no_baucar` | Voucher number |
| `akaun.nama_akaun` | Account |
| `kaedah` | Payment method |
| `jumlah` | Amount |
| `status` | draft / approved |
| `dilulus_oleh` | Approved by |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Voucher date |
| `id_akaun` | Select | ✅ | Account |
| `kaedah` | Select | ✅ | Payment method (e.g., cash, cheque, online) |
| `no_rujukan` | Text | — | Reference number |
| `jumlah` | Decimal | ✅ | Total voucher amount |
| `catatan` | Textarea | — | Notes / description |

---

### 12. Pindahan Akaun (Account Transfer)

#### List Fields

| Field | Description |
|-------|-------------|
| `tarikh` | Transfer date |
| `dariAkaun.nama_akaun` | Source account |
| `keAkaun.nama_akaun` | Destination account |
| `amaun` | Transfer amount |
| `catatan` | Notes |
| `created_by` | Recorded by |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Transfer date |
| `dari_akaun_id` | Select | ✅ | Source account |
| `ke_akaun_id` | Select | ✅ | Destination account |
| `amaun` | Decimal | ✅ | Amount to transfer |
| `catatan` | Textarea | — | Notes |

---

### 13. Sumber Hasil (Revenue Source)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `kod` | Text | ✅ | Short code |
| `nama_sumber` | Text | ✅ | Source name |
| `jenis` | Select | ✅ | Type (e.g., `jumaat`, `derma`, `tabung`) |
| `aktif` | Toggle | ✅ | Active status |
| `is_baseline` | Toggle | — | System default baseline |

---

### 14. Kategori Belanja (Expense Category)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `kod` | Text | ✅ | Short code |
| `nama_kategori` | Text | ✅ | Category name |
| `aktif` | Toggle | ✅ | Active status |

---

### 15. Tabung Khas (Special Fund)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama_tabung` | Text | ✅ | Fund name |
| `aktif` | Toggle | ✅ | Active status |
| Current balance | Computed | — | Net balance of this fund |

---

### 16. Program Masjid (Mosque Program)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama_program` | Text | ✅ | Program name |
| `aktif` | Toggle | ✅ | Active status |

---

### 17. Running No (Document Number Config)

| Field | Type | Description |
|-------|------|-------------|
| `prefix` | Text | Prefix for receipt/voucher number |
| `current_no` | Number | Current sequence number |
| `format` | Text | Full format pattern |
| `type` | Text | `hasil` or `belanja` |
| Actions | View, Generate Next, Edit |

---

### 18. Bank Import (CSV)

| Step | Fields |
|------|--------|
| Upload | File upload (`.xlsx`, `.csv`), Account selector |
| Preview | Parsed rows: tarikh, penerima/pemberi, amaun, jenis (credit/debit) |
| Confirm | Select rows to import, map to akaun |

---

### 19. Reports

All reports share common filter fields:

| Field | Type | Description |
|-------|------|-------------|
| `tarikh_mula` | Date | Start date |
| `tarikh_tamat` | Date | End date |
| `id_akaun` | Select | Filter by account |
| `id_masjid` | Select | Filter by mosque (Superadmin only) |

| Report | Description |
|--------|-------------|
| Laporan Buku Tunai | Cash book: all income and expense transactions |
| Laporan Jumaat | Friday collection report |
| Laporan Derma | Donation income report |
| Laporan Belanja | Expense breakdown by category |
| Laporan Penyata | Full financial statement |
| Laporan Tabung | Special fund balance and movements |

---

### 20. Log Aktiviti (Audit Log)

| Field | Description |
|-------|-------------|
| `created_at` | Timestamp of action |
| `user.name` | Who performed the action |
| `action` | What was done (create, update, delete) |
| `module` | Which module was affected |
| `description` | Human-readable action description |
| `ip_address` | IP of the actor |

---

### 21. Profile (All Roles)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Display name |
| `email` | Email | ✅ | Login email |
| `current_password` | Password | ✅ (if changing password) | Current password |
| `new_password` | Password | — | New password |
| `new_password_confirmation` | Password | — | Confirm new password |

---

### 22. Notifications (All Roles)

| Field | Description |
|-------|-------------|
| `title` | Notification title |
| `message` | Notification body |
| `type` | Category (finance, masjid, system) |
| `read_at` | Timestamp when read (null = unread) |
| `created_at` | When it was sent |
| Actions | Mark as read, Mark as unread, Delete |
