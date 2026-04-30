# Role: FinanceOfficer

**Level:** 3 — Tenant User  
**Scope:** Single mosque (tenant)  
**Description:** Finance data entry officer. Responsible for recording income, expenses, and account transfers. Can approve finance transactions. Has access to view reports but cannot export. Cannot manage reference data (categories, sources, funds, programs) or users.

---

## Access Summary

| Module | View | Create | Edit | Delete | Approve | Export |
|--------|------|--------|------|--------|---------|--------|
| Dashboard | ✅ | — | — | — | — | — |
| Profile | ✅ | — | ✅ | — | — | — |
| Notifications | ✅ | — | ✅ | ✅ | — | — |
| Masjid Profile | ✅ | — | — | — | — | — |
| User Management | ❌ | — | — | — | — | — |
| Akaun (Accounts) | ❌ | — | — | — | — | — |
| Hasil (Income) | ✅ | ✅ | ✅ | ✅ | — | — |
| Belanja (Expense) | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| Baucar Bayaran | ❌ | — | — | — | — | — |
| Pindahan Akaun | ✅ | ✅ | ✅ | ✅ | — | — |
| Sumber Hasil | ❌ | — | — | — | — | — |
| Kategori Belanja | ❌ | — | — | — | — | — |
| Tabung Khas | ❌ | — | — | — | — | — |
| Program Masjid | ❌ | — | — | — | — | — |
| Running No | ✅ | — | — | — | — | — |
| Bank Import | ❌ | — | — | — | — | — |
| Log Aktiviti | ❌ | — | — | — | — | — |
| Reports | ✅ (view only) | — | — | — | — | ❌ |

---

## Screens & Fields

### 1. Dashboard

| Field | Description |
|-------|-------------|
| Total hasil (this month) | Summary of income |
| Total belanja (this month) | Summary of expenses |
| Recent transactions | Latest hasil and belanja entries |
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

### 3. Hasil (Income) — Full CRUD

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
| `amaun_tunai` | Decimal | — | Cash portion |
| `amaun_online` | Decimal | — | Online portion |
| `jumlah` | Decimal | ✅ | Total (auto-calculates) |
| `id_tabung_khas` | Select | — | Special fund |
| `id_program` | Select | — | Program |
| `jenis_jumaat` | Select | — | Friday collection type |
| `catatan` | Textarea | — | Notes |

---

### 4. Belanja (Expense) — Full CRUD + Approve

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `kategoriBelanja.nama_kategori` | Category |
| `penerima` | Recipient |
| `amaun` | Amount |
| `status` | pending / approved |
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
| `bukti_fail` | File | — | Proof document (image/PDF) |
| `catatan` | Textarea | — | Notes |

#### Approve Action

| Field | Auto-set Value |
|-------|---------------|
| `status` | `approved` |
| `dilulus_oleh` | Current user ID |
| `tarikh_lulus` | Current timestamp |

---

### 5. Pindahan Akaun (Account Transfer) — Full CRUD

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

### 6. Running No — View Only

| Field | Description |
|-------|-------------|
| `type` | `hasil` or `belanja` |
| `prefix` | Prefix |
| `current_no` | Current sequence |

---

### 7. Reports — View Only (No Export)

| Filter | Type | Description |
|--------|------|-------------|
| `tarikh_mula` | Date | Start date |
| `tarikh_tamat` | Date | End date |

| Report | Accessible |
|--------|------------|
| Laporan Buku Tunai | ✅ (view only) |
| Laporan Jumaat | ✅ (view only) |
| Laporan Derma | ✅ (view only) |
| Laporan Belanja | ✅ (view only) |
| Laporan Penyata | ✅ (view only) |
| Laporan Tabung | ✅ (view only) |

> Export to PDF/Excel **not available** for this role.

---

### 8. Profile

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Display name |
| `email` | Email | ✅ | Login email |
| `current_password` | Password | ✅ (if changing) | Verification |
| `new_password` | Password | — | New password |
| `new_password_confirmation` | Password | — | Confirm |

---

### 9. Notifications

| Field | Description |
|-------|-------------|
| `title` | Title |
| `message` | Body |
| `type` | Category |
| `read_at` | Read timestamp |
| Actions | Mark read, Mark unread, Delete |
