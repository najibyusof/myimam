# Role: Bendahari (Treasurer)

**Level:** 3 — Tenant User  
**Scope:** Single mosque (tenant)  
**Description:** Mosque treasurer. Full finance data entry and management — income, expenses, accounts, transfers, reference data, and all financial reports. Can also manage user profiles (view/update) and approve transactions.

---

## Access Summary

| Module | View | Create | Edit | Delete | Approve | Export |
|--------|------|--------|------|--------|---------|--------|
| Dashboard | ✅ | — | — | — | — | — |
| Profile | ✅ | — | ✅ | — | — | — |
| Notifications | ✅ | — | ✅ | ✅ | — | — |
| Masjid Profile | ✅ | — | — | — | — | — |
| User Management | ✅ (view/update) | — | ✅ | — | — | — |
| Akaun (Accounts) | ✅ | ✅ | ✅ | ✅ | — | — |
| Hasil (Income) | ✅ | ✅ | ✅ | ✅ | — | — |
| Belanja (Expense) | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| Baucar Bayaran | ❌ | — | — | — | — | — |
| Pindahan Akaun | ✅ | ✅ | ✅ | ✅ | — | — |
| Sumber Hasil | ✅ | ✅ | ✅ | ✅ | — | — |
| Kategori Belanja | ✅ | ✅ | ✅ | ✅ | — | — |
| Tabung Khas | ✅ | ✅ | ✅ | ✅ | — | — |
| Program Masjid | ✅ | ✅ | ✅ | ✅ | — | — |
| Running No | ✅ | — | ✅ | — | — | — |
| Bank Import | ❌ | — | — | — | — | — |
| Log Aktiviti | ❌ | — | — | — | — | — |
| All Reports | ✅ | — | — | — | — | ✅ |

---

## Screens & Fields

### 1. Dashboard

| Field | Description |
|-------|-------------|
| Total hasil (this month) | Income summary |
| Total belanja (this month) | Expense summary |
| Account balances | Per-account net balance |
| Pending approvals | Count of pending belanja |
| Recent transactions | Last 10 entries |
| Notification bell | Unread count |

---

### 2. User Management — View + Update Only

#### User List

| Field | Description |
|-------|-------------|
| `name` | Full name |
| `email` | Email |
| `peranan` | Role label |
| `aktif` | Active / Inactive |
| Actions | Edit (update only) |

#### Edit User Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Full name |
| `email` | Email | ✅ | Login email |
| `peranan` | Text | — | Position label |
| `aktif` | Toggle | ✅ | Active status |

---

### 3. Akaun (Accounts) — Full CRUD

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
| `no_akaun` | Text | — | Account number (required if bank) |
| `nama_bank` | Text | — | Bank name (required if bank) |
| `status_aktif` | Toggle | ✅ | Active |

---

### 4. Hasil (Income) — Full CRUD

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
| `tabungKhas.nama_tabung` | Special fund |
| `program.nama_program` | Program |
| Actions | View Receipt, Edit, Delete |

#### Create / Edit Form

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `tarikh` | Date | ✅ | Collection date |
| `id_akaun` | Select | ✅ | Account to credit |
| `id_sumber_hasil` | Select | ✅ | Revenue source |
| `amaun_tunai` | Decimal | — | Cash portion |
| `amaun_online` | Decimal | — | Online transfer portion |
| `jumlah` | Decimal | ✅ | Total (auto-calculates) |
| `id_tabung_khas` | Select | — | Special fund |
| `id_program` | Select | — | Program |
| `jenis_jumaat` | Select | — | Friday type (if Jumaat source) |
| `catatan` | Textarea | — | Notes |

---

### 5. Belanja (Expense) — Full CRUD + Approve

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `kategoriBelanja.nama_kategori` | Category |
| `penerima` | Recipient |
| `amaun` | Amount |
| `status` | pending / approved / rejected |
| `akaun.nama_akaun` | Account |
| Actions | View Attachment, Edit, Delete, **Approve** |

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
| `id_baucar` | Select | — | Link payment voucher |
| `bukti_fail` | File | — | Proof document |
| `catatan` | Textarea | — | Notes |

#### Approve Action

| Field | Auto-set Value |
|-------|---------------|
| `status` | `approved` |
| `dilulus_oleh` | Current user ID |
| `tarikh_lulus` | Current timestamp |

---

### 6. Pindahan Akaun (Account Transfer) — Full CRUD

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
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

### 7. Sumber Hasil (Revenue Source) — Full CRUD

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `kod` | Text | ✅ | Short code |
| `nama_sumber` | Text | ✅ | Source name |
| `jenis` | Select | ✅ | Type (jumaat / derma / tabung / lain) |
| `aktif` | Toggle | ✅ | Active status |
| `is_baseline` | Toggle | — | Protected system default |

---

### 8. Kategori Belanja (Expense Category) — Full CRUD

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `kod` | Text | ✅ | Short code |
| `nama_kategori` | Text | ✅ | Category name |
| `aktif` | Toggle | ✅ | Active status |

---

### 9. Tabung Khas (Special Fund) — Full CRUD

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama_tabung` | Text | ✅ | Fund name |
| `aktif` | Toggle | ✅ | Active status |
| Current balance | Computed | — | Net fund balance |

---

### 10. Program Masjid (Mosque Program) — Full CRUD

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `nama_program` | Text | ✅ | Program name |
| `aktif` | Toggle | ✅ | Active status |

---

### 11. Running No — View + Update

| Field | Type | Description |
|-------|------|-------------|
| `type` | Display | `hasil` or `belanja` |
| `prefix` | Text | Prefix string |
| `current_no` | Number | Current sequence |
| `format` | Text | Full format |
| Actions | Edit, Generate Next |

---

### 12. Reports — View + Export

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

### 13. Profile

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Display name |
| `email` | Email | ✅ | Login email |
| `current_password` | Password | ✅ (if changing) | Verification |
| `new_password` | Password | — | New password |
| `new_password_confirmation` | Password | — | Confirm |

---

### 14. Notifications

| Field | Description |
|-------|-------------|
| `title` | Title |
| `message` | Body |
| `type` | Category |
| `read_at` | Read timestamp |
| Actions | Mark read, Mark unread, Delete |
