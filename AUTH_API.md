# JobNest Auth API

## Overview

- Base prefix: `/api/auth`
- Auth mechanism: Sanctum bearer tokens
- Registration is still step-based: `step-1`, `step-2`, `step-3`
- OTP is used only for forgot/reset password
- `logout` revokes the current token only
- `logout-all` revokes every active token for the authenticated user
- Session identifiers returned by the API are opaque IDs, not raw database token IDs
- Google login accepts a Google ID token from mobile/frontend, verifies it on the backend, then returns a Sanctum token

## Standard Response Shapes

Successful auth responses that issue a token return:

```json
{
  "message": "Logged in successfully.",
  "token": "1|plain-text-token",
  "access_token": "1|plain-text-token",
  "refresh_token": "opaque-refresh-token",
  "token_type": "Bearer",
  "expires_at": "2026-04-18T19:55:00+00:00",
  "access_token_expires_at": "2026-04-18T19:55:00+00:00",
  "refresh_token_expires_at": "2026-05-18T19:40:00+00:00",
  "current_token_id": "opaque-session-id",
  "current_token": {
    "id": "opaque-session-id",
    "name": "login:flutter-android",
    "current": true,
    "abilities": ["*"],
    "last_used_at": null,
    "created_at": "2026-04-17T14:30:00+00:00",
    "expires_at": null
  },
  "user": {}
}
```

Validation failures return HTTP `422`:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

## Endpoint Index

| Method | Route | Auth | Purpose |
| --- | --- | --- | --- |
| POST | `/api/auth/register/step-1` | No | Create account and issue token |
| POST | `/api/auth/register/step-2` | Yes | Continue person/company onboarding |
| POST | `/api/auth/register/step-3` | Yes | Finish onboarding and upload files |
| POST | `/api/auth/login` | No | Email/password login |
| POST | `/api/auth/google/login` | No | Google ID-token login |
| POST | `/api/auth/refresh-token` | No | Rotate refresh token and issue a new token pair |
| POST | `/api/auth/forgot-password` | No | Send reset OTP |
| POST | `/api/auth/verify-reset-otp` | No | Verify reset OTP |
| POST | `/api/auth/resend-reset-otp` | No | Resend reset OTP |
| POST | `/api/auth/reset-password` | No | Reset password with verified OTP |
| GET | `/api/auth/email/verify/{id}/{hash}` | No | Verify email from signed link |
| GET | `/api/auth/me` | Yes | Fetch authenticated user |
| POST | `/api/auth/email/verification/send` | Yes | Send verification email |
| POST | `/api/auth/email/verification/resend` | Yes | Resend verification email |
| GET | `/api/auth/email/verification-status` | Yes | Check email verification status |
| POST | `/api/auth/change-password` | Yes | Change password |
| POST | `/api/auth/logout` | Yes | Logout current device |
| POST | `/api/auth/logout-all` | Yes | Logout all devices |
| GET | `/api/auth/sessions` | Yes | List active sessions/tokens |
| DELETE | `/api/auth/sessions/{sessionId}` | Yes | Revoke one session/token |

## Registration Flow

### POST `/api/auth/register/step-1`

Auth required: `No`

Person payload:

```json
{
  "account_type": "person",
  "name": "Ali Hassan",
  "email": "ali@example.com",
  "phone": "01012345678",
  "password": "password123",
  "password_confirmation": "password123",
  "university": "Cairo University",
  "major": "Computer Science",
  "device_name": "flutter-android"
}
```

Company payload:

```json
{
  "account_type": "company",
  "name": "Tech Corp HR",
  "email": "hr@techcorp.com",
  "phone": "01098765432",
  "password": "password123",
  "password_confirmation": "password123",
  "company_name": "Tech Corp",
  "website": "https://techcorp.com",
  "company_size": "51-200",
  "industry": "Technology",
  "location": "Cairo"
}
```

Response notes:

- Returns `201`
- Returns a Sanctum token
- Returns `current_step: 1`
- Existing registration behavior remains unchanged; `device_name` is optional

Validation highlights:

- `account_type` must be `person` or `company`
- `email` and `phone` must be unique
- `password` must be confirmed
- `university` and `major` are required for person accounts
- `company_name` is required for company accounts

### POST `/api/auth/register/step-2`

Auth required: `Yes`

Person payload:

```json
{
  "employment_status": "employed",
  "employment_type": "full_time",
  "current_job_title": "Backend Developer",
  "company_name": "Acme",
  "preferred_work_location": "remote",
  "expected_salary_min": 3000,
  "expected_salary_max": 7000,
  "linkedin_url": "https://linkedin.com/in/ali",
  "portfolio_url": "https://ali.dev",
  "skills": [1, 2],
  "languages": [1]
}
```

Company payload:

```json
{
  "website": "https://techcorp.com",
  "company_size": "201-500",
  "industry": "Software",
  "location": "Cairo",
  "about": "We build hiring products."
}
```

Response notes:

- Returns `200`
- Returns updated `user`
- Returns `current_step: 2`

Validation highlights:

- `preferred_work_location` must be `onsite`, `remote`, or `hybrid`
- `expected_salary_max` must be greater than or equal to `expected_salary_min`
- `skills.*` and `languages.*` must exist

### POST `/api/auth/register/step-3`

Auth required: `Yes`

Person payload: multipart form-data

- `profile_photo`: image
- `cv`: `pdf|doc|docx`
- `certificates[]`: files
- `about`: string
- `interests[]`: interest IDs

Company payload: multipart form-data

- `logo`: image
- `about`: string

Response notes:

- Returns `200`
- Returns updated `user`
- Returns `current_step: 3`

Validation highlights:

- `cv` max size `5 MB`
- `profile_photo` and `logo` max size `2 MB`
- `interests.*` must exist

## Login Flow

### POST `/api/auth/login`

Auth required: `No`

Payload:

```json
{
  "email": "ali@example.com",
  "password": "password123",
  "device_name": "iphone-15"
}
```

Response notes:

- Returns token pair (`access_token`, `refresh_token`) with expiration metadata
- Keeps backward compatibility via `token` alias for `access_token`
- Token names are contextual, for example `login:iphone-15`

Validation highlights:

- Invalid credentials return `422` on `email`

### POST `/api/auth/google/login`

Auth required: `No`

Payload:

```json
{
  "id_token": "google-id-token-from-mobile-or-web",
  "account_type": "person",
  "company_name": "Tech Corp",
  "device_name": "flutter-android"
}
```

Behavior:

- The backend verifies the Google ID token with Google
- Existing users are matched by `google_id`, then by email
- If a matching email exists without `google_id`, the account is linked
- New users are created without sending welcome or verification messages
- New Google users default to `account_type: person` if `account_type` is omitted
- New Google users can continue step `2` and step `3` later without blocking current flows
- For new company accounts, `company_name` is optional; if omitted, the user name is used as the initial company profile name

Response notes:

- Returns the same token payload as regular login
- Adds `is_new_user`

Validation highlights:

- Invalid or expired Google ID tokens return `422` on `id_token`
- If the Google account conflicts with another linked account, the request returns `422`

## Password Reset Flow

### POST `/api/auth/forgot-password`

Auth required: `No`

Payload:

```json
{
  "email_or_phone": "ali@example.com",
  "method": "email"
}
```

Response:

```json
{
  "message": "OTP sent successfully."
}
```

Validation highlights:

- `method` must be `email` or `phone`
- `email_or_phone` must match the selected method

### POST `/api/auth/verify-reset-otp`

Auth required: `No`

Payload:

```json
{
  "email_or_phone": "ali@example.com",
  "otp": "123456"
}
```

Response:

```json
{
  "message": "OTP verified successfully."
}
```

Validation highlights:

- `otp` must be exactly 6 digits
- Invalid or expired OTP returns `422`

### POST `/api/auth/resend-reset-otp`

Auth required: `No`

Payload:

```json
{
  "email_or_phone": "ali@example.com",
  "method": "email"
}
```

Response:

```json
{
  "message": "OTP resent successfully."
}
```

### POST `/api/auth/reset-password`

Auth required: `No`

Payload:

```json
{
  "email_or_phone": "ali@example.com",
  "otp": "123456",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

Response:

```json
{
  "message": "Password reset successfully."
}
```

Validation highlights:

- `otp` must be verified and not expired
- `password` must be confirmed and at least 8 characters

## Authenticated User and Password

### GET `/api/auth/me`

Auth required: `Yes`

Response:

```json
{
  "message": "Authenticated user fetched successfully.",
  "user": {
    "email_verified": true,
    "email_verified_at": "2026-04-18T19:40:00+00:00"
  }
}
```

### POST `/api/auth/change-password`

Auth required: `Yes`

Payload:

```json
{
  "old_password": "password123",
  "password": "newpassword456",
  "password_confirmation": "newpassword456"
}
```

Response:

```json
{
  "message": "Password changed successfully."
}
```

## Logout and Session Management

### POST `/api/auth/logout`

Auth required: `Yes`

Behavior:

- Revokes only the current Sanctum token
- Other active sessions remain valid

Response:

```json
{
  "message": "Logged out successfully."
}
```

### POST `/api/auth/logout-all`

Auth required: `Yes`

Behavior:

- Revokes every active Sanctum token for the authenticated user, including the current one

Response:

```json
{
  "message": "Logged out from all devices successfully.",
  "revoked_tokens_count": 3,
  "revoked_refresh_tokens_count": 3
}
```

### GET `/api/auth/sessions`

Auth required: `Yes`

Response:

```json
{
  "message": "Active sessions fetched successfully.",
  "current_token_id": "opaque-session-id",
  "sessions": [
    {
      "id": "opaque-session-id",
      "name": "login:flutter-android",
      "current": true,
      "abilities": ["*"],
      "last_used_at": null,
      "created_at": "2026-04-17T14:30:00+00:00",
      "expires_at": null
    }
  ]
}
```

### DELETE `/api/auth/sessions/{sessionId}`

Auth required: `Yes`

Behavior:

- Revokes one session belonging to the authenticated user
- `sessionId` must be one of the opaque IDs returned by `GET /api/auth/sessions`

Response:

```json
{
  "message": "Session revoked successfully."
}
```

Validation highlights:

- Unknown or malformed `sessionId` returns `422` on `session_id`

## Email Verification Flow

### POST `/api/auth/email/verification/send`

Auth required: `Yes`

Behavior:

- Sends verification email when account is unverified
- Returns success if already verified (idempotent)

### POST `/api/auth/email/verification/resend`

Auth required: `Yes`

Behavior:

- Same behavior as send endpoint
- Rate limited to reduce abuse

### GET `/api/auth/email/verify/{id}/{hash}`

Auth required: `No`

Behavior:

- Verifies email through a temporary signed URL
- Invalid/expired signatures return `422`
- Re-verification is safe and idempotent

Success response:

```json
{
  "message": "Email verified successfully.",
  "email_verified": true,
  "email_verified_at": "2026-04-18T19:40:00+00:00"
}
```

### GET `/api/auth/email/verification-status`

Auth required: `Yes`

Response:

```json
{
  "message": "Verification status fetched successfully.",
  "email_verified": false,
  "email_verified_at": null
}
```

## Refresh Token Flow

### POST `/api/auth/refresh-token`

Auth required: `No`

Payload:

```json
{
  "refresh_token": "opaque-refresh-token",
  "device_name": "iphone-15"
}
```

Behavior:

- Validates refresh token hash against stored DB hash
- Rotates refresh token on every successful refresh
- Revokes prior access token tied to the refreshed session
- Rejects replayed (already rotated) refresh tokens

Success response:

```json
{
  "message": "Token refreshed successfully.",
  "access_token": "2|new-access-token",
  "refresh_token": "new-opaque-refresh-token",
  "token_type": "Bearer",
  "access_token_expires_at": "2026-04-18T20:00:00+00:00",
  "refresh_token_expires_at": "2026-05-18T19:45:00+00:00"
}
```
