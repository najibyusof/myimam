# IMAM API - Quick Reference Card

## 📌 Base URL

```
http://localhost:8000/api
```

## 🔑 Authentication Header

```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 🚀 Quick Commands

### Register

```bash
POST /auth/register
Body: {
  "name": "User Name",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Login

```bash
POST /auth/login
Body: {
  "email": "user@example.com",
  "password": "password123"
}
Returns: { token, user, expires_in, token_type }
```

### Get Current User

```bash
GET /auth/me
Auth: Required
```

### Update Profile

```bash
PATCH /auth/profile
Body: { "name": "New Name", "email": "new@email.com" }
```

### Upload Digital Signature (Profile)

```bash
POST /auth/profile/signature
Content-Type: multipart/form-data
Body: {
  "signature_image": <file>   # JPG/PNG/WebP, max 2MB, min 200×50px, max 2000×600px
}
Returns: { message, data: UserResource }
Rate limit: 3 uploads/min
```

### Remove Digital Signature

```bash
DELETE /auth/profile/signature
Returns: { message, data: UserResource }
Rate limit: 5 removals/min
```

### Change Password

```bash
POST /auth/change-password
Body: {
  "current_password": "old_pass",
  "new_password": "new_pass",
  "new_password_confirmation": "new_pass"
}
```

### Refresh Token

```bash
POST /auth/refresh
Auth: Required
```

### Logout

```bash
POST /auth/logout
Auth: Required
```

---

## 👥 User Management (Requires `users.manage`)

### List Users

```bash
GET /users[?search=NAME][&aktif=1][&role=ROLE][&per_page=15][&page=1]
```

### Create User

```bash
POST /users
Body: {
  "name": "User Name",
  "email": "user@example.com",
  "password": "password123",
  "peranan": "Officer",
  "id_masjid": 1,
  "roles": ["officer"]
}
```

### Get User

```bash
GET /users/{id}
```

### Update User

```bash
PATCH /users/{id}
Body: { "name": "New Name", "email": "new@email.com", "peranan": "Officer" }
```

### Delete User

```bash
DELETE /users/{id}
```

### Toggle Status

```bash
PATCH /users/{id}/status
```

### Assign Roles

```bash
POST /users/{id}/roles
Body: { "roles": ["officer", "analyst"] }
```

### Get User Permissions

```bash
GET /users/{id}/permissions
```

---

## 🕌 Masjid Management (Requires `masjid.manage`)

### List Masjids

```bash
GET /masjids[?search=NAME][&status=active][&negeri=STATE][&per_page=15]
```

### Create Masjid

```bash
POST /masjids
Body: {
  "nama": "Masjid Name",
  "alamat": "Address",
  "bandar": "City",
  "negeri": "State",
  "poskod": "12345",
  "no_telefon": "03-1234-5678",
  "emel": "email@masjid.com",
  "kapasiti_solat": 5000,
  "imam": "Imam Name",
  "tahun_ditubuhkan": 2005,
  "status": "active"
}
```

### Get Masjid

```bash
GET /masjids/{id}
```

### Update Masjid

```bash
PATCH /masjids/{id}
Body: { "nama": "New Name", "kapasiti_solat": 6000, ... }
```

### Delete Masjid

```bash
DELETE /masjids/{id}
```

### Get Programs

```bash
GET /masjids/{id}/programs[?per_page=15][&page=1]
```

### Get Members

```bash
GET /masjids/{id}/members[?per_page=15][&page=1]
```

---

## 💰 Finance Module

### Current Status

```bash
Finance API Phase B and Phase C endpoints are implemented under /finance/* (as of April 30, 2026).
All six reports endpoints return JSON data.
Baucar API added: GET /finance/baucar and GET /finance/baucar/{id}.
Digital signature upload/remove added: POST/DELETE /auth/profile/signature.
```

### Finance Endpoints (Phase B)

> **Note:** As of April 30, 2026 — Baucar API and digital signature endpoints have been added.

#### Accounts (`akaun`)

```bash
GET    /finance/akaun[?search=NAME][&status_aktif=1][&jenis=tunai][&per_page=15][&page=1]
POST   /finance/akaun
GET    /finance/akaun/{id}
PATCH  /finance/akaun/{id}
DELETE /finance/akaun/{id}
```

#### Income (`hasil`)

```bash
GET    /finance/hasil[?search=KEYWORD][&tarikh_mula=YYYY-MM-DD][&tarikh_tamat=YYYY-MM-DD][&id_akaun=1][&id_sumber_hasil=1][&per_page=15][&page=1]
POST   /finance/hasil
GET    /finance/hasil/{id}
PATCH  /finance/hasil/{id}
DELETE /finance/hasil/{id}
GET    /finance/hasil/{id}/receipt
```

#### Expenses (`belanja`)

```bash
GET    /finance/belanja[?search=KEYWORD][&status=draft|pending-pengerusi|approved][&tarikh_mula=YYYY-MM-DD][&tarikh_tamat=YYYY-MM-DD][&id_kategori_belanja=1][&per_page=15][&page=1]
POST   /finance/belanja
GET    /finance/belanja/{id}
PATCH  /finance/belanja/{id}
DELETE /finance/belanja/{id}
PATCH  /finance/belanja/{id}/approve          # Requires permission:finance.approve + profile signature image
```

> **Approval workflow:** Bendahari approves first (`status=pending-pengerusi`), then Pengerusi finalises (`status=approved`). Both approvers must have a digital signature image on their profile before approving.

#### Payment Vouchers (`baucar`)

Read-only view of belanja records formatted as official payment vouchers.

```bash
GET    /finance/baucar[?search=KEYWORD][&status=draft|pending-pengerusi|rejected|approved][&tarikh_mula=YYYY-MM-DD][&tarikh_tamat=YYYY-MM-DD][&id_kategori_belanja=1][&per_page=15][&page=1]
GET    /finance/baucar/{id}
GET    /finance/baucar/{id}/pdf               # Returns 501 with pdf_url pointing to web route (planned)
```

**Baucar response format:**

```json
{
  "id": 10,
  "baucar_no": "BV-2026-000010",
  "tarikh": "2026-03-01",
  "akaun": { "id": 1, "nama": "Tunai" },
  "kategori": { "id": 2, "nama": "UTILITI" },
  "penerima": "Tenaga Nasional Berhad",
  "catatan": "Bayaran elektrik",
  "amaun": 350.00,
  "status": "approved",
  "approval_step": 2,
  "is_locked": true,
  "bendahari": {
    "nama": "Ahmad Bendahari",
    "lulus_pada": "2026-03-02T10:00:00+08:00",
    "signature": "A1B2C3D4E5F6A1B2C3D4E5F6"
  },
  "pengerusi": {
    "nama": "Haji Pengerusi",
    "lulus_pada": "2026-03-03T14:30:00+08:00",
    "signature": "F6E5D4C3B2A1F6E5D4C3B2A1"
  },
  "links": {
    "show": "/api/finance/baucar/10",
    "pdf": "/baucar/10/pdf"
  }
}
```

**Status values:**

| Value               | Meaning                                      |
| ------------------- | -------------------------------------------- |
| `draft`             | New — awaiting Bendahari review              |
| `pending-pengerusi` | Bendahari approved — awaiting Pengerusi      |
| `rejected`          | Rejected — has rejection note                |
| `approved`          | Fully approved & locked by Pengerusi         |

#### Account Transfers (`pindahan-akaun`)

```bash
GET    /finance/pindahan-akaun[?akaun_id=1][&tarikh_mula=YYYY-MM-DD][&tarikh_tamat=YYYY-MM-DD][&per_page=15][&page=1]
POST   /finance/pindahan-akaun
GET    /finance/pindahan-akaun/{id}
PATCH  /finance/pindahan-akaun/{id}
DELETE /finance/pindahan-akaun/{id}
```

#### Finance Master Data

```bash
GET|POST|PATCH|DELETE /finance/sumber-hasil
PATCH                /finance/sumber-hasil/{id}/status
GET|POST|PATCH|DELETE /finance/kategori-belanja
PATCH                /finance/kategori-belanja/{id}/status
GET|POST|PATCH|DELETE /finance/tabung-khas
PATCH                /finance/tabung-khas/{id}/status
GET|POST|PATCH|DELETE /finance/program-masjid
PATCH                /finance/program-masjid/{id}/status
GET                  /finance/running-no
POST                 /finance/running-no/generate
PATCH                /finance/running-no/{idMasjid}/{prefix}/{tahun}/{bulan}
```

#### Finance Reports (Phase C - Implemented)

```bash
GET /finance/reports/buku-tunai[?akaun_id=1][&tarikh_mula=YYYY-MM-DD][&tarikh_tamat=YYYY-MM-DD][&baki_awal=0][&id_masjid=1]
GET /finance/reports/jumaat[?tahun=2026][&bulan=4][&jenis_paparan=ringkasan_bulanan|senarai_jumaat][&id_masjid=1]
GET /finance/reports/derma[?tarikh_dari=YYYY-MM-DD][&tarikh_hingga=YYYY-MM-DD][&jenis_paparan=ringkasan_sumber|ringkasan_bulan|senarai_transaksi][&id_masjid=1]
GET /finance/reports/belanja[?tarikh_dari=YYYY-MM-DD][&tarikh_hingga=YYYY-MM-DD][&jenis_paparan=ringkasan_kategori|ringkasan_bulan|senarai_transaksi][&kategori_id=1][&akaun_id=1][&status=all|draf|lulus][&id_masjid=1]
GET /finance/reports/penyata[?jenis_penyata=bulanan|tahunan][&tahun=2026][&bulan=4][&id_masjid=1]
GET /finance/reports/tabung[?tarikh_dari=YYYY-MM-DD][&tarikh_hingga=YYYY-MM-DD][&id_masjid=1]
```

> Note: For superadmin users, `id_masjid` is required on reports endpoints.

---

## 🔔 Notifications

### List Notifications

```bash
GET /notifications[?per_page=20][&page=1]
Returns: { data, pagination, unread_count }
```

### Get Unread

```bash
GET /notifications/unread
```

### Mark as Read

```bash
PATCH /notifications/{id}/read
```

### Mark All as Read

```bash
PATCH /notifications/read-all
```

### Delete Notification

```bash
DELETE /notifications/{id}
```

### Delete All

```bash
DELETE /notifications
```

### Get Preferences

```bash
GET /notifications/preferences
```

### Update Preferences

```bash
PATCH /notifications/preferences
Body: {
  "email_notifications": true,
  "push_notifications": true,
  "notification_types": ["finance", "masjid"]
}
```

---

## 📊 Pagination Query Parameters

| Parameter  | Default        | Example        |
| ---------- | -------------- | -------------- |
| `per_page` | Varies (15-20) | `?per_page=50` |
| `page`     | 1              | `?page=2`      |

**Response includes:**

```json
{
  "data": [...],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7
  }
}
```

---

## 🔍 Search & Filter Query Parameters

| Endpoint           | Available Filters                                                                   |
| ------------------ | ----------------------------------------------------------------------------------- |
| `/users`           | `search`, `aktif`, `role`                                                           |
| `/masjids`         | `search`, `status`, `negeri`                                                        |
| `/finance/hasil`   | `search`, `tarikh_mula`, `tarikh_tamat`, `id_akaun`, `id_sumber_hasil`             |
| `/finance/belanja` | `search`, `status` (draft\|pending-pengerusi\|approved), `tarikh_mula`, `tarikh_tamat`, `id_kategori_belanja` |
| `/finance/baucar`  | `search`, `status` (draft\|pending-pengerusi\|rejected\|approved), `tarikh_mula`, `tarikh_tamat`, `id_kategori_belanja` |

---

## ❌ Error Status Codes

| Code | Meaning                              |
| ---- | ------------------------------------ |
| 200  | Success (GET, PATCH)                 |
| 201  | Created (POST)                       |
| 400  | Bad Request                          |
| 401  | Unauthorized (invalid/missing token) |
| 403  | Forbidden (no permission)            |
| 404  | Not Found                            |
| 422  | Validation Error                     |
| 500  | Server Error                         |

---

## 🐛 Troubleshooting

### "Unauthenticated" (401)

- Missing `Authorization` header
- Token is invalid or expired
- Solution: Login again, get new token

### "Unauthorized" (403)

- User lacks required permission
- Solution: Admin assigns correct role

### Validation Error (422)

- Invalid request data
- Solution: Check error messages in response

### "Resource not found" (404)

- ID doesn't exist
- Solution: Verify ID is correct

---

## 🧪 Test with cURL

### Login & Save Token

```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","password":"pass123"}' | jq -r '.token')
```

### Use Saved Token

```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"
```

### List Users (with permission)

```bash
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📱 Response Format

### Single Resource

```json
{
    "id": 1,
    "name": "Name",
    "email": "email@example.com"
    // ... other fields
}
```

### List with Pagination

```json
{
  "data": [
    { "id": 1, ... },
    { "id": 2, ... }
  ],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7
  }
}
```

### Auth Response

```json
{
    "user": {
        /* UserResource */
    },
    "token": "1|abc...",
    "expires_in": "2026-04-15T10:30:00Z",
    "token_type": "Bearer"
}
```

### Error Response

```json
{
    "message": "Error message",
    "errors": {
        "field": ["Error detail"]
    }
}
```

---

## 🔐 Permission Matrix

| Endpoint                                    | Required Permission    | Access        |
| ------------------------------------------- | ---------------------- | ------------- |
| `/auth/*`                                   | None (register, login) | Public        |
| `/auth/me`, `/auth/profile`, `/auth/logout` | `auth:sanctum`         | Authenticated |
| `/auth/profile/signature` (POST/DELETE)     | `auth:sanctum`         | Authenticated |
| `/users`                                    | `users.manage`         | Admin         |
| `/masjids`                                  | `masjid.manage`        | Admin         |
| `/finance/belanja/{id}/approve`             | `finance.approve`      | Bendahari / Pengerusi |
| `/finance/baucar`                           | `belanja.view`         | Finance roles |
| `/finance/*`                                | Module-specific perms  | Finance roles |
| `/notifications`                            | `auth:sanctum`         | Authenticated |

---

## 💡 Tips & Tricks

1. **Check Health**

    ```bash
    curl http://localhost:8000/api/health
    ```

2. **Pretty Print JSON** (with jq)

    ```bash
    curl -s http://localhost:8000/api/users | jq .
    ```

3. **Extract Field** (with jq)

    ```bash
    curl -s http://localhost:8000/api/auth/me | jq '.email'
    ```

4. **Count Items** (with jq)

    ```bash
    curl -s http://localhost:8000/api/users | jq '.pagination.total'
    ```

5. **Export Token to Variable**

    ```bash
    TOKEN="your_token_here"
    ```

6. **Batch Requests** - Use Postman collection for multiple requests

---

## 📚 Full Documentation

- **API_DOCUMENTATION.md** - Complete reference
- **docs/FINANCE_API_IMPLEMENTATION_CHECKLIST.md** - Finance API build checklist for mobile
- **SETUP_AND_INTEGRATION_GUIDE.md** - Integration guide
- **POSTMAN_COLLECTION.json** - Interactive testing
- **API_IMPLEMENTATION_SUMMARY.md** - Project overview

---

## ⚡ Common Workflows

### Complete Authentication Flow

```
1. POST /auth/register → Create account
2. POST /auth/login → Get token
3. Store token in client
4. GET /auth/me → Verify you're logged in
5. PATCH /auth/profile → Update profile
6. POST /auth/refresh → Get new token (optional)
7. POST /auth/logout → Clean logout
```

### Admin User Management

```
1. POST /auth/login → Authenticate as admin
2. GET /users → List all users
3. POST /users → Create new user
4. GET /users/{id} → View user details
5. PATCH /users/{id} → Update user
6. POST /users/{id}/roles → Assign roles
```

### Notification Management

```
1. GET /notifications → List all notifications
2. GET /notifications/unread → Check unread
3. PATCH /notifications/{id}/read → Mark as read
4. PATCH /notifications/preferences → Configure
```

---

**Version**: 1.0.0  
**Last Updated**: April 29, 2026  
**Status**: Production Ready
