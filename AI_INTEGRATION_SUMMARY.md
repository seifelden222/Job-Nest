# AI Integration Summary

This update completes the Laravel-side bridge between JobNest and the external AI service documented at `https://jopnest-production.up.railway.app/docs#/`.

Integrated external AI endpoints:

- `GET /api/health`
- `POST /api/recommend`
- `POST /api/recommend/realtime`
- `POST /api/chat`
- `GET /api/jobs`
- `POST /api/jobs/new`
- `GET /api/jobs/{job_id}/score`
- `GET /api/user/search`
- `GET /api/user/{user_id}`
- `POST /api/users/new`
- `GET /api/courses`
- `POST /api/courses/recommend`
- `POST /api/courses/new`

Laravel endpoints exposed to Flutter:

- `GET /api/ai/health`
- `POST /api/ai/recommendations`
- `POST /api/ai/recommendations/realtime`
- `POST /api/ai/courses/recommend`
- `GET /api/ai/users/search`
- `GET /api/ai/users/{user}`
- `GET /api/ai/jobs`
- `GET /api/ai/jobs/{job}/score`
- `GET /api/ai/courses`
- existing chatbot endpoints under `/api/chatbot/*`

Observer-based sync added:

- `UserObserver`
- `PersonProfileObserver`
- `UserSkillObserver`
- `JobObserver`
- `CourseObserver`

Observer behavior:

- Person users are synced to the AI users pool only when the required Swagger fields can be built safely.
- User sync is retried when the person profile or user skills become available.
- Jobs and courses sync after the request lifecycle finishes so their related skills are already attached before the AI payload is built.
- If required sync fields are missing, Laravel logs a warning and skips the AI push instead of breaking the main app flow.

Exact Swagger payload mapping used:

User sync to `POST /api/users/new`

- `user_name` <- `users.name`
- `user_skills` <- pipe-separated `skills.name`
- `role` <- `person_profiles.current_job_title`, otherwise `employment_status`, otherwise `Student`
- `user_location` <- `"Unknown"` because no person city field exists locally
- `experience_years` <- `0` because no numeric experience-years column exists locally
- `preferred_job_type` <- `preferred_work_location|employment_type`
- `expected_salary_egp` <- `expected_salary_min-expected_salary_max`

Job sync to `POST /api/jobs/new`

- `company_name` <- `company_profiles.company_name`, fallback `users.name`
- `title` <- localized `jobs.title`
- `job_required_skills` <- pipe-separated `skills.name`
- `job_location` <- `jobs.location`
- `industry` <- `jobs.industry`
- `job_type` <- formatted `jobs.employment_type`
- `salary_range_egp` <- `salary_min-salary_max`
- `experience_required` <- mapped from `jobs.experience_level`
- `description` <- localized `jobs.description`

Course sync to `POST /api/courses/new`

- `title` <- localized `courses.title`
- `specialty` <- `categories.name`
- `platform` <- `"JobNest"`
- `level` <- formatted `courses.level`
- `language` <- mapped from `courses.language`
- `price` <- `"Free"` or `"Paid"`
- `rating` <- `0`
- `skills` <- pipe-separated `skills.name`
- `instructor` <- course owner `users.name`
- `duration` <- `duration_hours + " hours"`
- `certificate` <- empty string because no dedicated local field exists
- `url` <- `courses.url`

Database/schema changes:

- added `users.ai_user_id`
- added `jobs.ai_job_id`
- added `courses.ai_course_id`

These fields store the external AI identifiers returned by the sync endpoints so Laravel can safely call:

- `GET /api/user/{user_id}`
- `GET /api/jobs/{job_id}/score`

Chatbot integration:

- Existing chatbot conversations and messages are reused.
- Laravel stores the user prompt locally, calls external `POST /api/chat`, stores the assistant reply, and returns normalized reply data.
- Outgoing chat payload uses exact upstream names:
  - `message`
  - `user_id`
  - `top_n`
  - `context`

Failure handling:

- Missing `AI_BASE_URL` returns a normalized `503`.
- Upstream connection problems return a normalized `504`.
- Invalid or non-2xx upstream responses return a normalized `502`.
- Observer sync failures do not block user/job/course creation, but they are logged and the local record remains unsynced until a later eligible observer event.

Assumptions made:

- The sync endpoints return `user_id`, `job_id`, and `course_id` in successful responses as described by the Swagger endpoint descriptions.
- The external jobs listing endpoint currently returns a server error in the live service for tested public requests, so Laravel still supports the endpoint but normalizes upstream failures when they occur.
