# IMAM API - Setup & Integration Guide

## Quick Start

### 1. Prerequisites

- PHP 8.1+
- Laravel 11
- Laravel Sanctum (for API tokens)
- Spatie Permission (for roles & permissions)

### 2. Initialize Sanctum

If Sanctum is not yet set up, publish its migration:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Ensure `api_tokens` table exists in your database.

### 3. Environment Setup

Add to your `.env` file:

```env
API_PREFIX=api
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000
SESSION_DOMAIN=.localhost
```

### 4. Run Migrations & Seed

```bash
php artisan migrate
php artisan db:seed --class=RoleAndPermissionSeeder
```

---

## API Authentication Flow

### Step 1: Register a New User

**Endpoint:** `POST /api/auth/register`

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ahmad Hassan",
    "email": "ahmad@example.com",
    "password": "securepass123",
    "password_confirmation": "securepass123",
    "peranan": "User"
  }'
```

**Response:**

```json
{
    "user": {
        "id": 1,
        "name": "Ahmad Hassan",
        "email": "ahmad@example.com",
        "roles": ["user"],
        "permissions": []
    },
    "token": "1|abc123def456ghi789jkl012mno345pqr",
    "expires_in": "2026-04-15T10:30:00Z",
    "token_type": "Bearer"
}
```

### Step 2: Login

**Endpoint:** `POST /api/auth/login`

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "ahmad@example.com",
    "password": "securepass123"
  }'
```

### Step 3: Use Token for Authenticated Requests

Store the token and include in Authorization header:

```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

### Step 4: Refresh Token (Before Expiry)

```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

### Step 5: Logout

```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Postman Setup

### 1. Import Collection

1. Open Postman
2. Click **Import**
3. Select `POSTMAN_COLLECTION.json` from this project
4. Click **Import**

### 2. Configure Variables

In Postman:

1. Click on the collection **IMAM API**
2. Go to **Variables** tab
3. Set:
    - `base_url`: `http://localhost:8000`
    - `token`: (automatically populated after login request)

### 3. Use Pre-Scripts

Add a test script to the Login request to automatically capture the token:

```javascript
var jsonData = pm.response.json();
pm.environment.set("token", jsonData.token);
```

---

## Permission-Based Access

### User Management (Requires `users.manage`)

Only users with `users.manage` permission can:

- List all users
- Create new users
- Update users
- Delete users
- Assign/revoke roles

**Example:**

```bash
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

**Response if unauthorized (403):**

```json
{
    "message": "Unauthorized",
    "error": "User does not have permission to manage users"
}
```

### Masjid Management (Requires `masjid.manage`)

Similar restrictions apply to masjid operations.

### Notifications (All Authenticated Users)

All authenticated users can view and manage their own notifications.

---

## API Response Format

All responses follow this structure:

### Success Responses

**Single Resource (200):**

```json
{
  "id": 1,
  "name": "Ahmad Hassan",
  "email": "ahmad@example.com",
  ...
}
```

**Multiple Resources (200):**

```json
{
  "data": [{...}, {...}],
  "pagination": {
    "total": 45,
    "per_page": 15,
    "current_page": 1,
    "last_page": 3
  }
}
```

**Creation Success (201):**

```json
{
  "id": 1,
  "name": "Ahmad Hassan",
  ...
}
```

### Error Responses

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

**Authentication Error (401):**

```json
{
    "message": "Unauthenticated",
    "error": "Please provide valid authentication credentials"
}
```

**Authorization Error (403):**

```json
{
    "message": "Unauthorized",
    "error": "You are not authorized to perform this action"
}
```

**Not Found (404):**

```json
{
    "message": "Resource not found",
    "error": "The requested resource does not exist"
}
```

---

## Search & Filtering

### Users Endpoint

```bash
# Search by name
GET /api/users?search=Ahmad

# Filter by status
GET /api/users?aktif=1

# Filter by role
GET /api/users?role=officer

# Pagination
GET /api/users?per_page=10&page=2

# Combined
GET /api/users?search=Ahmad&aktif=1&role=officer&per_page=20&page=1
```

### Masjids Endpoint

```bash
# Search by name/location
GET /api/masjids?search=Masjid

# Filter by status
GET /api/masjids?status=active

# Filter by state
GET /api/masjids?negeri=Selangor

# Pagination
GET /api/masjids?per_page=10&page=1

# Combined
GET /api/masjids?search=Azhar&status=active&negeri=Selangor&per_page=15
```

---

## Common Issues & Solutions

### Issue: Validation Error on Registration

**Problem:** `The password must be at least 8 characters`

**Solution:** Ensure password is at least 8 characters and matches confirmation

```json
{
    "password": "securepass123",
    "password_confirmation": "securepass123"
}
```

---

### Issue: Login Fails with Inactive Account

**Problem:** User is registered but can't login

**Check:** Verify user `aktif` status is `true`

```bash
# Admin can toggle with:
PATCH /api/users/{user}/status
```

---

### Issue: 403 Unauthorized on User Operations

**Problem:** Endpoint returns 403 Forbidden

**Cause:** User doesn't have required permission

**Solution:**

1. Admin assigns role with permission:

```bash
curl -X POST http://localhost:8000/api/users/{user}/roles \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"roles": ["officer", "admin"]}'
```

2. Ensure role has required permission via `RoleAndPermissionSeeder`

---

### Issue: Token Expired

**Problem:** `401 Unauthenticated` after some time

**Solution:** Refresh token before expiry:

```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer {old_token}"
```

This returns a new token to use.

---

### Issue: CORS Error

**Problem:** Browser shows CORS error

**Solution:** Check `config/cors.php`:

```php
'allowed_origins' => ['http://localhost:3000', 'http://localhost:8000'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => ['Authorization'],
'allowed_credentials' => true,
```

---

## Pagination Best Practices

### Default Pagination

- Users endpoint: 15 per page
- Masjids endpoint: 15 per page
- Notifications endpoint: 20 per page

### Retrieve Full Metadata

All paginated responses include:

```json
{
  "data": [...],
  "pagination": {
    "total": 45,           // Total records
    "per_page": 15,        // Items per page
    "current_page": 2,     // Current page
    "last_page": 3         // Last page number
  }
}
```

### Calculate Total Pages

```javascript
const totalPages = Math.ceil(total / perPage);
```

---

## Notification Management

### Check Unread Notifications

```bash
curl -X GET http://localhost:8000/api/notifications/unread \
  -H "Authorization: Bearer {token}"
```

### Mark as Read

```bash
curl -X PATCH http://localhost:8000/api/notifications/{id}/read \
  -H "Authorization: Bearer {token}"
```

### Update Preferences

```bash
curl -X PATCH http://localhost:8000/api/notifications/preferences \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "email_notifications": true,
    "push_notifications": true,
    "notification_types": ["finance", "masjid"]
  }'
```

---

## Testing with cURL Scripts

### Create Test Script (test-api.sh)

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api"
EMAIL="test$(date +%s)@example.com"
PASSWORD="testpass123"

echo "1. Testing Registration..."
RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d "{
    \"name\": \"Test User\",
    \"email\": \"$EMAIL\",
    \"password\": \"$PASSWORD\",
    \"password_confirmation\": \"$PASSWORD\",
    \"peranan\": \"User\"
  }")

TOKEN=$(echo $RESPONSE | jq -r '.token')
echo "Token: $TOKEN"

echo -e "\n2. Testing Get User Profile..."
curl -s -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $TOKEN" | jq .

echo -e "\n3. Testing List Users (requires permission)..."
curl -s -X GET "$BASE_URL/users" \
  -H "Authorization: Bearer $TOKEN" | jq .

echo -e "\n4. Testing Logout..."
curl -s -X POST "$BASE_URL/auth/logout" \
  -H "Authorization: Bearer $TOKEN" | jq .
```

Run with:

```bash
chmod +x test-api.sh
./test-api.sh
```

---

## Database Relationships

```
Users (1) ---> (Many) Notifications
       (1) ---> (1) Masjid
       (Many) ---> Many (via user_roles) Roles

Roles (Many) ---> Many (via role_permissions) Permissions

Notifications -> NotificationPreferences
```

---

## API Versioning

Currently **v1.0.0**

For future versioning, use URL prefix:

```
/api/v1/users
/api/v2/users
```

---

## Rate Limiting (Future)

Recommended implementation:

```php
Route::middleware('throttle:60,1')->group(function () {
    // 60 requests per 1 minute
});
```

---

## Documentation Files

- **API_DOCUMENTATION.md** - Full endpoint reference with examples
- **POSTMAN_COLLECTION.json** - Ready-to-import Postman collection
- **SETUP_AND_INTEGRATION_GUIDE.md** - This file

---

## Support & Troubleshooting

### Check API Health

```bash
curl http://localhost:8000/api/health
```

Expected response:

```json
{
    "status": "ok",
    "timestamp": "2026-04-14T15:30:00Z",
    "version": "1.0.0"
}
```

### Enable API Debugging

In `.env`:

```env
APP_DEBUG=true
```

### Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

---

## Next Steps

1. ✅ API infrastructure created
2. ⬜ Implement rate limiting
3. ⬜ Add API versioning
4. ⬜ Create webhook system
5. ⬜ Add advanced filtering/sorting
6. ⬜ Implement bulk operations
7. ⬜ Create comprehensive test suite
8. ⬜ Deploy API documentation (Swagger/OpenAPI)
