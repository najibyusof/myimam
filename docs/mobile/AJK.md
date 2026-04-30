# Role: AJK (Ahli Jawatankuasa / Committee Member)

**Level:** 3 — Tenant User  
**Scope:** Single mosque (tenant)  
**Description:** Mosque committee member. **View-only** access to all financial data including accounts, income, expenses, transfers, reference data, and reports. Cannot create, edit, delete, or approve any records.

---

## Access Summary

| Module | View | Create | Edit | Delete | Approve | Export |
|--------|------|--------|------|--------|---------|--------|
| Dashboard | ✅ | — | — | — | — | — |
| Profile | ✅ | — | ✅ | — | — | — |
| Notifications | ✅ | — | ✅ | ✅ | — | — |
| Masjid Profile | ✅ | — | — | — | — | — |
| User Management | ✅ (view) | — | — | — | — | — |
| Akaun | ✅ | — | — | — | — | — |
| Hasil (Income) | ✅ | — | — | — | — | — |
| Belanja (Expense) | ✅ | — | — | — | — | — |
| Baucar Bayaran | ❌ | — | — | — | — | — |
| Pindahan Akaun | ✅ | — | — | — | — | — |
| Sumber Hasil | ✅ | — | — | — | — | — |
| Kategori Belanja | ✅ | — | — | — | — | — |
| Tabung Khas | ✅ | — | — | — | — | — |
| Program Masjid | ✅ | — | — | — | — | — |
| Running No | ✅ | — | — | — | — | — |
| Bank Import | ❌ | — | — | — | — | — |
| Log Aktiviti | ❌ | — | — | — | — | — |
| Reports | ✅ | — | — | — | — | ❌ |

---

## Screens & Fields

### 1. Dashboard

| Field | Description |
|-------|-------------|
| Total hasil (this month) | Income summary |
| Total belanja (this month) | Expense summary |
| Account balances | Per-account balance |
| Recent activity | Latest transactions |
| Notification bell | Unread count |

---

### 2. Masjid Profile — View Only

| Field | Description |
|-------|-------------|
| `nama` | Mosque name |
| `alamat` | Address |
| `daerah` | District |
| `negeri` | State |
| `no_pendaftaran` | Registration number |
| `tarikh_daftar` | Registration date |
| `status` | Active / Suspended |

---

### 3. User List — View Only

| Field | Description |
|-------|-------------|
| `name` | Full name |
| `email` | Email |
| `peranan` | Role label |
| `aktif` | Active / Inactive |

> No edit or action buttons.

---

### 4. Akaun (Accounts) — View Only

| Field | Description |
|-------|-------------|
| `nama_akaun` | Account name |
| `jenis` | `tunai` / `bank` |
| `nama_bank` | Bank name |
| `no_akaun` | Account number |
| `status_aktif` | Active / Inactive |
| Current balance | Net balance |

---

### 5. Hasil (Income) — View Only

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
| `catatan` | Notes |

---

### 6. Belanja (Expense) — View Only

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `kategoriBelanja.nama_kategori` | Category |
| `penerima` | Recipient |
| `amaun` | Amount |
| `status` | pending / approved |
| `akaun.nama_akaun` | Account |
| `catatan` | Notes |

---

### 7. Pindahan Akaun (Account Transfer) — View Only

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `dariAkaun.nama_akaun` | From account |
| `keAkaun.nama_akaun` | To account |
| `amaun` | Amount |
| `catatan` | Notes |

---

### 8. Sumber Hasil — View Only

| Field | Description |
|-------|-------------|
| `kod` | Code |
| `nama_sumber` | Source name |
| `jenis` | Type |
| `aktif` | Active status |

---

### 9. Kategori Belanja — View Only

| Field | Description |
|-------|-------------|
| `kod` | Code |
| `nama_kategori` | Category name |
| `aktif` | Active status |

---

### 10. Tabung Khas (Special Fund) — View Only

| Field | Description |
|-------|-------------|
| `nama_tabung` | Fund name |
| `aktif` | Active status |
| Current balance | Net fund balance |

---

### 11. Program Masjid — View Only

| Field | Description |
|-------|-------------|
| `nama_program` | Program name |
| `aktif` | Active status |

---

### 12. Running No — View Only

| Field | Description |
|-------|-------------|
| `type` | `hasil` or `belanja` |
| `prefix` | Prefix |
| `current_no` | Current number |

---

### 13. Reports — View Only (No Export)

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

> Export **not available** for this role.

---

### 14. Profile

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Display name |
| `email` | Email | ✅ | Login email |
| `current_password` | Password | ✅ (if changing) | Verification |
| `new_password` | Password | — | New password |
| `new_password_confirmation` | Password | — | Confirm |

---

### 15. Notifications

| Field | Description |
|-------|-------------|
| `title` | Title |
| `message` | Body |
| `type` | Category |
| `read_at` | Read timestamp |
| Actions | Mark read, Mark unread, Delete |
