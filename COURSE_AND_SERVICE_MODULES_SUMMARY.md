# Course And Service Modules Summary

## What Was Added

- Shared `categories` module for job, course, and service classification.
- `training_provider_profiles` as a singleton provider profile tied to existing users.
- `courses` with category support, publishing workflow, provider ownership, skill tagging, enrollments, and optional reviews.
- `service_requests` as a separate marketplace module, independent from jobs.
- `service_proposals` for bidding on service requests.
- Service conversations layered onto the existing `conversations`, `conversation_participants`, and `messages` tables.

## Database Tables Added

- `categories`
- `training_provider_profiles`
- `courses`
- `course_skills`
- `course_enrollments`
- `course_reviews`
- `service_requests`
- `service_request_skills`
- `service_proposals`

## Database Tables Modified

- `jobs`
  Added `category_id`.
- `conversations`
  Added `service_request_id` and `service_proposal_id`.
  Extended conversation context to support service-related threads.

## Routes Added

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

- `GET /api/auth/training-provider-profile`
- `POST /api/auth/training-provider-profile`
- `PUT /api/auth/training-provider-profile`
- `GET /api/auth/my-courses`
- `GET /api/auth/my-service-requests`
- `POST /api/auth/categories`
- `PUT /api/auth/categories/{category}`
- `DELETE /api/auth/categories/{category}`

## Controllers Created

- `App\Http\Controllers\Api\CategoryController`
- `App\Http\Controllers\Api\TrainingProviderProfileController`
- `App\Http\Controllers\Api\CourseController`
- `App\Http\Controllers\Api\CourseEnrollmentController`
- `App\Http\Controllers\Api\CourseReviewController`
- `App\Http\Controllers\Api\ServiceRequestController`
- `App\Http\Controllers\Api\ServiceProposalController`
- `App\Http\Controllers\Api\ServiceConversationController`

## Requests Created

- `App\Http\Requests\Api\Categories\StoreCategoryRequest`
- `App\Http\Requests\Api\Categories\UpdateCategoryRequest`
- `App\Http\Requests\Api\TrainingProviders\UpsertTrainingProviderProfileRequest`
- `App\Http\Requests\Api\Courses\StoreCourseRequest`
- `App\Http\Requests\Api\Courses\UpdateCourseRequest`
- `App\Http\Requests\Api\Courses\StoreCourseEnrollmentRequest`
- `App\Http\Requests\Api\Courses\UpdateCourseEnrollmentRequest`
- `App\Http\Requests\Api\Courses\StoreCourseReviewRequest`
- `App\Http\Requests\Api\Courses\UpdateCourseReviewRequest`
- `App\Http\Requests\Api\Services\StoreServiceRequestRequest`
- `App\Http\Requests\Api\Services\UpdateServiceRequestRequest`
- `App\Http\Requests\Api\Services\StoreServiceProposalRequest`
- `App\Http\Requests\Api\Services\UpdateServiceProposalRequest`

## Models Created

- `App\Models\Category`
- `App\Models\TrainingProviderProfile`
- `App\Models\Course`
- `App\Models\CourseEnrollment`
- `App\Models\CourseReview`
- `App\Models\ServiceRequest`
- `App\Models\ServiceProposal`

## Existing Files Updated

- `app/Models/User.php`
- `app/Models/Job.php`
- `app/Models/Conversation.php`
- `app/Http/Controllers/Api/ConversationController.php`
- `app/Http/Controllers/Api/JobController.php`
- `app/Http/Requests/Api/Jobs/StoreJobRequest.php`
- `app/Http/Requests/Api/Jobs/UpdateJobRequest.php`
- `routes/api.php`
- `app/Services/Auth/ForgotPasswordService.php`
- `database/seeders/DatabaseSeeder.php`

## Postman Updates

- Added folders and requests for:
  - Categories
  - Training Provider Profiles
  - Courses
  - Course Enrollments
  - Course Reviews
  - Service Requests
  - Service Proposals
  - Service Conversations
- Added collection variables for:
  - `category_id`
  - `course_id`
  - `course_enrollment_id`
  - `course_review_id`
  - `service_request_id`
  - `service_proposal_id`

## Assumptions

- Any authenticated user may create a training provider profile and then manage courses through that provider profile.
- Category CRUD remains authenticated and follows the current project’s master-data pattern rather than introducing a separate admin guard.
- Service proposal chat is anchored to a proposal and reuses the existing conversation system instead of creating separate service chat tables.
- Existing jobs remain company-owned only; the new service request module handles broader task/service posting for both person and company accounts.
- Course reviews are included because they fit cleanly into the current API style and schema.

## Follow-Up Recommendations

- Add dedicated Pest coverage for all new course and service endpoints.
- Add policies or role-based authorization if the project later introduces true admin-only category management.
- Add payment integration and a separate payments table when paid course checkout becomes a real flow.
- Add notification events for course enrollment, proposal acceptance, and service conversation creation.
