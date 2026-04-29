# Role: User (General)

**Level:** 3 — Tenant User  
**Scope:** Single mosque (tenant)  
**Description:** General authenticated user. Minimal access — can view the dashboard and reports only. No financial data entry, no management functions. This is the baseline role for new accounts before a specific role is assigned.

---

## Access Summary

| Module | View | Create | Edit | Delete | Approve | Export |
|--------|------|--------|------|--------|---------|--------|
| Dashboard | ✅ | — | — | — | — | — |
| Profile | ✅ | — | ✅ | — | — | — |
| Notifications | ✅ | — | ✅ | ✅ | — | — |
| All Other Modules | ❌ | — | — | — | — | — |
| Reports | ✅ (view only) | — | — | — | — | ❌ |

---

## Screens & Fields

### 1. Dashboard

| Field | Description |
|-------|-------------|
| General summary | Basic overview visible to authenticated users |
| Notification bell | Unread count |

---

### 2. Reports — View Only (No Export)

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

### 3. Profile

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | Text | ✅ | Display name |
| `email` | Email | ✅ | Login email |
| `current_password` | Password | ✅ (if changing) | Verification |
| `new_password` | Password | — | New password |
| `new_password_confirmation` | Password | — | Confirm |

---

### 4. Notifications

| Field | Description |
|-------|-------------|
| `title` | Title |
| `message` | Body |
| `type` | Category |
| `read_at` | Read timestamp |
| Actions | Mark read, Mark unread, Delete |

---

> **Note for mobile developers:** If a user with this role attempts to access any restricted screen, the API will return `403 Forbidden`. The mobile app should hide all navigation items that the role does not have access to, and gracefully handle 403 responses.
