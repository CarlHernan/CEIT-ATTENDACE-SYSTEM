# Mobile API (LSG & Society Officers)

Base path: `/api/v1`
Auth: Bearer tokens via Laravel Sanctum (token-based).

## Auth
- `POST /login` (throttle 60/min)
  - body: `email`, `password`, optional `device_name`
  - roles allowed: `officer`, `lsg_officer`
  - returns: `{ data: { token, user { id, name, email, role, is_society_officer, is_lsg_officer, societies[] } } }`
- `POST /logout` (auth:sanctum, throttle 300/min)
- `GET /me` (auth:sanctum)

## Events (view-only)
- `GET /events` (auth:sanctum)
  - filters: `search`, `from`, `to`, `status`, `per_page`
  - Officers: only their society’s events. LSG: only `type` ceit/lsg or `is_ceit_wide`.
- `GET /events/{event}` (auth:sanctum) respects the same visibility rules.

## Attendance
- `GET /events/{event}/attendance` (auth:sanctum)
  - filters: `course`, `year_level`, `method`, `search`, `per_page`
- `POST /events/{event}/attendance` (auth:sanctum)
  - body: `identifier` (qr text or id number), `direction` (`in`|`out`), `method` (`qr`|`manual`|`hybrid`)
  - upserts a single record; returns `already_recorded` when a repeat scan occurs.
- `GET /events/{event}/stats` (auth:sanctum)
  - returns totals and counts by course/year.

## Rate limits
- Unauthenticated: 60 requests/min
- Authenticated: 300 requests/min (adjust as needed)

## CORS
Configure `CORS_ALLOWED_ORIGINS` (comma-separated) in `.env`. Defaults include:
`https://preview.flutterflow.app,https://app.flutterflow.io`
Add your current ngrok URL (e.g., `https://abcd1234.ngrok-free.app`). Avoid `*` in production.
