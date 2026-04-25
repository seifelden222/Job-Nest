# Saved Items Module Summary

## What Was Added

- Added a unified saved items module using one database table: `saved_items`
- Added authenticated API endpoints to list, create, remove, and check saved items
- Added feature tests covering the required save and favorite scenarios
- Added a `Saved Items` folder to the main Postman API collection

## Unified Save Table

Table: `saved_items`

Columns:

- `id`
- `user_id`
- `type`
- `target_id`
- `created_at`
- `updated_at`

Constraints:

- foreign key from `user_id` to `users.id`
- unique index on `user_id`, `type`, `target_id`
- index on `type`, `target_id`

## Supported Types

- `job`
- `course`
- `service_request`

## Routes Added

- `GET /api/auth/saved-items`
- `POST /api/auth/saved-items`
- `DELETE /api/auth/saved-items/{type}/{targetId}`
- `GET /api/auth/saved-items/check?type=job&target_id=1`

## Validation Rules

- `type` is required for create, check, and delete flows
- `type` must be one of: `job`, `course`, `service_request`
- `target_id` is required for create and check flows
- `target_id` must exist in the matching table for the selected `type`
- duplicate saves are rejected per user and resource using request validation plus a database unique constraint

## Response Shape

- list responses return `message`, `data`, `grouped_data`, and `filters`
- create responses return `message` and `saved_item`
- delete responses return `message`
- check responses return `message` and `data.is_saved`

## Assumptions

- Any authenticated user may save jobs, courses, and service requests
- Saving an item is independent from ownership of the original resource
- Removing a save only deletes the saved record, never the original resource
- Delete requests return `404` if the saved item does not belong to the authenticated user
