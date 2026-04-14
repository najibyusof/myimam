# Enhanced Auth Flow

## Features

- Login / Logout (Breeze)
- Registration (Breeze)
- Forgot Password / Reset Password (Breeze)
- Email Verification (enabled via `MustVerifyEmail` on `User`)
- Two-Factor Authentication (TOTP, Google Authenticator compatible)

## Login Flow

1. User submits email + password.
2. Credentials validated and rate-limited (`LoginRequest`).
3. If hash algorithm/cost changed, password is rehashed automatically.
4. If 2FA is enabled:
    - user is temporarily signed out,
    - pending 2FA user ID is stored in session,
    - user is redirected to `/two-factor-challenge`.
5. If 2FA is not enabled:
    - session is regenerated,
    - user is redirected to intended dashboard.

## 2FA Setup Flow

1. Authenticated + verified user opens `/two-factor`.
2. App generates secret and OTP Auth URL.
3. User adds secret to authenticator app.
4. User confirms with 6-digit code.
5. App stores:
    - `two_factor_secret` (encrypted)
    - `two_factor_recovery_codes` (encrypted array)
    - `two_factor_confirmed_at`

## 2FA Challenge Flow

1. During login, if 2FA enabled, redirect to challenge.
2. User submits TOTP code or recovery code.
3. If valid, login is finalized and session regenerated.
4. Recovery code is one-time use and removed after use.

## Security Notes

- Session ID regeneration on login and after 2FA challenge.
- Session invalidation and CSRF token regeneration on logout.
- Session encryption enabled by default.
- Password hashing cast (`hashed`) + rehash-on-login.
- Email verification enforced by `verified` middleware.

## Key Routes

- `GET /login`, `POST /login`
- `POST /logout`
- `GET /register`, `POST /register`
- `GET /forgot-password`, `POST /forgot-password`
- `GET /reset-password/{token}`, `POST /reset-password`
- `GET /verify-email`, `GET /verify-email/{id}/{hash}`
- `GET /two-factor` (settings)
- `POST /two-factor` (enable)
- `DELETE /two-factor` (disable)
- `GET /two-factor-challenge` (challenge)
- `POST /two-factor-challenge` (verify)
