# Ideas backlog

Ideas for future features, collected as they come up. When an idea is taken into work, it goes
through the Spec-Kit flow (`speckit-specify` → `specs/NNN-…/`) and its entry here gets the status
and a link to the spec.

**Statuses:** 💡 new · 💬 discussing · 🚧 in progress · ✅ done · ❌ rejected

| ID | Idea | Status | Added | Spec |
|---|---|---|---|---|
| [IDEA-001](#idea-001--extended-statistics-for-admins) | Extended statistics for admins | 💡 new | 2026-11-22 | — |

---

## IDEA-001 — Extended statistics for admins

**Status:** 💡 new · **Added:** 2026-11-22

Admins currently have `GET /api/system/user-stats` (user counts, new users, active in the last hour,
users with/without cars), season finance stats, the registrations count, the monthly spotting stats
in Telegram and the daily digest message stats. Candidates below are computable from data already in
the database unless noted otherwise.

### Priority candidates
- [ ] Season debtors: active members with no `finances` record since the season start; share of paid members; average contribution.
- [ ] Unmatched Monobank payments: `mono_transactions` stuck in `pending`/`rejected` or with `user_id = null`.
- [ ] Incomplete profiles: no `telegram_id` (cannot log in by code, gets no bot messages), no car, no city, no photo, no birth date.
- [ ] Members' cars listed for sale: `external_cars` with `user_id` set and `is_sold = false`.

### App activity (`personal_access_tokens`)
- [ ] DAU / WAU / MAU and DAU/MAU ratio (currently only "last hour").
- [ ] Platform and app version distribution (`app_platform`, `app_version`).
- [ ] Dormant accounts: never logged in to the app, or no activity for 60+ days.

### Telegram chat
- [ ] Chat activity trend by day/week (`daily_digests.total_messages` is already stored daily).
- [ ] Silent members: have `telegram_id` but no messages in the main chat for N days.
- [ ] Activity by hour / weekday (best time to post announcements).

### Push notifications
- [ ] Share of users with push enabled (`fcm_tokens.active`), per platform.
- [ ] Push open rate: `read_at` / total in `notifications`, per `type`.

### Club composition
- [ ] Fleet: distribution by generation, model, colour; count of non-TT members (`is_tt = false`).
- [ ] Age (`birth_date`) and club tenure (`club_entry_date`), upcoming membership anniversaries.
- [ ] Registration funnel: submitted → approved, average time to approval (`registrations`).
- [ ] Churn: members deactivated over a period (dates from `audits`).

### Spotting and draws
- [ ] Spotting heatmap from `mentions.latitude` / `longitude`.
- [ ] Most caught members (`caught_user_id`) and cars never spotted.
- [ ] Draws: unique participants, most frequent participants, share of manually added ones.

### Market (Autoria)
- [ ] TT prices: average price by generation/year, sold per month, average time to sale.

### Needs new data collection
- Event attendance: participation in events is not recorded (would need RSVP).
- Purchase requests: they are only sent to Telegram, not stored.

### Notes for implementation
- `SystemController::systemUserStats` returns `response()->json(...)` directly instead of the `success()` envelope — align it if this endpoint is extended.
- `MentionEloquent::getMostActiveDayWeek` selects `day` but reads `$result->date` (would always return today's weekday); currently unused.
