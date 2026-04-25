# Auth Module — Changes Summary

## Form Requests

### `RegisterStepTwoRequest`

- **Added** (person): `employment_type`, `company_name`, `preferred_work_location` (`in:onsite,remote,hybrid`), `expected_salary_min`, `expected_salary_max` (with `gte` cross-field rule), `skills[]` (exists:skills,id), `languages[]` (exists:languages,id)
- All new fields are `nullable` to keep step optional

### `RegisterStepThreeRequest`

- **Added** (person): `interests[]` (exists:interests,id)
- Increased `about` max from 500 → 2000 characters

---

## Services

### `AuthService`

#### `registerStepTwo`

- Changed `updatePersonStepTwo` signature from `(PersonProfile $profile, ...)` → `(User $user, ...)` to allow pivot sync

#### `updatePersonStepTwo` (person step 2)

- **Added** all missing field updates: `employment_type`, `company_name`, `preferred_work_location`, `expected_salary_min`, `expected_salary_max`
- **Added** `skills()->sync()` and `languages()->sync()` using `array_key_exists` check (only syncs if key present in request)

#### `updatePersonStepThree` (person step 3)

- **Fixed** CV upload: now creates a `Document` record (`type=cv`, `is_primary=true`), replaces any existing primary CV
- **Fixed** certificate upload: now creates `Document` records (`type=certificate`, `is_primary=false`) for each file
- **Added** `interests()->sync()` with `array_key_exists` guard
- Previously files were stored to disk but **never saved to the database**

#### `loadUserProfiles`

- **Added** `skills`, `languages`, `interests`, `documents` to `loadMissing()` so all related data is included in every response

---

## Resources

### `UserResource`

- **Added** `profile_photo` URL generation via `Storage::url()`
- **Added** `skills`, `languages`, `interests` (person only) as `[id, name]` maps
- **Added** `documents` (person only) via `DocumentResource::collection()`
- Replaced `null` fallback on profile with `$this->when()` for cleaner conditional output

### `PersonProfileResource`

- **Added** missing fields: `employment_type`, `company_name`
- Fixed field ordering to match DB schema

### `DocumentResource` *(new)*

- Returns: `id`, `type`, `title`, `file_name`, `file_size`, `mime_type`, `url` (via `Storage::url()`), `is_primary`

---

## Mail

### `SendOtp`

- **Fixed** typo in view path: `'viwes.mail.auth.send-otp'` → `'mail.auth.send-otp'`
- Renamed `resources/views/mail/Auth/` → `resources/views/mail/auth/` (lowercase for Linux case-sensitivity)

---

## Routes (`routes/api.php`)

All 11 routes already in place — no changes needed:

| Method | URI | Middleware |
|--------|-----|------------|
| POST | `/api/auth/register/step-1` | — |
| POST | `/api/auth/register/step-2` | `auth:sanctum` |
| POST | `/api/auth/register/step-3` | `auth:sanctum` |
| POST | `/api/auth/login` | — |
| GET  | `/api/auth/me` | `auth:sanctum` |
| POST | `/api/auth/logout` | `auth:sanctum` |
| POST | `/api/auth/forgot-password` | — |
| POST | `/api/auth/verify-reset-otp` | — |
| POST | `/api/auth/resend-reset-otp` | — |
| POST | `/api/auth/reset-password` | — |
| POST | `/api/auth/change-password` | `auth:sanctum` |

---

## Postman Collection

Added: `postman/Job-Nest-Auth.postman_collection.json`

- **Auth** folder: Register Step 1 (person + company), Step 2, Step 3, Login, Me, Logout
- **Password** folder: Forgot Password, Verify OTP, Resend OTP, Reset Password, Change Password
- Login and Register Step 1 auto-save token to `{{token}}` collection variable
- All authenticated requests use `Bearer {{token}}`
- Set `{{base_url}}` to your server (e.g. `http://127.0.0.1:8000`)
