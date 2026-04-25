# JobNest Notifications Module Summary

## What Was Added

A full API notifications module was added using Laravel's existing `notifications` table and `Notifiable` trait on `User`.

The module includes:

- Notifications API endpoints for listing, unread count, mark read, mark all read, and delete.
- Structured database notification payloads for three business-critical events.
- Trigger hooks in existing flows for application status updates, new jobs, and new messages.
- Feature tests for API behavior and notification creation events.
- Postman collection updates with a dedicated Notifications folder.

## Notification Types

Three notification classes were added:

1. `ApplicationStatusUpdatedNotification`

- Trigger: company updates an application's status.
- Recipient: applicant (`application.user_id`).
- Payload action type: `application_status_updated`.

1. `NewJobPostedNotification`

- Trigger: company creates an active job.
- Recipient strategy: person users matching at least one required job skill.
- Payload action type: `job_posted`.

1. `NewMessageReceivedNotification`

- Trigger: participant sends message in a conversation.
- Recipient: other conversation participants, excluding sender.
- Payload action type: `new_message`.

## Routes Added

Under authenticated `auth:sanctum` + `auth` prefix routes:

- `GET /api/auth/notifications`
- `GET /api/auth/notifications/unread-count`
- `PATCH /api/auth/notifications/{notification}`
- `PATCH /api/auth/notifications/mark-all-read`
- `DELETE /api/auth/notifications/{notification}`

## Trigger Locations

1. Application status updates

- File: `app/Http/Controllers/Api/Applications/ApplicationController.php`
- Method: `update`
- Triggered when company changes status and status value actually changes.

1. New job posted

- File: `app/Http/Controllers/Api/Jobs/JobController.php`
- Method: `store`
- Triggered after creating an active job.
- Uses `resolveRecipientsForNewJob()` as a modular, simple matching strategy.

1. New message received

- File: `app/Http/Controllers/Api/Messages/MessageController.php`
- Method: `store`
- Triggered after message creation for all participants except sender.

## Payload Structure

All database notifications use a consistent JSON shape:

- `type`
- `title`
- `body`
- `action_type`
- `related_id`
- `related_type`
- `meta` (extra metadata)

## Authorization and Access Rules

- Notification API only works for authenticated users.
- Mark-read and delete actions resolve notifications only from the authenticated user's notification relation.
- Users cannot manipulate notifications belonging to other users.

## Assumptions

- Existing `notifications` table schema and `User` model `Notifiable` trait are correct and should be reused.
- "New jobs" notification strategy currently targets person users with overlapping skills; this is intentionally simple and modular for future expansion.
- Notification delivery uses the database channel for API consumption.
