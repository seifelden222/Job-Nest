# JobNest Graduation Project Documentation

**Project Name:** JobNest  
**Project Type:** Graduation Project (Backend REST API)  
**Primary Stack:** Laravel, PHP, MySQL, RESTful APIs, Sanctum Token Authentication, External AI Service  
**Prepared By:** [Student Name]  
**Supervisor:** [Supervisor Name]  
**Academic Year:** 2025/2026

---

## Table of Contents

1. [Project Abstract](#1-project-abstract)
2. [Introduction](#2-introduction)
3. [Problem Statement](#3-problem-statement)
4. [Project Objectives](#4-project-objectives)
5. [Project Scope](#5-project-scope)
6. [Development Methodology](#6-development-methodology)
7. [Technology Stack and Tools](#7-technology-stack-and-tools)
8. [System Architecture](#8-system-architecture)
9. [Database Design and Data Model](#9-database-design-and-data-model)
10. [Authentication, Authorization, and Security](#10-authentication-authorization-and-security)
11. [Localization and Translation Strategy](#11-localization-and-translation-strategy)
12. [API Design Principles](#12-api-design-principles)
13. [Comprehensive API Endpoint Reference](#13-comprehensive-api-endpoint-reference)
14. [AI Chatbot and Recommendation Integration](#14-ai-chatbot-and-recommendation-integration)
15. [Error Handling and Reliability](#15-error-handling-and-reliability)
16. [Testing and Validation Strategy](#16-testing-and-validation-strategy)
17. [Deployment and Operations](#17-deployment-and-operations)
18. [Limitations and Current Constraints](#18-limitations-and-current-constraints)
19. [Future Enhancements](#19-future-enhancements)
20. [Conclusion](#20-conclusion)
21. [Possible Discussion Questions and Answers](#21-possible-discussion-questions-and-answers)

---

## 1. Project Abstract

JobNest is a backend-centric career and professional services platform implemented as a Laravel REST API. The system supports two account categories (person and company) and provides modular functionality for authentication, profile management, job publishing and applications, course enrollment and reviews, service requests and proposals, conversations and messaging, saved items, and notifications.

A key contribution of the project is the integration of an external AI service for chatbot responses and recommendation endpoints. The backend is responsible for validating client requests, enforcing authorization, persisting data, assembling contextual payloads, forwarding requests to the AI service, and returning structured JSON responses.

The project demonstrates practical use of secure token-based authentication, layered backend architecture, relational database design, and API-first development suitable for mobile or web clients.

---

## 2. Introduction

Digital recruitment and professional development platforms often separate job discovery, learning opportunities, and communication channels into disconnected systems. JobNest addresses this fragmentation by providing a unified backend platform that combines:

- Recruitment workflows (job posting and applications)
- Learning workflows (courses, enrollments, and reviews)
- Service marketplace interactions (service requests and proposals)
- User-to-user communication and notifications
- AI-supported assistance and recommendations

This project is intentionally designed as a REST API backend, enabling interoperability with multiple client types such as Flutter mobile applications, web frontends, and API testing tools (e.g., Postman).

---

## 3. Problem Statement

Conventional job platforms typically rely on manual filtering and keyword searches, which limits personalization and recommendation quality. In addition, employers and applicants often require structured communication and lifecycle tracking that many basic platforms do not provide.

The core problem addressed by JobNest is the absence of an integrated and secure backend system that can:

- Serve multiple user roles with distinct permissions
- Manage profile enrichment data (skills, languages, interests, and documents)
- Coordinate jobs, applications, courses, and service requests in one platform
- Provide AI-assisted interaction and recommendation support
- Maintain robust access control and consistent API contracts

---

## 4. Project Objectives

The primary objectives of JobNest are:

1. Build a secure and modular Laravel REST API backend.
2. Support registration and authentication flows for both person and company accounts.
3. Implement profile onboarding and profile asset management.
4. Support jobs and applications lifecycle management.
5. Provide course publishing, enrollment, and review functionality.
6. Provide service request and proposal workflows.
7. Support direct messaging and conversation-based communication.
8. Integrate with an external AI service for chatbot responses and recommendations.
9. Support bilingual API behavior (Arabic and English).
10. Provide deployment-ready infrastructure and testable API endpoints.

---

## 5. Project Scope

### 5.1 In Scope

The current implementation includes:

- Authentication, session handling, and token refresh flows
- Person and company account onboarding
- Profile management and document management
- Skills, languages, interests, and categories management
- Jobs and applications module
- Courses, enrollments, and reviews module
- Service requests and proposals module
- Conversations and messages module
- Notifications and saved items module
- AI chatbot and recommendation endpoints via external AI service
- Localization-aware API responses (English/Arabic)

### 5.2 Out of Scope

The current version does not implement:

- Payment processing
- Video interviews
- Real-time socket-based chat
- Administrative dashboard UI
- Advanced analytics dashboards
- AI-based CV parsing

---

## 6. Development Methodology

The project follows an API-first, modular backend development approach:

1. **Requirements decomposition:** Features were organized into functional modules (auth, jobs, courses, chatbot, etc.).
2. **Schema-driven design:** Database entities and relationships were defined to support module workflows.
3. **Contract-first endpoints:** Route definitions and request/response contracts were standardized for client integration.
4. **Layered implementation:** Controllers, requests, services, models, and resources were separated to improve maintainability.
5. **Iterative validation:** Endpoints were tested and refined using Postman, logs, and database verification.

---

## 7. Technology Stack and Tools

| Category | Technology | Role in Project |
|---|---|---|
| Backend Framework | Laravel (PHP) | Routing, middleware, validation, ORM, queues, notifications |
| Language | PHP | Core implementation language |
| Database | MySQL | Persistent storage for all domain entities |
| API Style | RESTful JSON APIs | Communication interface with clients |
| Authentication | Laravel Sanctum | Token-based API authentication |
| AI Integration | External AI Service | Chatbot and recommendation generation |
| API Testing | Postman | Functional endpoint testing and debugging |
| Deployment | Railway | Hosting backend and external service infrastructure |
| Version Control | Git/GitHub | Source management and collaboration |

---

## 8. System Architecture

### 8.1 High-Level Architecture

```text
Client (Web / Mobile / Postman)
            |
            v
     Laravel REST API Layer
            |
   ---------------------------
   |            |            |
   v            v            v
MySQL DB   External AI API  File Storage

Optional supporting services:
- Queue workers (notifications/emails)
- Logging and monitoring
```

### 8.2 Backend Layer Responsibilities

- **Routing Layer (`routes/api.php`):** Defines endpoint structure and middleware protection.
- **Validation Layer (`FormRequest`):** Validates payload shape and input constraints.
- **Controller Layer:** Handles HTTP interaction and delegates business logic.
- **Service Layer:** Encapsulates business rules and integration logic.
- **Persistence Layer (Eloquent + MySQL):** Handles relational data operations.
- **Integration Layer:** Communicates with external AI endpoints and maps failures to controlled API errors.

### 8.3 Request Lifecycle (Typical Protected Endpoint)

1. Client sends HTTP request.
2. Laravel middleware validates token for protected routes.
3. Request data is validated.
4. Controller calls service logic.
5. Service executes database operations and/or external integrations.
6. API returns structured JSON response.

### 8.4 AI Chatbot Lifecycle

1. Client sends message to chatbot endpoint.
2. Laravel validates authentication and payload.
3. User message is persisted.
4. Recent context is assembled.
5. Context + user data is sent to external AI service.
6. AI response is received and persisted as assistant message.
7. API returns both user and assistant message data.

---

## 9. Database Design and Data Model

### 9.1 Core Design Principles

- Normalize entities to avoid redundant data.
- Use pivot tables for many-to-many relations.
- Preserve auditability through explicit status and relationship records.
- Keep authentication/session/token records separate from domain data.

### 9.2 Main Tables by Domain

| Domain | Key Tables |
|---|---|
| Authentication | `users`, `personal_access_tokens`, `refresh_tokens`, `otp_codes`, `password_reset_tokens`, `sessions` |
| Profiles | `person_profiles`, `company_profiles`, `documents`, `user_skills`, `user_languages`, `user_interests` |
| Reference Data | `skills`, `languages`, `interests`, `categories` |
| Jobs | `jobs`, `job_skills`, `applications` |
| Conversations | `conversations`, `conversation_participants`, `messages` |
| Courses | `courses`, `course_skills`, `course_enrollments`, `course_reviews` |
| Services | `service_requests`, `service_request_skills`, `service_proposals` |
| User Productivity | `saved_items`, `notifications` |
| System Support | `cache`, `cache_locks`, `queue_jobs`, `job_batches`, `failed_jobs`, `migrations` |

### 9.3 Principal Relationships

```text
users 1--1 person_profiles
users 1--1 company_profiles
users 1--many jobs
users 1--many applications
users many--many skills (user_skills)
users many--many languages (user_languages)
users many--many interests (user_interests)
jobs many--many skills (job_skills)
jobs 1--many applications
conversations many--many users (conversation_participants)
conversations 1--many messages
courses many--many skills (course_skills)
courses 1--many enrollments and reviews
service_requests many--many skills (service_request_skills)
service_requests 1--many service_proposals
users 1--many notifications
users 1--many saved_items
```

### 9.4 Data Integrity Notes

- Foreign keys preserve relational consistency.
- Ownership-based constraints are enforced in authorization logic.
- Token and refresh-token records are managed separately for secure session lifecycle control.

---

## 10. Authentication, Authorization, and Security

### 10.1 Authentication Model

JobNest uses token-based authentication via Laravel Sanctum.

- After login, the backend issues an access token.
- Protected endpoints require:

```http
Authorization: Bearer {access_token}
```

### 10.2 Refresh Token Strategy

- The system supports refresh-token based re-authentication.
- Refresh token rotation is used to reduce replay risk.
- Access and refresh token expiration timestamps are returned in authentication responses.

### 10.3 Authorization Model

Authorization is policy/ownership driven and includes:

- Role-based checks (person, company, admin)
- Owner-only resource modification
- Participant-only conversation/message access
- Verified-email requirements for specific endpoints

### 10.4 Security Implementation Notes

- Sensitive operations depend on authenticated user identity from token (`$request->user()`).
- Client-supplied identifiers (such as `user_id`) are not trusted for authorization decisions.
- Validation constraints prevent malformed and incomplete requests.
- Throttle middleware is used on selected high-risk endpoints (e.g., login, token refresh).

---

## 11. Localization and Translation Strategy

JobNest supports English (`en`) and Arabic (`ar`) output behavior.

### 11.1 Language Resolution Order

1. `Accept-Language` header
2. `lang` query parameter
3. Default fallback (`en`)

### 11.2 Storage and Response Strategy

- Translatable fields are stored as JSON objects with language keys.
- API responses return the value in the resolved language context.

Example storage format:

```json
{
  "en": "Backend Engineer",
  "ar": "مهندس باك اند"
}
```

---

## 12. API Design Principles

1. **RESTful routing conventions** using resource-oriented endpoint names.
2. **Consistent JSON response structure** for success and error payloads.
3. **Clear authentication boundaries** between public and protected APIs.
4. **Validation-first request handling** using Laravel form requests.
5. **Separation of concerns** through controllers/services/resources.
6. **Language-aware responses** for bilingual client support.

---

## 13. Comprehensive API Endpoint Reference

### 13.1 Response Legend

- **Auth:** `Public`, `Auth`, `Verified`, `Owner`, `Participant`, `Admin`, or `Signed URL`
- **Request Body:** Required fields only (key examples). `N/A` means no body required.
- **Success Response:** High-level shape only.

---

### 13.2 Public Discovery Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/categories` | List all public categories. | Public | N/A | Category collection with metadata. |
| GET | `/api/categories/{category}` | Retrieve one category by identifier. | Public | N/A | Category details. |
| GET | `/api/jobs` | List active jobs with filtering support. | Public | N/A | Job collection. |
| GET | `/api/jobs/{job}` | Retrieve details for one active job. | Public | N/A | Job details. |
| GET | `/api/courses` | List published courses. | Public | N/A | Course collection. |
| GET | `/api/courses/{course}` | Retrieve one course. | Public | N/A | Course details. |
| GET | `/api/courses/{course}/reviews` | List course reviews. | Public | N/A | Review collection. |
| GET | `/api/service-requests` | List open service requests. | Public | N/A | Service request collection. |
| GET | `/api/service-requests/{serviceRequest}` | Retrieve service request details. | Public | N/A | Service request details. |
| GET | `/api/users/{user}/portfolio` | Retrieve public portfolio for a user. | Public | N/A | Portfolio entries. |

---

### 13.3 Authentication and Account Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `/api/auth/register/step-1` | Create person account and start onboarding. | Public | `name`, `email`, `password`, other onboarding fields | User + token payload. |
| POST | `/api/auth/register/company` | Register company account. | Public | Company registration fields | User + token payload. |
| POST | `/api/auth/login` | Authenticate with credentials. | Public | `email`, `password`, optional `device_name` | Access/refresh tokens + user resource. |
| POST | `/api/auth/google/login` | Authenticate using Google identity flow. | Public | Google credential payload | Token payload + user resource. |
| POST | `/api/auth/refresh-token` | Exchange refresh token for new tokens. | Public | `refresh_token`, optional `device_name` | New token payload. |
| POST | `/api/auth/forgot-password` | Start password reset via OTP. | Public | Email or identifier fields | Confirmation message. |
| POST | `/api/auth/verify-reset-otp` | Validate OTP code for password reset. | Public | Reset OTP fields | Confirmation message. |
| POST | `/api/auth/resend-reset-otp` | Resend password reset OTP. | Public | Email or identifier fields | Confirmation message. |
| POST | `/api/auth/reset-password` | Complete password reset. | Public | Reset token/OTP + new password fields | Confirmation message. |
| GET | `/api/auth/email/verify/{id}/{hash}` | Verify email from signed URL. | Signed URL | N/A | Verification status payload. |
| POST | `/api/auth/register/step-2` | Continue person onboarding profile data. | Auth | Profile step-2 fields | Updated user profile state. |
| POST | `/api/auth/register/step-3` | Complete person onboarding. | Auth | Profile step-3 fields | Updated user profile state. |
| POST | `/api/auth/email/verification/send` | Send verification email. | Auth | N/A | Confirmation message. |
| POST | `/api/auth/email/verification/resend` | Resend verification email. | Auth | N/A | Confirmation message. |
| GET | `/api/auth/email/verification-status` | Check current email verification state. | Auth | N/A | `email_verified` status payload. |
| GET | `/api/auth/me` | Retrieve authenticated user details. | Auth | N/A | User resource. |
| POST | `/api/auth/change-password` | Change account password. | Verified | Current and new password fields | Confirmation message. |
| GET | `/api/auth/sessions` | List active sessions/devices. | Verified | N/A | Session list with current token marker. |
| DELETE | `/api/auth/sessions/{sessionId}` | Revoke one active session/device. | Verified | N/A | Confirmation message. |
| POST | `/api/auth/logout` | Revoke current session token. | Auth | N/A | Confirmation message. |
| POST | `/api/auth/logout-all` | Revoke all active user sessions. | Auth | N/A | Revocation summary payload. |

---

### 13.4 Profile and User Asset Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/auth/profile` | Retrieve authenticated user profile. | Auth | N/A | Profile payload (person/company structure). |
| PUT | `/api/auth/profile` | Update authenticated user profile. | Auth | Profile update fields | Updated profile payload. |
| GET | `/api/auth/user-documents` | List user documents (CV/certificates). | Auth | N/A | Document collection. |
| POST | `/api/auth/user-documents` | Upload a user document. | Auth | Multipart document fields | Created document metadata. |
| DELETE | `/api/auth/user-documents/{user_document}` | Delete one owned document. | Owner | N/A | Confirmation message. |

---

### 13.5 User Skills, Languages, and Interests Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/auth/user-skills` | List skills linked to current user. | Auth | N/A | User skill collection. |
| POST | `/api/auth/user-skills` | Add or update user skills. | Auth | Skill assignment payload | Updated user skill collection. |
| DELETE | `/api/auth/user-skills/{user_skill}` | Remove skill from user profile. | Owner | N/A | Confirmation message. |
| GET | `/api/auth/user-languages` | List user languages. | Auth | N/A | User language collection. |
| POST | `/api/auth/user-languages` | Add or update user languages. | Auth | Language assignment payload | Updated user language collection. |
| DELETE | `/api/auth/user-languages/{user_language}` | Remove language from user profile. | Owner | N/A | Confirmation message. |
| GET | `/api/auth/user-interests` | List user interests. | Auth | N/A | User interest collection. |
| POST | `/api/auth/user-interests` | Add or update user interests. | Auth | Interest assignment payload | Updated user interest collection. |
| DELETE | `/api/auth/user-interests/{user_interest}` | Remove interest from user profile. | Owner | N/A | Confirmation message. |

---

### 13.6 Reference Data Endpoints (Skills, Languages, Interests, Categories)

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/auth/skills` | List skill catalog entries. | Auth/Public by policy | N/A | Skill collection. |
| POST | `/api/auth/skills` | Create skill catalog item. | Admin | Skill fields | Created skill resource. |
| GET | `/api/auth/skills/{skill}` | Retrieve one skill. | Auth/Public by policy | N/A | Skill resource. |
| PUT/PATCH | `/api/auth/skills/{skill}` | Update a skill item. | Admin | Updated skill fields | Updated skill resource. |
| DELETE | `/api/auth/skills/{skill}` | Delete a skill item. | Admin | N/A | Confirmation message. |
| GET | `/api/auth/languages` | List language catalog entries. | Auth/Public by policy | N/A | Language collection. |
| POST | `/api/auth/languages` | Create language catalog item. | Admin | Language fields | Created language resource. |
| GET | `/api/auth/languages/{language}` | Retrieve one language. | Auth/Public by policy | N/A | Language resource. |
| PUT/PATCH | `/api/auth/languages/{language}` | Update language item. | Admin | Updated language fields | Updated language resource. |
| DELETE | `/api/auth/languages/{language}` | Delete language item. | Admin | N/A | Confirmation message. |
| GET | `/api/auth/interests` | List interest catalog entries. | Auth/Public by policy | N/A | Interest collection. |
| POST | `/api/auth/interests` | Create interest catalog item. | Admin | Interest fields | Created interest resource. |
| GET | `/api/auth/interests/{interest}` | Retrieve one interest. | Auth/Public by policy | N/A | Interest resource. |
| PUT/PATCH | `/api/auth/interests/{interest}` | Update interest item. | Admin | Updated interest fields | Updated interest resource. |
| DELETE | `/api/auth/interests/{interest}` | Delete interest item. | Admin | N/A | Confirmation message. |
| POST | `/api/auth/categories` | Create category. | Admin | Category fields | Created category resource. |
| PUT/PATCH | `/api/auth/categories/{category}` | Update category. | Admin | Updated category fields | Updated category resource. |
| DELETE | `/api/auth/categories/{category}` | Delete category. | Admin | N/A | Confirmation message. |

---

### 13.7 Jobs and Applications Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `/api/jobs` | Create a job posting. | Company/Auth | Job payload (`title`, `description`, `location`, etc.) | Created job resource. |
| PUT | `/api/jobs/{job}` | Update owned job posting. | Owner/Company | Updated job fields | Updated job resource. |
| DELETE | `/api/jobs/{job}` | Remove owned job posting. | Owner/Company | N/A | Confirmation message. |
| GET | `/api/jobs/{job}/applications` | List applications for a job owner/company. | Owner/Company | N/A | Application collection. |
| POST | `/api/jobs/{job}/applications` | Submit job application as person user. | Person/Auth | `cv_document_id`, `cover_letter`, language field | Created application resource. |
| GET | `/api/applications/{application}` | Retrieve one application. | Owner or Applicant | N/A | Application details. |
| PUT | `/api/applications/{application}` | Update application status/details. | Owner/Company | Status/update payload | Updated application resource. |
| DELETE | `/api/applications/{application}` | Withdraw or remove application. | Applicant/Owner | N/A | Confirmation message. |
| GET | `/api/auth/my-applications` | List authenticated user applications. | Auth | N/A | Application collection. |

---

### 13.8 Conversations and Messages Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/conversations` | List user conversations. | Auth | N/A | Conversation collection. |
| POST | `/api/conversations` | Create direct or context-bound conversation. | Auth | Conversation initialization payload | Created conversation resource. |
| GET | `/api/conversations/{conversation}` | Retrieve one conversation. | Participant | N/A | Conversation details. |
| GET | `/api/conversations/{conversation}/messages` | List messages in conversation. | Participant | N/A | Message collection. |
| POST | `/api/conversations/{conversation}/messages` | Send message to conversation. | Participant | Message body (`body`, `message_type`, optional file data) | Created message resource. |

---

### 13.9 Chatbot Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/chatbot/conversations` | List chatbot conversations for user. | Auth | N/A | Chatbot conversation collection. |
| POST | `/api/chatbot/conversations` | Create/reuse chatbot conversation thread. | Auth | Optional conversation init payload | Conversation descriptor. |
| GET | `/api/chatbot/conversations/{conversation_id}` | Retrieve one chatbot conversation. | Auth/Owner | N/A | Conversation details. |
| GET | `/api/chatbot/conversations/{conversation_id}/messages` | List chatbot messages in thread. | Auth/Owner | N/A | Message collection. |
| POST | `/api/chatbot/conversations/{conversation_id}/messages` | Submit prompt and get AI reply. | Auth/Owner | `body`, optional `source_language`, optional `top_n` | User message + assistant message payload. |

---

### 13.10 AI Gateway and Recommendation Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/ai/health` | Check AI integration availability. | Auth | N/A | Health status payload. |
| POST | `/api/ai/recommendations` | Get generic recommendations for user context. | Auth | Recommendation input fields (optional personalization overrides) | Recommendation list payload. |
| POST | `/api/ai/recommendations/realtime` | Get realtime recommendation output based on profile-like inputs. | Auth | `user_skills`, `cv_summary`, `user_location`, `experience_years`, `preferred_job_type`, `expected_salary_egp`, `top_n` (optional) | Realtime recommendation payload. |
| POST | `/api/ai/courses/recommend` | Get course recommendations for authenticated user. | Auth | Optional `user_name`, optional `top_n` | Course recommendation payload. |
| GET | `/api/ai/users/search` | Search users via AI gateway service. | Auth | N/A | Matched user summaries. |
| GET | `/api/ai/users/{user}` | Retrieve AI gateway user details. | Auth | N/A | User AI data payload. |
| GET | `/api/ai/jobs` | Retrieve jobs through AI gateway endpoint. | Auth | N/A | Job list payload. |
| GET | `/api/ai/jobs/{job}/score` | Get AI score for one job relative to user/context. | Auth | N/A | Score and scoring metadata. |
| GET | `/api/ai/courses` | Retrieve courses through AI gateway endpoint. | Auth | N/A | Course list payload. |

---

### 13.11 Courses, Enrollments, and Reviews Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `/api/courses` | Create a course. | Auth | Course payload | Created course resource. |
| PUT | `/api/courses/{course}` | Update owned course. | Owner | Updated course payload | Updated course resource. |
| DELETE | `/api/courses/{course}` | Delete owned course. | Owner | N/A | Confirmation message. |
| POST | `/api/courses/{course}/enrollments` | Enroll authenticated user in course. | Auth | Enrollment payload if required | Enrollment resource. |
| GET | `/api/courses/{course}/enrollments` | List enrollments for provider/owner. | Owner | N/A | Enrollment collection. |
| GET | `/api/course-enrollments` | List authenticated user enrollments. | Auth | N/A | Enrollment collection. |
| PUT | `/api/course-enrollments/{courseEnrollment}` | Update enrollment state (owner/provider). | Owner/Provider | Enrollment update fields | Updated enrollment resource. |
| POST | `/api/courses/{course}/reviews` | Create review for enrolled user. | Auth/Enrolled | Review fields (`rating`, `comment`, etc.) | Created review resource. |
| PUT | `/api/course-reviews/{courseReview}` | Update own review. | Review Owner | Review update fields | Updated review resource. |
| DELETE | `/api/course-reviews/{courseReview}` | Delete own review. | Review Owner | N/A | Confirmation message. |
| GET | `/api/auth/my-courses` | List courses related to authenticated user. | Auth | N/A | Course collection. |

---

### 13.12 Service Requests and Proposals Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `/api/service-requests` | Create service request. | Auth | Service request payload | Created service request resource. |
| PUT | `/api/service-requests/{serviceRequest}` | Update owned service request. | Owner | Updated fields | Updated service request resource. |
| DELETE | `/api/service-requests/{serviceRequest}` | Delete owned service request. | Owner | N/A | Confirmation message. |
| GET | `/api/service-requests/{serviceRequest}/proposals` | List proposals for service request owner. | Owner | N/A | Proposal collection. |
| POST | `/api/service-requests/{serviceRequest}/proposals` | Submit proposal to service request. | Auth | Proposal payload | Created proposal resource. |
| GET | `/api/service-proposals/{serviceProposal}` | Retrieve one proposal. | Owner or Proposer | N/A | Proposal details. |
| PUT | `/api/service-proposals/{serviceProposal}` | Update proposal status/content. | Owner or Proposer | Update payload | Updated proposal resource. |
| POST | `/api/service-proposals/{serviceProposal}/conversation` | Create conversation from proposal. | Owner or Proposer | Optional init payload | Created conversation resource. |
| GET | `/api/auth/my-service-requests` | List authenticated user service requests. | Auth | N/A | Service request collection. |

---

### 13.13 Saved Items and Notifications Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/auth/saved-items` | List all saved items for user. | Auth | N/A | Saved item collection. |
| POST | `/api/auth/saved-items` | Save a target item (job/course/service). | Auth | `type`, `target_id` | Created/updated saved item state. |
| DELETE | `/api/auth/saved-items/{type}/{targetId}` | Remove one saved item. | Auth | N/A | Confirmation message. |
| GET | `/api/auth/saved-items/check` | Check whether item is saved. | Auth | Query params (`type`, `target_id`) | Boolean-like saved state payload. |
| GET | `/api/auth/notifications` | List user notifications. | Auth | N/A | Notification collection. |
| GET | `/api/auth/notifications/unread-count` | Retrieve unread notification count. | Auth | N/A | Count payload. |
| PATCH | `/api/auth/notifications/mark-all-read` | Mark all notifications as read. | Auth | N/A | Confirmation + summary. |
| PATCH | `/api/auth/notifications/{notification}` | Mark one notification as read. | Auth | N/A | Updated notification status. |
| DELETE | `/api/auth/notifications/{notification}` | Delete one notification record. | Auth | N/A | Confirmation message. |

---

### 13.14 External AI Service Endpoint (Internal Integration)

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `{AI_BASE_URL}/api/chat` | Generate chatbot/recommendation output in external AI service. | Internal service-to-service | `message`, `user_id`, optional `top_n`, optional `context` | AI-generated response payload consumed by Laravel. |

---

## 14. AI Chatbot and Recommendation Integration

### 14.1 Integration Purpose

The external AI integration provides:

- Conversational assistant replies
- Job/course recommendation support
- Context-aware outputs using user history and recent messages

### 14.2 Backend Responsibilities in AI Flow

Laravel performs all integration control steps:

1. Validates request and authenticated user.
2. Persists incoming user message.
3. Builds contextual payload from recent conversation history.
4. Maps authenticated user identity to AI payload user identifier.
5. Calls external AI endpoint.
6. Persists assistant response.
7. Returns structured API response to client.

### 14.3 Example Internal AI Payload

```json
{
  "message": "Help me find a backend job.",
  "user_id": 45,
  "top_n": 5,
  "context": [
    {
      "role": "user",
      "content": "Help me find a backend job."
    }
  ]
}
```

### 14.4 Failure Modes and Controls

| Scenario | Typical Error | Backend Handling |
|---|---|---|
| Invalid AI URL or unreachable upstream | 502 / upstream failure | Controlled JSON error response |
| Missing user mapping in payload | `user_id: null` at AI side | Use authenticated user object to build payload |
| Invalid conversation access | 404 model not found or authorization failure | Restrict by ownership/participant rules |

---

## 15. Error Handling and Reliability

### 15.1 HTTP Status Policy

| Status | Meaning in JobNest |
|---|---|
| 200 | Successful read/update operation |
| 201 | Successful creation operation |
| 400 | Invalid request format |
| 401 | Missing or invalid authentication token |
| 403 | Authenticated but not authorized |
| 404 | Resource not found or inaccessible |
| 422 | Validation errors |
| 429 | Rate-limited endpoint |
| 500 | Unexpected backend error |
| 502 | External AI integration failure |

### 15.2 Standard Error Payload Style

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

### 15.3 Reliability Practices Used

- Validation-first endpoint handling
- Explicit exception-to-response mapping in integration points
- Persistent logs for debugging request failures and external service errors
- Queue infrastructure support for asynchronous operations

---

## 16. Testing and Validation Strategy

### 16.1 Testing Tools

- Postman collections for endpoint coverage
- Laravel logs for backend behavior verification
- Database inspection for persistence validation
- Manual scenario testing for workflow correctness

### 16.2 Core Functional Test Scenarios

| Scenario | Expected Outcome |
|---|---|
| Register and login flow | User and token payload returned |
| Access protected endpoint without token | `401 Unauthenticated` |
| Create and list jobs | Job persists and appears in listing |
| Apply to job | Application record is created |
| Conversation message send | Message persists and is retrievable |
| Chatbot message send | User message + AI reply returned or controlled AI error |
| Invalid resource access | `404` or `403` depending on policy |

### 16.3 API Contract Verification Checklist

- Required headers set (`Accept`, `Content-Type`, `Authorization` when needed)
- Request schema matches validation rules
- Response codes align with endpoint contract
- Error payloads remain consistent across modules

---

## 17. Deployment and Operations

### 17.1 Deployment Platform

The backend and AI service are deployed on Railway.

### 17.2 Required Runtime Components

- PHP runtime and Composer dependencies
- MySQL database
- Environment variable configuration
- Laravel migration execution
- Queue worker for queued workloads (where applicable)

### 17.3 Environment Variables (Core)

```env
APP_NAME=JobNest
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-backend-url.com

DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

AI_BASE_URL=https://your-ai-service-url.com
EXTERNAL_AI_BASE_URL=https://your-ai-service-url.com

MAIL_MAILER=smtp
QUEUE_CONNECTION=database
```

### 17.4 Configuration Refresh Commands

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

## 18. Limitations and Current Constraints

1. No built-in payment integration.
2. No real-time socket chat implementation.
3. No administrative UI dashboard in current scope.
4. AI output quality depends on external AI service availability and behavior.
5. Advanced analytical dashboards are not included in this phase.

---

## 19. Future Enhancements

1. Real-time chat using WebSockets.
2. AI-assisted CV parsing and profile enrichment.
3. Advanced recommendation ranking and explainability.
4. Company-side analytics dashboards.
5. Payment support for premium course/service workflows.
6. Enhanced interview scheduling and tracking workflows.
7. Push notification channels and richer notification delivery strategies.

---

## 20. Conclusion

JobNest demonstrates a complete backend/API implementation for a multi-domain career platform using Laravel and MySQL. The project integrates authentication, profile enrichment, job and course workflows, service marketplace operations, messaging, notifications, and external AI-assisted interactions under one RESTful architecture.

From a graduation project perspective, JobNest provides evidence of practical competency in backend architecture, secure API design, relational database modeling, external service integration, and deployment-readiness for production-like environments.

---

## 21. Possible Discussion Questions and Answers

### Q1. Why was Laravel chosen for this project?
**Answer:** Laravel provides a mature ecosystem for API development, including routing, middleware, validation, authentication (Sanctum), ORM support, queue handling, and modular code organization. This reduced boilerplate and improved maintainability.

### Q2. Why is token-based authentication appropriate for JobNest?
**Answer:** JobNest is an API-first system consumed by web/mobile clients. Token-based authentication is stateless, scalable, and suitable for multi-client environments. Sanctum also integrates naturally with Laravel request lifecycle and authorization checks.

### Q3. How do you ensure that one user cannot access another user's protected data?
**Answer:** Protected endpoints require valid tokens, and authorization is enforced through ownership and participant checks (e.g., conversation participants, resource owners, role restrictions). Sensitive logic depends on `$request->user()` identity.

### Q4. What is the difference between authentication and authorization in your implementation?
**Answer:** Authentication confirms who the user is (valid token), while authorization checks what the user is allowed to do (role, ownership, participation, verification status).

### Q5. How does the chatbot integration work technically?
**Answer:** The backend validates and stores the user message, builds recent context, sends a structured payload to the external AI service, stores the returned assistant message, and responds to the client with persisted conversation data.

### Q6. Why should backend code avoid trusting `user_id` sent by the client?
**Answer:** Client-side payloads can be manipulated. The secure approach is to derive identity from the authenticated token and use server-side authorization logic for all sensitive operations.

### Q7. How do you handle AI service failures without breaking the platform?
**Answer:** Integration errors are mapped to controlled API responses (e.g., 502), and the backend preserves consistent response structures, enabling clients to handle failures gracefully.

### Q8. What database design choices were important in JobNest?
**Answer:** Many-to-many relationships are normalized through pivot tables (skills, languages, interests), workflow entities are separated by module, and token/session tables are isolated for secure authentication lifecycle management.

### Q9. How was API quality validated?
**Answer:** API quality was validated using Postman collections, manual workflow testing, log analysis, and database state verification after key operations.

### Q10. What would you prioritize if this project moved to production scale?
**Answer:** Priority areas include automated test coverage expansion, centralized monitoring/alerting, queue scaling, caching optimization, and tighter observability around external AI dependencies.

### Q11. How does localization influence API design in this project?
**Answer:** Localization is handled at API level via `Accept-Language` and fallback logic. Translatable content is stored in multilingual format while responses return the active language value to simplify client rendering.

### Q12. What is the most important backend engineering lesson from this project?
**Answer:** A clean separation between validation, business logic, persistence, and integrations significantly improves reliability, debugging speed, and long-term extensibility.

---

**End of Document**# JobNest Graduation Project Documentation

**Project Name:** JobNest  
**Project Type:** Graduation Project (Backend REST API)  
**Primary Stack:** Laravel, PHP, MySQL, RESTful APIs, Sanctum Token Authentication, External AI Service  
**Prepared By:** [Student Name]  
**Supervisor:** [Supervisor Name]  
**Academic Year:** 2025/2026

---

## Table of Contents

1. [Project Abstract](#1-project-abstract)
2. [Introduction](#2-introduction)
3. [Problem Statement](#3-problem-statement)
4. [Project Objectives](#4-project-objectives)
5. [Project Scope](#5-project-scope)
6. [Development Methodology](#6-development-methodology)
7. [Technology Stack and Tools](#7-technology-stack-and-tools)
8. [System Architecture](#8-system-architecture)
9. [Database Design and Data Model](#9-database-design-and-data-model)
10. [Authentication, Authorization, and Security](#10-authentication-authorization-and-security)
11. [Localization and Translation Strategy](#11-localization-and-translation-strategy)
12. [API Design Principles](#12-api-design-principles)
13. [Comprehensive API Endpoint Reference](#13-comprehensive-api-endpoint-reference)
14. [AI Chatbot and Recommendation Integration](#14-ai-chatbot-and-recommendation-integration)
15. [Error Handling and Reliability](#15-error-handling-and-reliability)
16. [Testing and Validation Strategy](#16-testing-and-validation-strategy)
17. [Deployment and Operations](#17-deployment-and-operations)
18. [Limitations and Current Constraints](#18-limitations-and-current-constraints)
19. [Future Enhancements](#19-future-enhancements)
20. [Conclusion](#20-conclusion)
21. [Possible Discussion Questions and Answers](#21-possible-discussion-questions-and-answers)

---

## 1. Project Abstract

JobNest is a backend-centric career and professional services platform implemented as a Laravel REST API. The system supports two account categories (person and company) and provides modular functionality for authentication, profile management, job publishing and applications, course enrollment and reviews, service requests and proposals, conversations and messaging, saved items, and notifications.

A key contribution of the project is the integration of an external AI service for chatbot responses and recommendation endpoints. The backend is responsible for validating client requests, enforcing authorization, persisting data, assembling contextual payloads, forwarding requests to the AI service, and returning structured JSON responses.

The project demonstrates practical use of secure token-based authentication, layered backend architecture, relational database design, and API-first development suitable for mobile or web clients.

---

## 2. Introduction

Digital recruitment and professional development platforms often separate job discovery, learning opportunities, and communication channels into disconnected systems. JobNest addresses this fragmentation by providing a unified backend platform that combines:

- Recruitment workflows (job posting and applications)
- Learning workflows (courses, enrollments, and reviews)
- Service marketplace interactions (service requests and proposals)
- User-to-user communication and notifications
- AI-supported assistance and recommendations

This project is intentionally designed as a REST API backend, enabling interoperability with multiple client types such as Flutter mobile applications, web frontends, and API testing tools (e.g., Postman).

---

## 3. Problem Statement

Conventional job platforms typically rely on manual filtering and keyword searches, which limits personalization and recommendation quality. In addition, employers and applicants often require structured communication and lifecycle tracking that many basic platforms do not provide.

The core problem addressed by JobNest is the absence of an integrated and secure backend system that can:

- Serve multiple user roles with distinct permissions
- Manage profile enrichment data (skills, languages, interests, and documents)
- Coordinate jobs, applications, courses, and service requests in one platform
- Provide AI-assisted interaction and recommendation support
- Maintain robust access control and consistent API contracts

---

## 4. Project Objectives

The primary objectives of JobNest are:

1. Build a secure and modular Laravel REST API backend.
2. Support registration and authentication flows for both person and company accounts.
3. Implement profile onboarding and profile asset management.
4. Support jobs and applications lifecycle management.
5. Provide course publishing, enrollment, and review functionality.
6. Provide service request and proposal workflows.
7. Support direct messaging and conversation-based communication.
8. Integrate with an external AI service for chatbot responses and recommendations.
9. Support bilingual API behavior (Arabic and English).
10. Provide deployment-ready infrastructure and testable API endpoints.

---

## 5. Project Scope

### 5.1 In Scope

The current implementation includes:

- Authentication, session handling, and token refresh flows
- Person and company account onboarding
- Profile management and document management
- Skills, languages, interests, and categories management
- Jobs and applications module
- Courses, enrollments, and reviews module
- Service requests and proposals module
- Conversations and messages module
- Notifications and saved items module
- AI chatbot and recommendation endpoints via external AI service
- Localization-aware API responses (English/Arabic)

### 5.2 Out of Scope

The current version does not implement:

- Payment processing
- Video interviews
- Real-time socket-based chat
- Administrative dashboard UI
- Advanced analytics dashboards
- AI-based CV parsing

---

## 6. Development Methodology

The project follows an API-first, modular backend development approach:

1. **Requirements decomposition:** Features were organized into functional modules (auth, jobs, courses, chatbot, etc.).
2. **Schema-driven design:** Database entities and relationships were defined to support module workflows.
3. **Contract-first endpoints:** Route definitions and request/response contracts were standardized for client integration.
4. **Layered implementation:** Controllers, requests, services, models, and resources were separated to improve maintainability.
5. **Iterative validation:** Endpoints were tested and refined using Postman, logs, and database verification.

---

## 7. Technology Stack and Tools

| Category | Technology | Role in Project |
|---|---|---|
| Backend Framework | Laravel (PHP) | Routing, middleware, validation, ORM, queues, notifications |
| Language | PHP | Core implementation language |
| Database | MySQL | Persistent storage for all domain entities |
| API Style | RESTful JSON APIs | Communication interface with clients |
| Authentication | Laravel Sanctum | Token-based API authentication |
| AI Integration | External AI Service | Chatbot and recommendation generation |
| API Testing | Postman | Functional endpoint testing and debugging |
| Deployment | Railway | Hosting backend and external service infrastructure |
| Version Control | Git/GitHub | Source management and collaboration |

---

## 8. System Architecture

### 8.1 High-Level Architecture

```text
Client (Web / Mobile / Postman)
            |
            v
     Laravel REST API Layer
            |
   ---------------------------
   |            |            |
   v            v            v
MySQL DB   External AI API  File Storage
            
Optional supporting services:
- Queue workers (notifications/emails)
- Logging and monitoring
```

### 8.2 Backend Layer Responsibilities

- **Routing Layer (`routes/api.php`):** Defines endpoint structure and middleware protection.
- **Validation Layer (`FormRequest`):** Validates payload shape and input constraints.
- **Controller Layer:** Handles HTTP interaction and delegates business logic.
- **Service Layer:** Encapsulates business rules and integration logic.
- **Persistence Layer (Eloquent + MySQL):** Handles relational data operations.
- **Integration Layer:** Communicates with external AI endpoints and maps failures to controlled API errors.

### 8.3 Request Lifecycle (Typical Protected Endpoint)

1. Client sends HTTP request.
2. Laravel middleware validates token for protected routes.
3. Request data is validated.
4. Controller calls service logic.
5. Service executes database operations and/or external integrations.
6. API returns structured JSON response.

### 8.4 AI Chatbot Lifecycle

1. Client sends message to chatbot endpoint.
2. Laravel validates authentication and payload.
3. User message is persisted.
4. Recent context is assembled.
5. Context + user data is sent to external AI service.
6. AI response is received and persisted as assistant message.
7. API returns both user and assistant message data.

---

## 9. Database Design and Data Model

### 9.1 Core Design Principles

- Normalize entities to avoid redundant data.
- Use pivot tables for many-to-many relations.
- Preserve auditability through explicit status and relationship records.
- Keep authentication/session/token records separate from domain data.

### 9.2 Main Tables by Domain

| Domain | Key Tables |
|---|---|
| Authentication | `users`, `personal_access_tokens`, `refresh_tokens`, `otp_codes`, `password_reset_tokens`, `sessions` |
| Profiles | `person_profiles`, `company_profiles`, `documents`, `user_skills`, `user_languages`, `user_interests` |
| Reference Data | `skills`, `languages`, `interests`, `categories` |
| Jobs | `jobs`, `job_skills`, `applications` |
| Conversations | `conversations`, `conversation_participants`, `messages` |
| Courses | `courses`, `course_skills`, `course_enrollments`, `course_reviews` |
| Services | `service_requests`, `service_request_skills`, `service_proposals` |
| User Productivity | `saved_items`, `notifications` |
| System Support | `cache`, `cache_locks`, `queue_jobs`, `job_batches`, `failed_jobs`, `migrations` |

### 9.3 Principal Relationships

```text
users 1--1 person_profiles
users 1--1 company_profiles
users 1--many jobs
users 1--many applications
users many--many skills (user_skills)
users many--many languages (user_languages)
users many--many interests (user_interests)
jobs many--many skills (job_skills)
jobs 1--many applications
conversations many--many users (conversation_participants)
conversations 1--many messages
courses many--many skills (course_skills)
courses 1--many enrollments and reviews
service_requests many--many skills (service_request_skills)
service_requests 1--many service_proposals
users 1--many notifications
users 1--many saved_items
```

### 9.4 Data Integrity Notes

- Foreign keys preserve relational consistency.
- Ownership-based constraints are enforced in authorization logic.
- Token and refresh-token records are managed separately for secure session lifecycle control.

---

## 10. Authentication, Authorization, and Security

### 10.1 Authentication Model

JobNest uses token-based authentication via Laravel Sanctum.

- After login, the backend issues an access token.
- Protected endpoints require:

```http
Authorization: Bearer {access_token}
```

### 10.2 Refresh Token Strategy

- The system supports refresh-token based re-authentication.
- Refresh token rotation is used to reduce replay risk.
- Access and refresh token expiration timestamps are returned in authentication responses.

### 10.3 Authorization Model

Authorization is policy/ownership driven and includes:

- Role-based checks (person, company, admin)
- Owner-only resource modification
- Participant-only conversation/message access
- Verified-email requirements for specific endpoints

### 10.4 Security Implementation Notes

- Sensitive operations depend on authenticated user identity from token (`$request->user()`).
- Client-supplied identifiers (such as `user_id`) are not trusted for authorization decisions.
- Validation constraints prevent malformed and incomplete requests.
- Throttle middleware is used on selected high-risk endpoints (e.g., login, token refresh).

---

## 11. Localization and Translation Strategy

JobNest supports English (`en`) and Arabic (`ar`) output behavior.

### 11.1 Language Resolution Order

1. `Accept-Language` header
2. `lang` query parameter
3. Default fallback (`en`)

### 11.2 Storage and Response Strategy

- Translatable fields are stored as JSON objects with language keys.
- API responses return the value in the resolved language context.

Example storage format:

```json
{
  "en": "Backend Engineer",
  "ar": "مهندس باك اند"
}
```

---

## 12. API Design Principles

1. **RESTful routing conventions** using resource-oriented endpoint names.
2. **Consistent JSON response structure** for success and error payloads.
3. **Clear authentication boundaries** between public and protected APIs.
4. **Validation-first request handling** using Laravel form requests.
5. **Separation of concerns** through controllers/services/resources.
6. **Language-aware responses** for bilingual client support.

---

## 13. Comprehensive API Endpoint Reference

### 13.1 Response Legend

- **Auth:** `Public`, `Auth`, `Verified`, `Owner`, `Participant`, `Admin`, or `Signed URL`
- **Request Body:** Required fields only (key examples). `N/A` means no body required.
- **Success Response:** High-level shape only.

---

### 13.2 Public Discovery Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/categories` | List all public categories. | Public | N/A | Category collection with metadata. |
| GET | `/api/categories/{category}` | Retrieve one category by identifier. | Public | N/A | Category details. |
| GET | `/api/jobs` | List active jobs with filtering support. | Public | N/A | Job collection. |
| GET | `/api/jobs/{job}` | Retrieve details for one active job. | Public | N/A | Job details. |
| GET | `/api/courses` | List published courses. | Public | N/A | Course collection. |
| GET | `/api/courses/{course}` | Retrieve one course. | Public | N/A | Course details. |
| GET | `/api/courses/{course}/reviews` | List course reviews. | Public | N/A | Review collection. |
| GET | `/api/service-requests` | List open service requests. | Public | N/A | Service request collection. |
| GET | `/api/service-requests/{serviceRequest}` | Retrieve service request details. | Public | N/A | Service request details. |
| GET | `/api/users/{user}/portfolio` | Retrieve public portfolio for a user. | Public | N/A | Portfolio entries. |

---

### 13.3 Authentication and Account Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `/api/auth/register/step-1` | Create person account and start onboarding. | Public | `name`, `email`, `password`, other onboarding fields | User + token payload. |
| POST | `/api/auth/register/company` | Register company account. | Public | Company registration fields | User + token payload. |
| POST | `/api/auth/login` | Authenticate with credentials. | Public | `email`, `password`, optional `device_name` | Access/refresh tokens + user resource. |
| POST | `/api/auth/google/login` | Authenticate using Google identity flow. | Public | Google credential payload | Token payload + user resource. |
| POST | `/api/auth/refresh-token` | Exchange refresh token for new tokens. | Public | `refresh_token`, optional `device_name` | New token payload. |
| POST | `/api/auth/forgot-password` | Start password reset via OTP. | Public | Email or identifier fields | Confirmation message. |
| POST | `/api/auth/verify-reset-otp` | Validate OTP code for password reset. | Public | Reset OTP fields | Confirmation message. |
| POST | `/api/auth/resend-reset-otp` | Resend password reset OTP. | Public | Email or identifier fields | Confirmation message. |
| POST | `/api/auth/reset-password` | Complete password reset. | Public | Reset token/OTP + new password fields | Confirmation message. |
| GET | `/api/auth/email/verify/{id}/{hash}` | Verify email from signed URL. | Signed URL | N/A | Verification status payload. |
| POST | `/api/auth/register/step-2` | Continue person onboarding profile data. | Auth | Profile step-2 fields | Updated user profile state. |
| POST | `/api/auth/register/step-3` | Complete person onboarding. | Auth | Profile step-3 fields | Updated user profile state. |
| POST | `/api/auth/email/verification/send` | Send verification email. | Auth | N/A | Confirmation message. |
| POST | `/api/auth/email/verification/resend` | Resend verification email. | Auth | N/A | Confirmation message. |
| GET | `/api/auth/email/verification-status` | Check current email verification state. | Auth | N/A | `email_verified` status payload. |
| GET | `/api/auth/me` | Retrieve authenticated user details. | Auth | N/A | User resource. |
| POST | `/api/auth/change-password` | Change account password. | Verified | Current and new password fields | Confirmation message. |
| GET | `/api/auth/sessions` | List active sessions/devices. | Verified | N/A | Session list with current token marker. |
| DELETE | `/api/auth/sessions/{sessionId}` | Revoke one active session/device. | Verified | N/A | Confirmation message. |
| POST | `/api/auth/logout` | Revoke current session token. | Auth | N/A | Confirmation message. |
| POST | `/api/auth/logout-all` | Revoke all active user sessions. | Auth | N/A | Revocation summary payload. |

---

### 13.4 Profile and User Asset Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/auth/profile` | Retrieve authenticated user profile. | Auth | N/A | Profile payload (person/company structure). |
| PUT | `/api/auth/profile` | Update authenticated user profile. | Auth | Profile update fields | Updated profile payload. |
| GET | `/api/auth/user-documents` | List user documents (CV/certificates). | Auth | N/A | Document collection. |
| POST | `/api/auth/user-documents` | Upload a user document. | Auth | Multipart document fields | Created document metadata. |
| DELETE | `/api/auth/user-documents/{user_document}` | Delete one owned document. | Owner | N/A | Confirmation message. |

---

### 13.5 User Skills, Languages, and Interests Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/auth/user-skills` | List skills linked to current user. | Auth | N/A | User skill collection. |
| POST | `/api/auth/user-skills` | Add or update user skills. | Auth | Skill assignment payload | Updated user skill collection. |
| DELETE | `/api/auth/user-skills/{user_skill}` | Remove skill from user profile. | Owner | N/A | Confirmation message. |
| GET | `/api/auth/user-languages` | List user languages. | Auth | N/A | User language collection. |
| POST | `/api/auth/user-languages` | Add or update user languages. | Auth | Language assignment payload | Updated user language collection. |
| DELETE | `/api/auth/user-languages/{user_language}` | Remove language from user profile. | Owner | N/A | Confirmation message. |
| GET | `/api/auth/user-interests` | List user interests. | Auth | N/A | User interest collection. |
| POST | `/api/auth/user-interests` | Add or update user interests. | Auth | Interest assignment payload | Updated user interest collection. |
| DELETE | `/api/auth/user-interests/{user_interest}` | Remove interest from user profile. | Owner | N/A | Confirmation message. |

---

### 13.6 Reference Data Endpoints (Skills, Languages, Interests, Categories)

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/auth/skills` | List skill catalog entries. | Auth/Public by policy | N/A | Skill collection. |
| POST | `/api/auth/skills` | Create skill catalog item. | Admin | Skill fields | Created skill resource. |
| GET | `/api/auth/skills/{skill}` | Retrieve one skill. | Auth/Public by policy | N/A | Skill resource. |
| PUT/PATCH | `/api/auth/skills/{skill}` | Update a skill item. | Admin | Updated skill fields | Updated skill resource. |
| DELETE | `/api/auth/skills/{skill}` | Delete a skill item. | Admin | N/A | Confirmation message. |
| GET | `/api/auth/languages` | List language catalog entries. | Auth/Public by policy | N/A | Language collection. |
| POST | `/api/auth/languages` | Create language catalog item. | Admin | Language fields | Created language resource. |
| GET | `/api/auth/languages/{language}` | Retrieve one language. | Auth/Public by policy | N/A | Language resource. |
| PUT/PATCH | `/api/auth/languages/{language}` | Update language item. | Admin | Updated language fields | Updated language resource. |
| DELETE | `/api/auth/languages/{language}` | Delete language item. | Admin | N/A | Confirmation message. |
| GET | `/api/auth/interests` | List interest catalog entries. | Auth/Public by policy | N/A | Interest collection. |
| POST | `/api/auth/interests` | Create interest catalog item. | Admin | Interest fields | Created interest resource. |
| GET | `/api/auth/interests/{interest}` | Retrieve one interest. | Auth/Public by policy | N/A | Interest resource. |
| PUT/PATCH | `/api/auth/interests/{interest}` | Update interest item. | Admin | Updated interest fields | Updated interest resource. |
| DELETE | `/api/auth/interests/{interest}` | Delete interest item. | Admin | N/A | Confirmation message. |
| POST | `/api/auth/categories` | Create category. | Admin | Category fields | Created category resource. |
| PUT/PATCH | `/api/auth/categories/{category}` | Update category. | Admin | Updated category fields | Updated category resource. |
| DELETE | `/api/auth/categories/{category}` | Delete category. | Admin | N/A | Confirmation message. |

---

### 13.7 Jobs and Applications Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `/api/jobs` | Create a job posting. | Company/Auth | Job payload (`title`, `description`, `location`, etc.) | Created job resource. |
| PUT | `/api/jobs/{job}` | Update owned job posting. | Owner/Company | Updated job fields | Updated job resource. |
| DELETE | `/api/jobs/{job}` | Remove owned job posting. | Owner/Company | N/A | Confirmation message. |
| GET | `/api/jobs/{job}/applications` | List applications for a job owner/company. | Owner/Company | N/A | Application collection. |
| POST | `/api/jobs/{job}/applications` | Submit job application as person user. | Person/Auth | `cv_document_id`, `cover_letter`, language field | Created application resource. |
| GET | `/api/applications/{application}` | Retrieve one application. | Owner or Applicant | N/A | Application details. |
| PUT | `/api/applications/{application}` | Update application status/details. | Owner/Company | Status/update payload | Updated application resource. |
| DELETE | `/api/applications/{application}` | Withdraw or remove application. | Applicant/Owner | N/A | Confirmation message. |
| GET | `/api/auth/my-applications` | List authenticated user applications. | Auth | N/A | Application collection. |

---

### 13.8 Conversations and Messages Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/conversations` | List user conversations. | Auth | N/A | Conversation collection. |
| POST | `/api/conversations` | Create direct or context-bound conversation. | Auth | Conversation initialization payload | Created conversation resource. |
| GET | `/api/conversations/{conversation}` | Retrieve one conversation. | Participant | N/A | Conversation details. |
| GET | `/api/conversations/{conversation}/messages` | List messages in conversation. | Participant | N/A | Message collection. |
| POST | `/api/conversations/{conversation}/messages` | Send message to conversation. | Participant | Message body (`body`, `message_type`, optional file data) | Created message resource. |

---

### 13.9 Chatbot Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/chatbot/conversations` | List chatbot conversations for user. | Auth | N/A | Chatbot conversation collection. |
| POST | `/api/chatbot/conversations` | Create/reuse chatbot conversation thread. | Auth | Optional conversation init payload | Conversation descriptor. |
| GET | `/api/chatbot/conversations/{conversation_id}` | Retrieve one chatbot conversation. | Auth/Owner | N/A | Conversation details. |
| GET | `/api/chatbot/conversations/{conversation_id}/messages` | List chatbot messages in thread. | Auth/Owner | N/A | Message collection. |
| POST | `/api/chatbot/conversations/{conversation_id}/messages` | Submit prompt and get AI reply. | Auth/Owner | `body`, optional `source_language`, optional `top_n` | User message + assistant message payload. |

---

### 13.10 AI Gateway and Recommendation Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/ai/health` | Check AI integration availability. | Auth | N/A | Health status payload. |
| POST | `/api/ai/recommendations` | Get generic recommendations for user context. | Auth | Recommendation input fields (optional personalization overrides) | Recommendation list payload. |
| POST | `/api/ai/recommendations/realtime` | Get realtime recommendation output based on profile-like inputs. | Auth | `user_skills`, `cv_summary`, `user_location`, `experience_years`, `preferred_job_type`, `expected_salary_egp`, `top_n` (optional) | Realtime recommendation payload. |
| POST | `/api/ai/courses/recommend` | Get course recommendations for authenticated user. | Auth | Optional `user_name`, optional `top_n` | Course recommendation payload. |
| GET | `/api/ai/users/search` | Search users via AI gateway service. | Auth | N/A | Matched user summaries. |
| GET | `/api/ai/users/{user}` | Retrieve AI gateway user details. | Auth | N/A | User AI data payload. |
| GET | `/api/ai/jobs` | Retrieve jobs through AI gateway endpoint. | Auth | N/A | Job list payload. |
| GET | `/api/ai/jobs/{job}/score` | Get AI score for one job relative to user/context. | Auth | N/A | Score and scoring metadata. |
| GET | `/api/ai/courses` | Retrieve courses through AI gateway endpoint. | Auth | N/A | Course list payload. |

---

### 13.11 Courses, Enrollments, and Reviews Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `/api/courses` | Create a course. | Auth | Course payload | Created course resource. |
| PUT | `/api/courses/{course}` | Update owned course. | Owner | Updated course payload | Updated course resource. |
| DELETE | `/api/courses/{course}` | Delete owned course. | Owner | N/A | Confirmation message. |
| POST | `/api/courses/{course}/enrollments` | Enroll authenticated user in course. | Auth | Enrollment payload if required | Enrollment resource. |
| GET | `/api/courses/{course}/enrollments` | List enrollments for provider/owner. | Owner | N/A | Enrollment collection. |
| GET | `/api/course-enrollments` | List authenticated user enrollments. | Auth | N/A | Enrollment collection. |
| PUT | `/api/course-enrollments/{courseEnrollment}` | Update enrollment state (owner/provider). | Owner/Provider | Enrollment update fields | Updated enrollment resource. |
| POST | `/api/courses/{course}/reviews` | Create review for enrolled user. | Auth/Enrolled | Review fields (`rating`, `comment`, etc.) | Created review resource. |
| PUT | `/api/course-reviews/{courseReview}` | Update own review. | Review Owner | Review update fields | Updated review resource. |
| DELETE | `/api/course-reviews/{courseReview}` | Delete own review. | Review Owner | N/A | Confirmation message. |
| GET | `/api/auth/my-courses` | List courses related to authenticated user. | Auth | N/A | Course collection. |

---

### 13.12 Service Requests and Proposals Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `/api/service-requests` | Create service request. | Auth | Service request payload | Created service request resource. |
| PUT | `/api/service-requests/{serviceRequest}` | Update owned service request. | Owner | Updated fields | Updated service request resource. |
| DELETE | `/api/service-requests/{serviceRequest}` | Delete owned service request. | Owner | N/A | Confirmation message. |
| GET | `/api/service-requests/{serviceRequest}/proposals` | List proposals for service request owner. | Owner | N/A | Proposal collection. |
| POST | `/api/service-requests/{serviceRequest}/proposals` | Submit proposal to service request. | Auth | Proposal payload | Created proposal resource. |
| GET | `/api/service-proposals/{serviceProposal}` | Retrieve one proposal. | Owner or Proposer | N/A | Proposal details. |
| PUT | `/api/service-proposals/{serviceProposal}` | Update proposal status/content. | Owner or Proposer | Update payload | Updated proposal resource. |
| POST | `/api/service-proposals/{serviceProposal}/conversation` | Create conversation from proposal. | Owner or Proposer | Optional init payload | Created conversation resource. |
| GET | `/api/auth/my-service-requests` | List authenticated user service requests. | Auth | N/A | Service request collection. |

---

### 13.13 Saved Items and Notifications Endpoints

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| GET | `/api/auth/saved-items` | List all saved items for user. | Auth | N/A | Saved item collection. |
| POST | `/api/auth/saved-items` | Save a target item (job/course/service). | Auth | `type`, `target_id` | Created/updated saved item state. |
| DELETE | `/api/auth/saved-items/{type}/{targetId}` | Remove one saved item. | Auth | N/A | Confirmation message. |
| GET | `/api/auth/saved-items/check` | Check whether item is saved. | Auth | Query params (`type`, `target_id`) | Boolean-like saved state payload. |
| GET | `/api/auth/notifications` | List user notifications. | Auth | N/A | Notification collection. |
| GET | `/api/auth/notifications/unread-count` | Retrieve unread notification count. | Auth | N/A | Count payload. |
| PATCH | `/api/auth/notifications/mark-all-read` | Mark all notifications as read. | Auth | N/A | Confirmation + summary. |
| PATCH | `/api/auth/notifications/{notification}` | Mark one notification as read. | Auth | N/A | Updated notification status. |
| DELETE | `/api/auth/notifications/{notification}` | Delete one notification record. | Auth | N/A | Confirmation message. |

---

### 13.14 External AI Service Endpoint (Internal Integration)

| Method | Endpoint | Purpose | Auth | Request Body | Expected Success Response |
|---|---|---|---|---|---|
| POST | `{AI_BASE_URL}/api/chat` | Generate chatbot/recommendation output in external AI service. | Internal service-to-service | `message`, `user_id`, optional `top_n`, optional `context` | AI-generated response payload consumed by Laravel. |

---

## 14. AI Chatbot and Recommendation Integration

### 14.1 Integration Purpose

The external AI integration provides:

- Conversational assistant replies
- Job/course recommendation support
- Context-aware outputs using user history and recent messages

### 14.2 Backend Responsibilities in AI Flow

Laravel performs all integration control steps:

1. Validates request and authenticated user.
2. Persists incoming user message.
3. Builds contextual payload from recent conversation history.
4. Maps authenticated user identity to AI payload user identifier.
5. Calls external AI endpoint.
6. Persists assistant response.
7. Returns structured API response to client.

### 14.3 Example Internal AI Payload

```json
{
  "message": "Help me find a backend job.",
  "user_id": 45,
  "top_n": 5,
  "context": [
    {
      "role": "user",
      "content": "Help me find a backend job."
    }
  ]
}
```

### 14.4 Failure Modes and Controls

| Scenario | Typical Error | Backend Handling |
|---|---|---|
| Invalid AI URL or unreachable upstream | 502 / upstream failure | Controlled JSON error response |
| Missing user mapping in payload | `user_id: null` at AI side | Use authenticated user object to build payload |
| Invalid conversation access | 404 model not found or authorization failure | Restrict by ownership/participant rules |

---

## 15. Error Handling and Reliability

### 15.1 HTTP Status Policy

| Status | Meaning in JobNest |
|---|---|
| 200 | Successful read/update operation |
| 201 | Successful creation operation |
| 400 | Invalid request format |
| 401 | Missing or invalid authentication token |
| 403 | Authenticated but not authorized |
| 404 | Resource not found or inaccessible |
| 422 | Validation errors |
| 429 | Rate-limited endpoint |
| 500 | Unexpected backend error |
| 502 | External AI integration failure |

### 15.2 Standard Error Payload Style

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

### 15.3 Reliability Practices Used

- Validation-first endpoint handling
- Explicit exception-to-response mapping in integration points
- Persistent logs for debugging request failures and external service errors
- Queue infrastructure support for asynchronous operations

---

## 16. Testing and Validation Strategy

### 16.1 Testing Tools

- Postman collections for endpoint coverage
- Laravel logs for backend behavior verification
- Database inspection for persistence validation
- Manual scenario testing for workflow correctness

### 16.2 Core Functional Test Scenarios

| Scenario | Expected Outcome |
|---|---|
| Register and login flow | User and token payload returned |
| Access protected endpoint without token | `401 Unauthenticated` |
| Create and list jobs | Job persists and appears in listing |
| Apply to job | Application record is created |
| Conversation message send | Message persists and is retrievable |
| Chatbot message send | User message + AI reply returned or controlled AI error |
| Invalid resource access | `404` or `403` depending on policy |

### 16.3 API Contract Verification Checklist

- Required headers set (`Accept`, `Content-Type`, `Authorization` when needed)
- Request schema matches validation rules
- Response codes align with endpoint contract
- Error payloads remain consistent across modules

---

## 17. Deployment and Operations

### 17.1 Deployment Platform

The backend and AI service are deployed on Railway.

### 17.2 Required Runtime Components

- PHP runtime and Composer dependencies
- MySQL database
- Environment variable configuration
- Laravel migration execution
- Queue worker for queued workloads (where applicable)

### 17.3 Environment Variables (Core)

```env
APP_NAME=JobNest
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-backend-url.com

DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

AI_BASE_URL=https://your-ai-service-url.com
EXTERNAL_AI_BASE_URL=https://your-ai-service-url.com

MAIL_MAILER=smtp
QUEUE_CONNECTION=database
```

### 17.4 Configuration Refresh Commands

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

## 18. Limitations and Current Constraints

1. No built-in payment integration.
2. No real-time socket chat implementation.
3. No administrative UI dashboard in current scope.
4. AI output quality depends on external AI service availability and behavior.
5. Advanced analytical dashboards are not included in this phase.

---

## 19. Future Enhancements

1. Real-time chat using WebSockets.
2. AI-assisted CV parsing and profile enrichment.
3. Advanced recommendation ranking and explainability.
4. Company-side analytics dashboards.
5. Payment support for premium course/service workflows.
6. Enhanced interview scheduling and tracking workflows.
7. Push notification channels and richer notification delivery strategies.

---

## 20. Conclusion

JobNest demonstrates a complete backend/API implementation for a multi-domain career platform using Laravel and MySQL. The project integrates authentication, profile enrichment, job and course workflows, service marketplace operations, messaging, notifications, and external AI-assisted interactions under one RESTful architecture.

From a graduation project perspective, JobNest provides evidence of practical competency in backend architecture, secure API design, relational database modeling, external service integration, and deployment-readiness for production-like environments.

---

## 21. Possible Discussion Questions and Answers

### Q1. Why was Laravel chosen for this project?
**Answer:** Laravel provides a mature ecosystem for API development, including routing, middleware, validation, authentication (Sanctum), ORM support, queue handling, and modular code organization. This reduced boilerplate and improved maintainability.

### Q2. Why is token-based authentication appropriate for JobNest?
**Answer:** JobNest is an API-first system consumed by web/mobile clients. Token-based authentication is stateless, scalable, and suitable for multi-client environments. Sanctum also integrates naturally with Laravel request lifecycle and authorization checks.

### Q3. How do you ensure that one user cannot access another user's protected data?
**Answer:** Protected endpoints require valid tokens, and authorization is enforced through ownership and participant checks (e.g., conversation participants, resource owners, role restrictions). Sensitive logic depends on `$request->user()` identity.

### Q4. What is the difference between authentication and authorization in your implementation?
**Answer:** Authentication confirms who the user is (valid token), while authorization checks what the user is allowed to do (role, ownership, participation, verification status).

### Q5. How does the chatbot integration work technically?
**Answer:** The backend validates and stores the user message, builds recent context, sends a structured payload to the external AI service, stores the returned assistant message, and responds to the client with persisted conversation data.

### Q6. Why should backend code avoid trusting `user_id` sent by the client?
**Answer:** Client-side payloads can be manipulated. The secure approach is to derive identity from the authenticated token and use server-side authorization logic for all sensitive operations.

### Q7. How do you handle AI service failures without breaking the platform?
**Answer:** Integration errors are mapped to controlled API responses (e.g., 502), and the backend preserves consistent response structures, enabling clients to handle failures gracefully.

### Q8. What database design choices were important in JobNest?
**Answer:** Many-to-many relationships are normalized through pivot tables (skills, languages, interests), workflow entities are separated by module, and token/session tables are isolated for secure authentication lifecycle management.

### Q9. How was API quality validated?
**Answer:** API quality was validated using Postman collections, manual workflow testing, log analysis, and database state verification after key operations.

### Q10. What would you prioritize if this project moved to production scale?
**Answer:** Priority areas include automated test coverage expansion, centralized monitoring/alerting, queue scaling, caching optimization, and tighter observability around external AI dependencies.

### Q11. How does localization influence API design in this project?
**Answer:** Localization is handled at API level via `Accept-Language` and fallback logic. Translatable content is stored in multilingual format while responses return the active language value to simplify client rendering.

### Q12. What is the most important backend engineering lesson from this project?
**Answer:** A clean separation between validation, business logic, persistence, and integrations significantly improves reliability, debugging speed, and long-term extensibility.

---

**End of Document**
