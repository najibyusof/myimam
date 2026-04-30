# Role: Manager

**Level:** 3 — Tenant User  
**Scope:** Single mosque (tenant)  
**Description:** Oversees mosque operations. Can view income and expense data, approve finance transactions, manage account transfers, and export reports. Does **not** enter income or expense records directly. Can view and update user profiles but cannot create or delete users.

---

## Access Summary

| Module | View | Create | Edit | Delete | Approve | Export |
|--------|------|--------|------|--------|---------|--------|
| Dashboard | ✅ | — | — | — | — | — |
| Profile | ✅ | — | ✅ | — | — | — |
| Notifications | ✅ | — | ✅ | ✅ | — | — |
| Masjid Profile (own) | ✅ | — | ✅ | — | — | — |
| User Management | ✅ (view) | — | ✅ | — | — | — |
| Roles (assign) | ✅ | — | ✅ | — | — | — |
| Akaun | ❌ | — | — | — | — | — |
| Hasil (Income) | ✅ | — | — | — | — | — |
| Belanja (Expense) | ✅ | — | — | — | ✅ | — |
| Baucar Bayaran | ❌ | — | — | — | — | — |
| Pindahan Akaun | ✅ | ✅ | ✅ | ✅ | — | — |
| Sumber Hasil | ❌ | — | — | — | — | — |
| Kategori Belanja | ❌ | — | — | — | — | — |
| Tabung Khas | ❌ | — | — | — | — | — |
| Program Masjid | ❌ | — | — | — | — | — |
| Running No | ✅ | — | — | — | — | — |
| Bank Import | ❌ | — | — | — | — | — |
| Log Aktiviti (Audit) | ✅ | — | — | — | — | — |
| Reports | ✅ | — | — | — | — | ✅ |

---

## Screens & Fields

### 1. Dashboard

| Field | Description |
|-------|-------------|
| Total hasil (this month) | Summary of income this month |
| Total belanja (this month) | Summary of expense this month |
| Pending approvals | Count of belanja pending approval |
| Recent transactions | Last transactions across hasil and belanja |
| Notification bell | Unread count |

---

### 2. User Management

> **View and update only — no create or delete.**

#### User List

| Field | Description |
|-------|-------------|
| `name` | Full name |
| `email` | Email |
| `peranan` | Role label |
| `aktif` | Active / Inactive |
| Actions | Edit (update only), Assign Roles |

#### Edit User Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Full name |
| `email` | Email | ✅ | Login email |
| `peranan` | Text | — | Position label |
| `aktif` | Toggle | ✅ | Active status |

#### Assign Roles

| Field | Description |
|-------|-------------|
| `roles` | Multi-select checkboxes |

---

### 3. Hasil (Income) — View Only

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `no_resit` | Receipt number |
| `sumberHasil.nama_sumber` | Revenue source |
| `akaun.nama_akaun` | Account |
| `jumlah` | Total amount |
| `catatan` | Notes |

> No create, edit, or delete actions available.

---

### 4. Belanja (Expense) — View + Approve

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `kategoriBelanja.nama_kategori` | Category |
| `penerima` | Recipient |
| `amaun` | Amount |
| `status` | pending / approved / rejected |
| `akaun.nama_akaun` | Account |
| Actions | View Attachment, **Approve** |

#### Approve Action

| Field | Description |
|-------|-------------|
| `status` | Set to `approved` |
| `dilulus_oleh` | Auto-set to current user |
| `tarikh_lulus` | Auto-set to now |

> No create or edit access on belanja.

---

### 5. Pindahan Akaun (Account Transfer) — Full CRUD

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Transfer date |
| `dariAkaun.nama_akaun` | From account |
| `keAkaun.nama_akaun` | To account |
| `amaun` | Amount |
| `catatan` | Notes |
| Actions | Edit, Delete |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Transfer date |
| `dari_akaun_id` | Select | ✅ | Source account |
| `ke_akaun_id` | Select | ✅ | Destination account |
| `amaun` | Decimal | ✅ | Amount |
| `catatan` | Textarea | — | Notes |

---

### 6. Running No — View Only

| Field | Description |
|-------|-------------|
| `type` | hasil or belanja |
| `prefix` | Prefix string |
| `current_no` | Current sequence number |
| `format` | Document number format |

---

### 7. Masjid Profile (Own) — View + Edit

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama` | Text | ✅ | Mosque name |
| `alamat` | Textarea | ✅ | Address |
| `daerah` | Text | — | District |
| `negeri` | Text | ✅ | State |
| `no_pendaftaran` | Text | — | Registration number |
| `tarikh_daftar` | Date | — | Registration date |
| `status` | Display | — | Read-only |

---

### 8. Log Aktiviti (Audit Log) — View Only

| Field | Description |
|-------|-------------|
| `created_at` | Timestamp |
| `user.name` | Who acted |
| `action` | create / update / delete / approve |
| `module` | Module affected |
| `description` | Summary |

---

### 9. Reports — View + Export

Filters available:

| Filter | Type | Description |
|--------|------|-------------|
| `tarikh_mula` | Date | Start date |
| `tarikh_tamat` | Date | End date |
| `id_akaun` | Select | Optional account filter |

| Report | Accessible |
|--------|------------|
| Laporan Buku Tunai | ✅ |
| Laporan Jumaat | ✅ |
| Laporan Derma | ✅ |
| Laporan Belanja | ✅ |
| Laporan Penyata | ✅ |
| Laporan Tabung | ✅ |

All reports: **Export to PDF / Excel available.**

---

### 10. Profile

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Display name |
| `email` | Email | ✅ | Login email |
| `current_password` | Password | ✅ (if changing) | Verification |
| `new_password` | Password | — | New password |
| `new_password_confirmation` | Password | — | Confirm |

---

### 11. Notifications

| Field | Description |
|-------|-------------|
| `title` | Title |
| `message` | Body |
| `type` | Category |
| `read_at` | Read timestamp |
| Actions | Mark read, Mark unread, Delete |
