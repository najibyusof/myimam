# Role: MasjidOfficer

**Level:** 3 — Tenant User  
**Scope:** Single mosque (tenant)  
**Description:** Mosque operational officer. Can record new income entries and view financial reports. Does not have access to expense management, reference data, accounts, or audit logs.

---

## Access Summary

| Module | View | Create | Edit | Delete | Approve | Export |
|--------|------|--------|------|--------|---------|--------|
| Dashboard | ✅ | — | — | — | — | — |
| Profile | ✅ | — | ✅ | — | — | — |
| Notifications | ✅ | — | ✅ | ✅ | — | — |
| Masjid Profile | ✅ | — | — | — | — | — |
| User Management | ❌ | — | — | — | — | — |
| Akaun | ❌ | — | — | — | — | — |
| Hasil (Income) | ✅ | ✅ | — | — | — | — |
| Belanja (Expense) | ❌ | — | — | — | — | — |
| Baucar Bayaran | ❌ | — | — | — | — | — |
| Pindahan Akaun | ❌ | — | — | — | — | — |
| Sumber Hasil | ❌ | — | — | — | — | — |
| Kategori Belanja | ❌ | — | — | — | — | — |
| Tabung Khas | ❌ | — | — | — | — | — |
| Program Masjid | ❌ | — | — | — | — | — |
| Running No | ❌ | — | — | — | — | — |
| Bank Import | ❌ | — | — | — | — | — |
| Log Aktiviti | ❌ | — | — | — | — | — |
| Reports | ✅ (view only) | — | — | — | — | ❌ |

---

## Screens & Fields

### 1. Dashboard

| Field | Description |
|-------|-------------|
| Hasil summary | Recent income recorded |
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

### 3. Hasil (Income) — View + Create Only

> Can record new income but **cannot edit or delete** existing records.

#### List

| Field | Description |
|-------|-------------|
| `tarikh` | Date |
| `no_resit` | Receipt number |
| `sumberHasil.nama_sumber` | Revenue source |
| `akaun.nama_akaun` | Account |
| `jumlah` | Total |
| `catatan` | Notes |

#### Create Form

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
| `jenis_jumaat` | Select | — | Friday collection type |
| `catatan` | Textarea | — | Notes |

---

### 4. Reports — View Only (No Export)

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

### 5. Profile

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Display name |
| `email` | Email | ✅ | Login email |
| `current_password` | Password | ✅ (if changing) | Verification |
| `new_password` | Password | — | New password |
| `new_password_confirmation` | Password | — | Confirm |

---

### 6. Notifications

| Field | Description |
|-------|-------------|
| `title` | Title |
| `message` | Body |
| `type` | Category |
| `read_at` | Read timestamp |
| Actions | Mark read, Mark unread, Delete |
