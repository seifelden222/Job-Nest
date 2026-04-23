# Course And Service Modules Summary

## Final Architecture

- Jobs are company-only.
- Courses are owned directly by users through `courses.user_id`.
- Both person and company users can create courses.
- Service requests are owned directly by users through `service_requests.user_id`.
- Both person and company users can create service requests.
- `training_provider_profiles` has been fully removed from the architecture.

## Database Tables

### Added / Kept

- `categories`
- `courses`
- `course_skills`
- `course_enrollments`
- `course_reviews`
- `service_requests`
- `service_request_skills`
- `service_proposals`

### Modified

- `jobs`
  Added `category_id`.
- `conversations`
  Added `service_request_id` and `service_proposal_id` for service-related context.
- `courses`
  Uses `user_id` foreign key to `users` for direct ownership.

### Removed

- `training_provider_profiles`

## Routes

### Public

- `GET /api/categories`
- `GET /api/categories/{category}`
- `GET /api/courses`
- `GET /api/courses/{course}`
- `GET /api/courses/{course}/reviews`
- `GET /api/service-requests`
- `GET /api/service-requests/{serviceRequest}`

### Authenticated

- `POST /api/courses`
- `PUT /api/courses/{course}`
- `DELETE /api/courses/{course}`
- `GET /api/course-enrollments`
- `PUT /api/course-enrollments/{courseEnrollment}`
- `POST /api/courses/{course}/enrollments`
- `GET /api/courses/{course}/enrollments`
- `POST /api/courses/{course}/reviews`
- `PUT /api/course-reviews/{courseReview}`
- `DELETE /api/course-reviews/{courseReview}`
- `POST /api/service-requests`
- `PUT /api/service-requests/{serviceRequest}`
- `DELETE /api/service-requests/{serviceRequest}`
- `GET /api/service-requests/{serviceRequest}/proposals`
- `POST /api/service-requests/{serviceRequest}/proposals`
- `GET /api/service-proposals/{serviceProposal}`
- `PUT /api/service-proposals/{serviceProposal}`
- `POST /api/service-proposals/{serviceProposal}/conversation`

### Auth Prefix

- `GET /api/auth/my-courses`
- `GET /api/auth/my-service-requests`
- `POST /api/auth/categories`
- `PUT /api/auth/categories/{category}`
- `DELETE /api/auth/categories/{category}`

## Key Behavioral Rules

1. Jobs
- Only company users can create, update, or delete jobs.

2. Courses
- Any authenticated user can create a course.
- Only the owner (`courses.user_id`) can update or delete a course.
- `my-courses` returns courses where `courses.user_id = auth()->id()`.

3. Service Requests
- Any authenticated user can create a service request.
- Only the owner (`service_requests.user_id`) can update or delete it.
- `my-service-requests` returns requests where `service_requests.user_id = auth()->id()`.

4. Enrollments and Reviews
- Enrollment and review flows remain intact.
- Course-owner checks use `courses.user_id`.

## Postman Updates

- Removed all training provider profile requests/endpoints.
- Kept and validated:
  - `GET /api/auth/my-courses`
  - `GET /api/auth/my-service-requests`
- Kept course and service request flows aligned with direct user ownership.
