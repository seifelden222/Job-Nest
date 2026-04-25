# Authorization Audit Summary

## What Was Reviewed

This audit reviewed the current authorization architecture across the main API modules in JobNest, including:

- jobs
- applications
- conversations
- messages
- courses
- course enrollments
- course reviews
- service requests
- service proposals
- notifications
- categories
- profile-related self-service endpoints
- saved items
- skills, languages, and interests

The review covered route protection, controller authorization, form request authorization, and policy usage.

## What Was Found

### Already Correct Or Acceptable

- Authentication middleware was already applied to the authenticated API groups.
- Saved items were already scoped to the authenticated user and did not expose cross-user write access.
- Notification endpoints were already centered around the authenticated user and did not require extra ownership policies in the current architecture.
- Profile and other self-service endpoints were already structured around the authenticated user, so no broad policy rewrite was required there.

### Risks And Gaps Found

- Only one policy existed before this audit, and it was a non-standard `JobPolicy` under `App\\Policies\\Api`.
- Many important ownership and role checks lived in controllers or form requests instead of dedicated policies.
- Jobs used non-standard policy ability names such as `store` and `destroy`, which made authorization harder to reason about and easier to drift.
- Applications, conversations, messages, courses, enrollments, reviews, service requests, and service proposals had incomplete or inconsistent authorization coverage.
- Master-data write endpoints for categories, skills, languages, and interests were effectively writable by any authenticated user.
- Some authorization relied only on `auth:sanctum`, even when ownership or role checks were required.

## What Was Fixed

### Policies Added

The following policies were added under `app/Policies`:

- `ApplicationPolicy`
- `CategoryPolicy`
- `ConversationPolicy`
- `CourseEnrollmentPolicy`
- `CoursePolicy`
- `CourseReviewPolicy`
- `InterestPolicy`
- `JobPolicy`
- `LanguagePolicy`
- `ServiceProposalPolicy`
- `ServiceRequestPolicy`
- `SkillPolicy`

### Policy Registration

Policies were registered explicitly in `AppServiceProvider` to keep authorization resolution predictable.

### Controller And Request Refactors

Controllers and form requests were updated to use policy-backed authorization consistently.

The implementation now uses standard Laravel policy ability names where appropriate:

- `viewAny`
- `view`
- `create`
- `update`
- `delete`

Additional contextual abilities were added only where needed, such as:

- `createForApplication`
- `viewEnrollments`
- `viewProposals`

## Authorization Rules Now Enforced

### Jobs

- only active company users can create jobs
- only the owning company can update or delete its own jobs

### Applications

- only active person users can apply to jobs
- only the applicant or the owning company can view an application
- only the owning company can list applications for a job
- only the applicant can delete an application

### Conversations And Messages

- only authenticated active users can create conversations
- only conversation participants can view a conversation or its messages
- application-linked conversations can only be created by the applicant or the company that owns the job

### Courses

- authenticated active users can create courses
- only the course owner can update or delete a course
- only the course owner can view provider enrollment listings

### Course Enrollments

- authenticated active users can enroll
- only the course owner can update enrollment status

### Course Reviews

- authenticated active users can create reviews
- only the review owner can update or delete a review

### Service Requests

- authenticated active users can create service requests
- only the service request owner can update or delete it
- only the service request owner can list proposals for it

### Service Proposals

- authenticated active users can create proposals
- users cannot create proposals on their own service requests
- only the proposal owner or the service request owner can view a proposal
- only the proposal owner or the service request owner can interact with the proposal conversation flow already present in the project

### Categories, Skills, Languages, Interests

- list and show endpoints remain available to authenticated users where the current API already expects that
- create, update, and delete are now restricted to active admins only

## Risks Removed

- Removed broad authenticated-user write access to master data
- Removed reliance on inconsistent inline authorization checks for core business modules
- Removed non-standard and stale policy naming around jobs
- Tightened access to applications, conversations, messages, and proposals so outsiders can no longer access data they do not own or participate in

## Assumptions

- The existing project contains an `admins` table but no separate admin API authentication flow for these endpoints.
- Based on the current architecture, an API user is treated as an admin for master-data management when their authenticated user email matches an active record in the `admins` table.
- Public browsing rules for jobs, courses, and service requests were left aligned with the existing route design unless a write or ownership rule required stricter protection.
- Notifications, saved items, and self-profile endpoints were not rewritten beyond the necessary audit because their current user-scoped behavior was already acceptable in the repository.
