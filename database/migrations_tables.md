# DB Diagram (dbdiagram.io)

Below are the tables extracted from the project's migrations in dbdiagram DSL. Paste this into <https://dbdiagram.io> and adjust types/indexes if you need exact SQL types.

Table users {
  id bigint [pk, increment]
  name varchar
  email varchar [unique]
  google_id varchar [unique, null]
  phone varchar(30) [unique, null]
  account_type enum('person','company') [default: 'person']
  profile_photo varchar [null]
  status enum('active','inactive','suspended') [default: 'active']
  email_verified_at timestamp [null]
  password varchar
  remember_token varchar [null]
  created_at timestamp
  updated_at timestamp
}

Table password_reset_tokens {
  email varchar [pk]
  token varchar
  created_at timestamp [null]
}

Table sessions {
  id varchar [pk]
  user_id bigint [ref: > users.id, null]
  ip_address varchar(45) [null]
  user_agent text [null]
  payload longtext
  last_activity int
}

Table admins {
  id bigint [pk, increment]
  name varchar
  email varchar [unique]
  phone varchar [unique, null]
  password varchar
  profile_photo varchar [null]
  status enum('active','inactive') [default: 'active']
  last_login_at timestamp [null]
  remember_token varchar [null]
  created_at timestamp
  updated_at timestamp
}

Table jobs {
  id bigint [pk, increment]
  queue varchar [index]
  payload longtext
  attempts tinyint unsigned
  reserved_at int unsigned [null]
  available_at int unsigned
  created_at int unsigned
}

Table job_batches {
  id varchar [pk]
  name varchar
  total_jobs int
  pending_jobs int
  failed_jobs int
  failed_job_ids longtext
  options mediumtext [null]
  cancelled_at int [null]
  created_at int
  finished_at int [null]
}

Table failed_jobs {
  id bigint [pk, increment]
  uuid varchar [unique]
  connection text
  queue text
  payload longtext
  exception longtext
  failed_at timestamp
}

Table notifications {
  id uuid [pk]
  type varchar
  notifiable_id bigint
  notifiable_type varchar
  data text
  read_at timestamp [null]
  created_at timestamp
  updated_at timestamp
}

Table personal_access_tokens {
  id bigint [pk, increment]
  tokenable_id bigint
  tokenable_type varchar
  name text
  token varchar(64) [unique]
  abilities text [null]
  last_used_at timestamp [null]
  expires_at timestamp [null, index]
  created_at timestamp
  updated_at timestamp
}

Table refresh_tokens {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id]
  access_token_id bigint [ref: - personal_access_tokens.id, null]
  family_id uuid [index]
  replaced_by_token_id bigint [ref: - refresh_tokens.id, null]
  name varchar(120) [null]
  token_hash varchar(64) [unique]
  ip_address varchar(45) [null]
  user_agent text [null]
  last_used_at timestamp [null]
  revoked_at timestamp [null, index]
  expires_at timestamp [index]
  created_at timestamp
  updated_at timestamp
}

Table personal_access_tokens_relation_note {
  Note: "tokenable is a morphs relation (tokenable_id, tokenable_type)"
}

Table languages {
  id bigint [pk, increment]
  name varchar [unique]
  created_at timestamp
  updated_at timestamp
}

Table user_languages {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id]
  language_id bigint [ref: > languages.id]
  created_at timestamp
  updated_at timestamp
  Indexes: [user_id, language_id] (unique)
}

Table skills {
  id bigint [pk, increment]
  name varchar [unique]
  created_at timestamp
  updated_at timestamp
}

Table user_skills {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id]
  skill_id bigint [ref: > skills.id]
  created_at timestamp
  updated_at timestamp
  Indexes: [user_id, skill_id] (unique)
}

Table interests {
  id bigint [pk, increment]
  name varchar [unique]
  created_at timestamp
  updated_at timestamp
}

Table user_interests {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id]
  interest_id bigint [ref: > interests.id]
  created_at timestamp
  updated_at timestamp
  Indexes: [user_id, interest_id] (unique)
}

Table person_profiles {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id, unique]
  university varchar [null]
  major varchar [null]
  employment_status varchar [null]
  employment_type varchar [null]
  current_job_title varchar [null]
  company_name varchar [null]
  linkedin_url varchar [null]
  portfolio_url varchar [null]
  preferred_work_location enum('onsite','remote','hybrid') [null]
  expected_salary_min decimal(10,2) [null]
  expected_salary_max decimal(10,2) [null]
  about text [null]
  onboarding_step tinyint unsigned [default: 1]
  is_profile_completed boolean [default: false]
  created_at timestamp
  updated_at timestamp
}

Table company_profiles {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id, unique]
  company_name varchar
  website varchar [null]
  company_size varchar [null]
  industry varchar [null]
  location varchar [null]
  about text [null]
  logo varchar [null]
  onboarding_step tinyint unsigned [default: 1]
  is_profile_completed boolean [default: false]
  created_at timestamp
  updated_at timestamp
}

Table otp_codes {
  id bigint [pk, increment]
  user_type enum('user','super_admin')
  user_id bigint [null]
  email varchar [null]
  phone varchar [null]
  code varchar(10)
  type enum('verify_email','reset_password')
  expires_at timestamp
  verified_at timestamp [null]
  created_at timestamp
  updated_at timestamp
  Indexes: [user_type, user_id], [email, type], [phone, type]
}

Table documents {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id]
  type enum('cv','certificate')
  title varchar [null]
  file_path varchar
  file_name varchar
  mime_type varchar [null]
  file_size bigint unsigned [null]
  is_primary boolean [default: false]
  created_at timestamp
  updated_at timestamp
}

Table cache {
  key varchar [pk]
  value mediumtext
  expiration bigint [index]
}

Table cache_locks {
  key varchar [pk]
  owner varchar
  expiration bigint [index]
}

---
Notes:

- I mapped common Laravel column methods to approximate SQL types for dbdiagram.
- Morphs columns (e.g. `morphs('tokenable')`) are represented as `tokenable_id` + `tokenable_type`.
- Foreign keys are annotated with `ref` where available; dbdiagram may need manual FK lines if you prefer explicit `Ref` syntax.
- If you want me to export this as SQL `CREATE TABLE` statements instead, I can generate that next.
