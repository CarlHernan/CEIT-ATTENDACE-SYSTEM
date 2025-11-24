# CEIT Digital Attendance - Schema Overview

z## Core Entities

- **roles**
  - `id`, `name`, `slug` (`student`, `officer`, `lsg_officer`, `admin`), timestamps
  - Used for portal access/authorization.

- **users**
  - Identity: `id`, `name`, `email`, `email_verified_at`, `password`
  - Academic: `id_number`, `course`, `section` (nullable), `year_level`, `department` (always `CEIT`)
  - QR linkage: `qr_raw_text` (nullable, unique)
  - Roles/flags: `role_id` (FK → roles), `is_society_officer` (bool), `is_lsg_officer` (bool)
  - Timestamps

- **societies**
  - `id`, `name`, `slug`, `abbreviation`, timestamps
  - Seeds: PSITS, PICE, ICPEP, JIECEP, PSABE.

- **society_user** (pivot)
  - `user_id`, `society_id`, `position` (e.g., Member, Officer), timestamps
  - A user can belong to multiple societies; position stored per society.

- **events**
  - Core: `id`, `title`, `description`, `start_at`, `end_at`, `location`
  - Ownership: `society_id` (FK → societies), `created_by` (FK → users)
  - Scope/meta:
    - `type` (`society`, `ceit`, `lsg`, `meeting`)
    - `template` (`GA`, `Meeting`, `Seminar`, `Formal Event`, etc.)
    - `attendance_mode` (`qr`, `manual`, `hybrid`)
    - `audience` (`society`, `year_specific`, `all`, `officers_only`, `society_officers`, `lsg_officers`, `lsg_and_society_officers`)
    - `audience_years` (CSV years when `year_specific`)
    - `visibility` (`students`, `officers`, `all`)
    - `is_ceit_wide` (bool)
    - `require_timeout` (bool)
    - `status` (`active`, `cancelled`, etc.)
  - Timestamps

- **attendance_records**
  - `id`, `event_id` (FK → events), `user_id` (FK → users)
  - Timing: `time_in`, `time_out` (nullable)
  - `method` (`qr`, `manual`, `hybrid`)
  - `recorded_by` (FK → users, the officer who recorded)
  - Timestamps

## Key Relationships

- User ↔ Role: `users.role_id` → `roles.id`
- User ↔ Society: many-to-many via `society_user` with `position`
- Event ↔ Society: many-to-one via `events.society_id`
- Event ↔ Creator: `events.created_by` → `users.id`
- Event ↔ Attendance: one-to-many `attendance_records.event_id`
- User ↔ Attendance: one-to-many `attendance_records.user_id`
- Attendance ↔ Recorder: `attendance_records.recorded_by` → `users.id`

## Audience/Visibility Logic (effective in controllers/UI)

- Students see events only if:
  - `audience` is `society` or `year_specific` and their society/year matches, or
  - `audience` is `society_officers` and they are flagged `is_society_officer`, or
  - `audience` is `lsg_officers` and they are flagged `is_lsg_officer`, or
  - `audience` is `lsg_and_society_officers` and they are any officer, or
  - `audience` is `officers_only` and they are any officer flag, or
  - `audience` is `all` and event is `is_ceit_wide`.
  - And `visibility` allows students (`students` or `all`).
- Officers see only their society’s events; LSG officers see CEIT/LSG types.

## Seeds (summary)

- Roles: student, officer, lsg_officer, admin
- Societies: PSITS, PICE, ICPEP, JIECEP, PSABE
- Users: admin, generic officer, LSG officer, per-society officers, sample students (Carl, John) with `section`, `qr_raw_text`
- Events: sample GA/meetings/CEIT forum in `EventSeeder`
