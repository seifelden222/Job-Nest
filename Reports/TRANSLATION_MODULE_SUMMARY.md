# JobNest Translation Module Summary

## What Changed

- Added a centralized API locale middleware that resolves the active language from `Accept-Language`, then `lang`, and falls back to English.
- Added a reusable translation service layer with a swappable machine translator contract and a safe fallback translator.
- Converted selected user-facing content fields to JSON translations stored in the same tables.
- Updated create and update flows to accept `source_language` and automatically persist both `en` and `ar`.
- Updated model serialization so normal API responses return only the value for the current request language.
- Updated tests and the main Postman collection for the new request contract.

## Language Resolution

The API language is resolved in this order:

1. `Accept-Language` header
2. `lang` query parameter
3. default locale from `config/translation.php` (`en`)

The resolved locale is applied to the Laravel request lifecycle through `App::setLocale()` and exposed in responses through the `Content-Language` header.

## Translatable Tables And Fields

The following existing tables now store translated content as JSON objects with `en` and `ar` keys:

- `jobs`: `title`, `description`, `requirements`, `responsibilities`
- `courses`: `title`, `short_description`, `description`, `course_overview`, `what_you_learn`
- `service_requests`: `title`, `description`
- `categories`: `name`, `description`
- `skills`: `name`
- `interests`: `name`
- `languages`: `name`
- `service_proposals`: `message`
- `applications`: `cover_letter`
- `messages`: `body`
- `course_reviews`: `comment`

## Request Contract

Create and update requests for translatable content continue to accept flat string fields, but now also require or support:

```json
{
  "source_language": "en"
}
```

When a translatable field is present:

- `source_language` must be `en` or `ar`
- the submitted text is treated as the source value
- the missing counterpart is generated automatically

## Response Behavior

Stored JSON values are not exposed in normal API responses.

Example stored value:

```json
{
  "en": "Data Analyst",
  "ar": "محلل بيانات"
}
```

Example response behavior:

- `Accept-Language: en` returns `"Data Analyst"`
- `Accept-Language: ar` returns `"محلل بيانات"`
- invalid or missing language falls back to English

This applies to direct resource responses and nested relation payloads because translated models localize themselves during serialization.

## Fallback Behavior

- If a translator driver is unavailable or the external translation call fails, the fallback translator keeps the original text and stores it under both language keys.
- This ensures create and update flows do not fail because of translation infrastructure issues.
- The translation driver can be switched later through `config/translation.php` without changing controller or request logic.

## Assumptions

- Only Arabic and English are supported.
- Existing identity fields such as user names and company account names were left as-is.
- Fresh migrations are acceptable, so the schema was updated directly to the final JSON-column design.
