# RESTful API Documentation

## Base URL

```
http://localhost:8000/api
```

## Authentication

All protected endpoints require Bearer token authentication via Laravel Sanctum.

### Header

```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 1. Authentication Endpoints

### 1.1 Register User

**Endpoint:** `POST /auth/register`  
**Authentication:** No  
**Response Code:** 201

**Request:**

```json
{
    "name": "Ahmad Hassan",
    "email": "ahmad@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "peranan": "User"
}
```

**Success Response (201):**

```json
{
    "user": {
        "id": 1,
        "name": "Ahmad Hassan",
        "email": "ahmad@example.com",
        "peranan": "User",
        "aktif": true,
        "email_verified_at": null,
        "two_factor_enabled": false,
        "masjid_id": null,
        "roles": ["user"],
        "permissions": [],
        "created_at": "2026-04-14T10:30:00Z",
        "updated_at": "2026-04-14T10:30:00Z"
    },
    "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
    "expires_in": "2026-04-15T10:30:00Z",
    "token_type": "Bearer"
}
```

**Validation Error (422):**

```json
{
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

---

### 1.2 Login User

**Endpoint:** `POST /auth/login`  
**Authentication:** No  
**Response Code:** 200

**Request:**

```json
{
    "email": "ahmad@example.com",
    "password": "password123"
}
```

**Success Response (200):**

```json
{
    "user": {
        "id": 1,
        "name": "Ahmad Hassan",
        "email": "ahmad@example.com",
        "peranan": "User",
        "aktif": true,
        "email_verified_at": "2026-04-14T09:00:00Z",
        "two_factor_enabled": false,
        "masjid_id": 1,
        "masjid": {
            "id": 1,
            "nama": "Masjid Negara",
            "alamat": "Kuala Lumpur",
            "bandar": "KL",
            "negeri": "Wilayah Persekutuan",
            "poskod": "50050",
            "no_telefon": "03-1234-5678",
            "emel": "info@masjidnegara.com",
            "kapasiti_solat": 10000,
            "imam": "Imam Ahmad",
            "status": "active",
            "tahun_ditubuhkan": 1965,
            "koordinat_lat": "3.1547",
            "koordinat_long": "101.6964",
            "created_at": "2026-04-01T00:00:00Z",
            "updated_at": "2026-04-01T00:00:00Z"
        },
        "roles": ["officer"],
        "permissions": ["masjid.view", "notifications.manage"],
        "created_at": "2026-04-14T10:30:00Z",
        "updated_at": "2026-04-14T10:30:00Z"
    },
    "token": "2|xyz789abc456def012ghi345jkl678mno901pqr234stu567vw",
    "expires_in": "2026-04-15T10:30:00Z",
    "token_type": "Bearer"
}
```

**Auth Error (422):**

```json
{
    "message": "Validation failed",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

---

### 1.3 Get Current User Profile

**Endpoint:** `GET /auth/me`  
**Authentication:** Required  
**Response Code:** 200

**Success Response:**

```json
{
    "id": 1,
    "name": "Ahmad Hassan",
    "email": "ahmad@example.com",
    "peranan": "Officer",
    "aktif": true,
    "email_verified_at": "2026-04-14T09:00:00Z",
    "two_factor_enabled": false,
    "masjid_id": 1,
    "roles": ["officer"],
    "permissions": ["masjid.view", "notifications.manage"],
    "created_at": "2026-04-14T10:30:00Z",
    "updated_at": "2026-04-14T10:30:00Z"
}
```

---

### 1.4 Update Profile

**Endpoint:** `PATCH /auth/profile`  
**Authentication:** Required  
**Response Code:** 200

**Request:**

```json
{
    "name": "Ahmad Hassan Ibrahim",
    "email": "ahmad.hassan@example.com"
}
```

**Success Response:**

```json
{
    "id": 1,
    "name": "Ahmad Hassan Ibrahim",
    "email": "ahmad.hassan@example.com",
    "peranan": "Officer",
    "aktif": true,
    "email_verified_at": "2026-04-14T09:00:00Z",
    "two_factor_enabled": false,
    "masjid_id": 1,
    "roles": ["officer"],
    "permissions": ["masjid.view", "notifications.manage"],
    "created_at": "2026-04-14T10:30:00Z",
    "updated_at": "2026-04-14T11:45:00Z"
}
```

---

### 1.5 Change Password

**Endpoint:** `POST /auth/change-password`  
**Authentication:** Required  
**Response Code:** 200

**Request:**

```json
{
    "current_password": "oldpassword123",
    "new_password": "newpassword456",
    "new_password_confirmation": "newpassword456"
}
```

**Success Response:**

```json
{
    "message": "Password changed successfully"
}
```

---

### 1.6 Refresh Token

**Endpoint:** `POST /auth/refresh`  
**Authentication:** Required  
**Response Code:** 200

**Request:** None (uses current token)

**Success Response:**

```json
{
    "user": {
        "id": 1,
        "name": "Ahmad Hassan",
        "email": "ahmad@example.com",
        "peranan": "Officer",
        "aktif": true,
        "email_verified_at": "2026-04-14T09:00:00Z",
        "two_factor_enabled": false,
        "masjid_id": 1,
        "roles": ["officer"],
        "permissions": ["masjid.view", "notifications.manage"],
        "created_at": "2026-04-14T10:30:00Z",
        "updated_at": "2026-04-14T10:30:00Z"
    },
    "token": "3|new_token_here_xyz123...",
    "expires_in": "2026-04-15T11:45:00Z",
    "token_type": "Bearer"
}
```

---

### 1.7 Logout

**Endpoint:** `POST /auth/logout`  
**Authentication:** Required  
**Response Code:** 200

**Request:** None

**Success Response:**

```json
{
    "message": "Logout successful"
}
```

---

## 2. User Management Endpoints (Admin Only)

### 2.1 List Users

**Endpoint:** `GET /users`  
**Authentication:** Required  
**Permission:** users.manage  
**Response Code:** 200

**Query Parameters:**

- `search` - Search by name or email
- `aktif` - Filter by status (1 or 0)
- `role` - Filter by role name
- `per_page` - Items per page (default: 15)
- `page` - Page number

**Example Request:**

```
GET /users?search=Ahmad&aktif=1&per_page=10&page=1
```

**Success Response:**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Ahmad Hassan",
            "email": "ahmad@example.com",
            "peranan": "Officer",
            "aktif": true,
            "email_verified_at": "2026-04-14T09:00:00Z",
            "two_factor_enabled": false,
            "masjid_id": 1,
            "masjid": {
                "id": 1,
                "nama": "Masjid Negara",
                "alamat": "Kuala Lumpur",
                "bandar": "KL",
                "negeri": "Wilayah Persekutuan",
                "poskod": "50050",
                "no_telefon": "03-1234-5678",
                "emel": "info@masjidnegara.com",
                "kapasiti_solat": 10000,
                "imam": "Imam Ahmad",
                "status": "active",
                "tahun_ditubuhkan": 1965,
                "koordinat_lat": "3.1547",
                "koordinat_long": "101.6964",
                "created_at": "2026-04-01T00:00:00Z",
                "updated_at": "2026-04-01T00:00:00Z"
            },
            "roles": ["officer"],
            "permissions": ["masjid.view", "notifications.manage"],
            "created_at": "2026-04-14T10:30:00Z",
            "updated_at": "2026-04-14T10:30:00Z"
        }
    ],
    "pagination": {
        "total": 45,
        "per_page": 10,
        "current_page": 1,
        "last_page": 5,
        "from": 1,
        "to": 10
    }
}
```

---

### 2.2 Create User

**Endpoint:** `POST /users`  
**Authentication:** Required  
**Permission:** users.manage  
**Response Code:** 201

**Request:**

```json
{
    "name": "Fatimah Binti Ali",
    "email": "fatimah@example.com",
    "password": "securepass123",
    "peranan": "Officer",
    "id_masjid": 1,
    "roles": ["officer", "analyst"]
}
```

**Success Response (201):**

```json
{
    "id": 2,
    "name": "Fatimah Binti Ali",
    "email": "fatimah@example.com",
    "peranan": "Officer",
    "aktif": true,
    "email_verified_at": null,
    "two_factor_enabled": false,
    "masjid_id": 1,
    "masjid": null,
    "roles": ["officer", "analyst"],
    "permissions": [],
    "created_at": "2026-04-14T11:00:00Z",
    "updated_at": "2026-04-14T11:00:00Z"
}
```

---

### 2.3 Get User Details

**Endpoint:** `GET /users/{user}`  
**Authentication:** Required  
**Permission:** users.manage  
**Response Code:** 200

**Success Response:**

```json
{
  "id": 1,
  "name": "Ahmad Hassan",
  "email": "ahmad@example.com",
  "peranan": "Officer",
  "aktif": true,
  "email_verified_at": "2026-04-14T09:00:00Z",
  "two_factor_enabled": false,
  "masjid_id": 1,
  "masjid": {...},
  "roles": ["officer"],
  "permissions": ["masjid.view", "notifications.manage"],
  "created_at": "2026-04-14T10:30:00Z",
  "updated_at": "2026-04-14T10:30:00Z"
}
```

---

### 2.4 Update User

**Endpoint:** `PATCH /users/{user}`  
**Authentication:** Required  
**Permission:** users.manage  
**Response Code:** 200

**Request:**

```json
{
    "name": "Ahmad Hassan Ibrahim",
    "email": "ahmad.newemail@example.com",
    "peranan": "Senior Officer",
    "id_masjid": 2,
    "password": "newpassword456"
}
```

**Success Response:**

```json
{
    "id": 1,
    "name": "Ahmad Hassan Ibrahim",
    "email": "ahmad.newemail@example.com",
    "peranan": "Senior Officer",
    "aktif": true,
    "email_verified_at": "2026-04-14T09:00:00Z",
    "two_factor_enabled": false,
    "masjid_id": 2,
    "masjid": null,
    "roles": ["officer"],
    "permissions": ["masjid.view", "notifications.manage"],
    "created_at": "2026-04-14T10:30:00Z",
    "updated_at": "2026-04-14T12:00:00Z"
}
```

---

### 2.5 Delete User

**Endpoint:** `DELETE /users/{user}`  
**Authentication:** Required  
**Permission:** users.manage  
**Response Code:** 200

**Request:** None

**Success Response:**

```json
{
    "message": "User deleted successfully"
}
```

---

### 2.6 Toggle User Status

**Endpoint:** `PATCH /users/{user}/status`  
**Authentication:** Required  
**Permission:** users.manage  
**Response Code:** 200

**Request:** None

**Success Response:**

```json
{
    "id": 1,
    "name": "Ahmad Hassan",
    "email": "ahmad@example.com",
    "peranan": "Officer",
    "aktif": false,
    "email_verified_at": "2026-04-14T09:00:00Z",
    "two_factor_enabled": false,
    "masjid_id": 1,
    "roles": ["officer"],
    "permissions": ["masjid.view", "notifications.manage"],
    "created_at": "2026-04-14T10:30:00Z",
    "updated_at": "2026-04-14T12:05:00Z"
}
```

---

### 2.7 Assign Roles to User

**Endpoint:** `POST /users/{user}/roles`  
**Authentication:** Required  
**Permission:** users.manage  
**Response Code:** 200

**Request:**

```json
{
    "roles": ["officer", "analyst", "viewer"]
}
```

**Success Response:**

```json
{
    "id": 1,
    "name": "Ahmad Hassan",
    "email": "ahmad@example.com",
    "peranan": "Officer",
    "aktif": true,
    "email_verified_at": "2026-04-14T09:00:00Z",
    "two_factor_enabled": false,
    "masjid_id": 1,
    "roles": ["officer", "analyst", "viewer"],
    "permissions": [
        "masjid.view",
        "masjid.manage",
        "notifications.manage",
        "reports.view"
    ],
    "created_at": "2026-04-14T10:30:00Z",
    "updated_at": "2026-04-14T12:10:00Z"
}
```

---

### 2.8 Get User Permissions

**Endpoint:** `GET /users/{user}/permissions`  
**Authentication:** Required  
**Permission:** users.manage  
**Response Code:** 200

**Success Response:**

```json
{
    "permissions": [
        "masjid.view",
        "masjid.manage",
        "notifications.manage",
        "reports.view"
    ],
    "role_permissions": {
        "officer": ["masjid.view", "notifications.manage"],
        "analyst": ["reports.view"],
        "viewer": ["masjid.view"]
    }
}
```

---

## 3. Masjid Management Endpoints (Admin Only)

### 3.1 List Masjids

**Endpoint:** `GET /masjids`  
**Authentication:** Required  
**Permission:** masjid.manage  
**Response Code:** 200

**Query Parameters:**

- `search` - Search by name or location
- `status` - Filter by status (active, inactive, pending)
- `negeri` - Filter by state
- `per_page` - Items per page (default: 15)
- `page` - Page number

**Success Response:**

```json
{
    "data": [
        {
            "id": 1,
            "nama": "Masjid Negara",
            "alamat": "Jalan Masjid Negara, Kuala Lumpur",
            "bandar": "Kuala Lumpur",
            "negeri": "Wilayah Persekutuan",
            "poskod": "50050",
            "no_telefon": "03-1234-5678",
            "emel": "info@masjidnegara.com",
            "kapasiti_solat": 10000,
            "imam": "Imam Ahmad bin Hasan",
            "status": "active",
            "tahun_ditubuhkan": 1965,
            "koordinat_lat": "3.1547",
            "koordinat_long": "101.6964",
            "created_at": "2026-04-01T00:00:00Z",
            "updated_at": "2026-04-01T00:00:00Z"
        }
    ],
    "pagination": {
        "total": 42,
        "per_page": 15,
        "current_page": 1,
        "last_page": 3,
        "from": 1,
        "to": 15
    }
}
```

---

### 3.2 Create Masjid

**Endpoint:** `POST /masjids`  
**Authentication:** Required  
**Permission:** masjid.manage  
**Response Code:** 201

**Request:**

```json
{
    "nama": "Masjid Al-Azhar",
    "alamat": "Jalan Klang, Petaling Jaya",
    "bandar": "Petaling Jaya",
    "negeri": "Selangor",
    "poskod": "58200",
    "no_telefon": "03-7956-1234",
    "emel": "info@masajidal azhar.com",
    "kapasiti_solat": 5000,
    "imam": "Imam Muhammad Ali",
    "tahun_ditubuhkan": 2005,
    "koordinat_lat": "3.0573",
    "koordinat_long": "101.5243",
    "status": "active"
}
```

**Success Response (201):**

```json
{
    "id": 2,
    "nama": "Masjid Al-Azhar",
    "alamat": "Jalan Klang, Petaling Jaya",
    "bandar": "Petaling Jaya",
    "negeri": "Selangor",
    "poskod": "58200",
    "no_telefon": "03-7956-1234",
    "emel": "info@masajidal azhar.com",
    "kapasiti_solat": 5000,
    "imam": "Imam Muhammad Ali",
    "status": "active",
    "tahun_ditubuhkan": 2005,
    "koordinat_lat": "3.0573",
    "koordinat_long": "101.5243",
    "created_at": "2026-04-14T13:00:00Z",
    "updated_at": "2026-04-14T13:00:00Z"
}
```

---

### 3.3 Get Masjid Details

**Endpoint:** `GET /masjids/{masjid}`  
**Authentication:** Required  
**Permission:** masjid.manage  
**Response Code:** 200

**Success Response:**

```json
{
    "id": 1,
    "nama": "Masjid Negara",
    "alamat": "Jalan Masjid Negara, Kuala Lumpur",
    "bandar": "Kuala Lumpur",
    "negeri": "Wilayah Persekutuan",
    "poskod": "50050",
    "no_telefon": "03-1234-5678",
    "emel": "info@masjidnegara.com",
    "kapasiti_solat": 10000,
    "imam": "Imam Ahmad bin Hasan",
    "status": "active",
    "tahun_ditubuhkan": 1965,
    "koordinat_lat": "3.1547",
    "koordinat_long": "101.6964",
    "created_at": "2026-04-01T00:00:00Z",
    "updated_at": "2026-04-01T00:00:00Z"
}
```

---

### 3.4 Update Masjid

**Endpoint:** `PATCH /masjids/{masjid}`  
**Authentication:** Required  
**Permission:** masjid.manage  
**Response Code:** 200

**Request:**

```json
{
    "nama": "Masjid Negara (Updated)",
    "no_telefon": "03-9999-5678",
    "kapasiti_solat": 12000,
    "imam": "Imam Ahmad bin Ibrahim"
}
```

**Success Response:**

```json
{
    "id": 1,
    "nama": "Masjid Negara (Updated)",
    "alamat": "Jalan Masjid Negara, Kuala Lumpur",
    "bandar": "Kuala Lumpur",
    "negeri": "Wilayah Persekutuan",
    "poskod": "50050",
    "no_telefon": "03-9999-5678",
    "emel": "info@masjidnegara.com",
    "kapasiti_solat": 12000,
    "imam": "Imam Ahmad bin Ibrahim",
    "status": "active",
    "tahun_ditubuhkan": 1965,
    "koordinat_lat": "3.1547",
    "koordinat_long": "101.6964",
    "created_at": "2026-04-01T00:00:00Z",
    "updated_at": "2026-04-14T13:30:00Z"
}
```

---

### 3.5 Delete Masjid

**Endpoint:** `DELETE /masjids/{masjid}`  
**Authentication:** Required  
**Permission:** masjid.manage  
**Response Code:** 200

**Success Response:**

```json
{
    "message": "Masjid deleted successfully"
}
```

---

### 3.6 Get Masjid Programs

**Endpoint:** `GET /masjids/{masjid}/programs`  
**Authentication:** Required  
**Permission:** masjid.manage  
**Response Code:** 200

**Success Response:**

```json
{
    "data": [
        {
            "id": 1,
            "nama": "Kelas Quran Pagi",
            "deskripsi": "Pengajaran Quran untuk pemula",
            "hari": "Monday,Wednesday,Friday",
            "waktu_mula": "06:00",
            "waktu_tamat": "07:00",
            "bilangan_peserta": 25,
            "status": "active",
            "created_at": "2026-04-01T00:00:00Z"
        }
    ],
    "pagination": {
        "total": 5,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

---

### 3.7 Get Masjid Members

**Endpoint:** `GET /masjids/{masjid}/members`  
**Authentication:** Required  
**Permission:** masjid.manage  
**Response Code:** 200

**Success Response:**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Ahmad Hassan",
            "email": "ahmad@example.com",
            "peranan": "Officer",
            "aktif": true,
            "roles": ["officer"],
            "permissions": ["masjid.view", "notifications.manage"]
        }
    ],
    "pagination": {
        "total": 3,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

---

## 4. Notification Endpoints

### 4.1 List Notifications

**Endpoint:** `GET /notifications`  
**Authentication:** Required  
**Response Code:** 200

**Query Parameters:**

- `per_page` - Items per page (default: 20)
- `page` - Page number

**Success Response:**

```json
{
    "data": [
        {
            "id": "550e8400-e29b-41d4-a716-446655440000",
            "user_id": 1,
            "title": "Budget Allocation Approved",
            "message": "Your budget allocation request has been approved",
            "type": "finance",
            "channel": "database",
            "is_read": false,
            "read_at": null,
            "data": {
                "action_url": "/budgets/123",
                "action_label": "View Budget"
            },
            "created_at": "2026-04-14T14:30:00Z"
        },
        {
            "id": "550e8400-e29b-41d4-a716-446655440001",
            "user_id": 1,
            "title": "New Program Registration",
            "message": "A new program has been registered at Masjid Negara",
            "type": "masjid",
            "channel": "database",
            "is_read": true,
            "read_at": "2026-04-14T11:00:00Z",
            "data": {
                "program_id": 5,
                "masjid_id": 1
            },
            "created_at": "2026-04-14T10:00:00Z"
        }
    ],
    "pagination": {
        "total": 15,
        "per_page": 20,
        "current_page": 1,
        "last_page": 1
    },
    "unread_count": 3
}
```

---

### 4.2 Get Unread Notifications

**Endpoint:** `GET /notifications/unread`  
**Authentication:** Required  
**Response Code:** 200

**Success Response:**

```json
{
    "data": [
        {
            "id": "550e8400-e29b-41d4-a716-446655440000",
            "user_id": 1,
            "title": "Budget Allocation Approved",
            "message": "Your budget allocation request has been approved",
            "type": "finance",
            "channel": "database",
            "is_read": false,
            "read_at": null,
            "data": {
                "action_url": "/budgets/123",
                "action_label": "View Budget"
            },
            "created_at": "2026-04-14T14:30:00Z"
        }
    ],
    "pagination": {
        "total": 3,
        "per_page": 20,
        "current_page": 1,
        "last_page": 1
    }
}
```

---

### 4.3 Mark Notification as Read

**Endpoint:** `PATCH /notifications/{notification}/read`  
**Authentication:** Required  
**Response Code:** 200

**Request:** None

**Success Response:**

```json
{
    "message": "Notification marked as read"
}
```

---

### 4.4 Mark All as Read

**Endpoint:** `PATCH /notifications/read-all`  
**Authentication:** Required  
**Response Code:** 200

**Request:** None

**Success Response:**

```json
{
    "message": "All notifications marked as read"
}
```

---

### 4.5 Delete Notification

**Endpoint:** `DELETE /notifications/{notification}`  
**Authentication:** Required  
**Response Code:** 200

**Success Response:**

```json
{
    "message": "Notification deleted successfully"
}
```

---

### 4.6 Delete All Notifications

**Endpoint:** `DELETE /notifications`  
**Authentication:** Required  
**Response Code:** 200

**Request:** None

**Success Response:**

```json
{
    "message": "All notifications deleted successfully"
}
```

---

### 4.7 Get Notification Preferences

**Endpoint:** `GET /notifications/preferences`  
**Authentication:** Required  
**Response Code:** 200

**Success Response:**

```json
{
    "id": 1,
    "user_id": 1,
    "email_notifications": true,
    "sms_notifications": false,
    "push_notifications": true,
    "telegram_notifications": true,
    "fcm_token": "eIzSZe...",
    "telegram_chat_id": "123456789",
    "notification_types": ["finance", "masjid", "user", "system"],
    "created_at": "2026-04-01T00:00:00Z",
    "updated_at": "2026-04-14T15:00:00Z"
}
```

---

### 4.8 Update Notification Preferences

**Endpoint:** `PATCH /notifications/preferences`  
**Authentication:** Required  
**Response Code:** 200

**Request:**

```json
{
    "email_notifications": true,
    "sms_notifications": true,
    "push_notifications": true,
    "telegram_notifications": false,
    "fcm_token": "newtoken123...",
    "telegram_chat_id": "987654321",
    "notification_types": ["finance", "masjid"]
}
```

**Success Response:**

```json
{
    "message": "Preferences updated successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "email_notifications": true,
        "sms_notifications": true,
        "push_notifications": true,
        "telegram_notifications": false,
        "fcm_token": "newtoken123...",
        "telegram_chat_id": "987654321",
        "notification_types": ["finance", "masjid"],
        "created_at": "2026-04-01T00:00:00Z",
        "updated_at": "2026-04-14T15:15:00Z"
    }
}
```

---

## Error Responses

### Authentication Error (401)

```json
{
    "success": false,
    "message": "Unauthenticated",
    "error": "Please provide valid authentication credentials"
}
```

### Authorization Error (403)

```json
{
    "success": false,
    "message": "Unauthorized",
    "error": "You are not authorized to perform this action"
}
```

### Validation Error (422)

```json
{
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Not Found (404)

```json
{
    "success": false,
    "message": "Resource not found",
    "error": "The requested resource does not exist"
}
```

### Server Error (500)

```json
{
    "success": false,
    "message": "An error occurred",
    "error": "Internal server error"
}
```

---

## Rate Limiting (Optional)

Recommended rate limits per IP:

- **Authentication endpoints**: 5 requests per minute
- **General endpoints**: 60 requests per minute
- **Notification endpoints**: 100 requests per minute

---

## CORS Configuration

Allowed origins (configure in `/config/cors.php`):

- `http://localhost:3000`
- `http://localhost:8000`
- Production domain(s)

---

## Version Information

**API Version**: 1.0.0  
**Last Updated**: April 14, 2026  
**Base Framework**: Laravel 11  
**Authentication**: Laravel Sanctum

---

## Testing with cURL

### Register

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### Get Current User (with token)

```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### List Users

```bash
curl -X GET "http://localhost:8000/api/users?per_page=15&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

---

## WebHooks (Future Implementation)

Potential webhook events:

- `user.created`
- `user.updated`
- `notification.sent`
- `masjid.updated`
- `budget.approved`
