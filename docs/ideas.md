# Ideas backlog

Ideas for future features, collected as they come up. When an idea is taken into work, it goes
through the Spec-Kit flow (`speckit-specify` → `specs/NNN-…/`) and its entry here gets the status
and a link to the spec.

**Statuses:** 💡 new · 💬 discussing · 🚧 in progress · ✅ done · ❌ rejected

| ID | Idea | Status | Added | Spec |
|---|---|---|---|---|
| [IDEA-001](#idea-001--extended-statistics-for-admins) | Extended statistics for admins | 💡 new | 2026-11-22 | — |
| [IDEA-002](#idea-002--telegram-alert-when-the-autoria-package-runs-out) | Telegram alert when the AUTO.RIA package runs out | 🚧 in progress | 2026-11-22 | `specs/009-autoria-package-alert/` (branch `009-autoria-package-alert`) |

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

---

## IDEA-002 — Telegram alert when the AUTO.RIA package runs out

**Status:** 🚧 in progress · **Added:** 2026-11-22 · **Spec:** `specs/009-autoria-package-alert/` (branch `009-autoria-package-alert`)

The Autoria car sync uses the free AUTO.RIA developer package (1 000 requests, 30 per hour). When it
runs out, the sync silently stops working until someone renews the package by hand. AUTO.RIA has no
API to renew, activate or buy a package; it can only be done in the cabinet on developers.ria.com.

### What
When `SearchCarsJob` or `SyncCarDetailJob` gets a "package limit exhausted" response from AUTO.RIA,
send a message to the system-errors chat (`EnumTelegramEvents::SYSTEM_ERRORS`) with a
**"Renew package"** button that opens https://developers.ria.com/payment/. The package is then
renewed by hand in the cabinet.

### Requirements
- Detect package exhaustion in `SearchCarsJob` and `SyncCarDetailJob`.
- An exhausted package comes back as **HTTP 403** (see the production log below). The hourly limit (30 requests/hour) is **429**: it does not alert, the job is just released as it is now. A 403 can also mean an invalid or disabled API key; both are fixed in the cabinet, so the alert covers both.
- On 403 the job fails at once (`$this->fail()`) instead of retrying: retries cannot succeed until the package is renewed, they only waste calls and fill `failed_jobs`.
- Log the response body on 403 and 429 (today `SearchCarsJob` logs only the status), so the exact AUTO.RIA error text is known next time.
- No spam: at most one message per day (a lock in the cache), even when many jobs fail at once.
- Message template in `resources/lang/uk/telegram.php`; sent through a Notification and `TelegramChannel`. The button goes through `buttons` in `TelegramMessagePayload` (URL buttons are already supported).
- Tests: the alert is sent once when the package is exhausted, and not sent on the hourly limit.

### Evidence: production log, 2026-11-21
- `SearchCarsJob` got `Search API невдало: 403` at 06:00 and on both retries at 07:00 and 08:00 (`$tries = 3`, `$backoff = 3600`), then the same at 15:00, 16:00 and 17:00. Six failed calls that day and no alert.
- The response body was not logged, so the exact AUTO.RIA message is unknown.
- There are no `SyncCarDetailJob` entries that day: the search failed on page 0, so nothing was dispatched. That `/auto/info` also answers 403 for an exhausted package is an assumption, not confirmed yet.

### Notes
- After renewal nothing needs to be restarted by hand: the next scheduled sync (06:00 / 15:00) picks up new and stale listings.
- Out of scope: a "Restart sync" button right in Telegram. The bot does not handle button presses (`callback_query`) yet; that is separate work.
- Related: the package runs out mostly because every active listing is re-fetched every 3 days (`stale(3)` in `SearchCarsJob`) and the sync runs twice a day. Roughly `N/3 + 2·⌈N/100⌉ + new` requests per day for N active listings, which is about 1 600 per month at N ≈ 150. Raising the stale threshold to 7–10 days and syncing once a day would make the free package last.
- Related bug: listings can be deactivated by mistake. Each search page touches `updated_at` of the listings it finds; after the last page every listing with `updated_at` older than 60 minutes is deactivated (`SearchCarsJob.php:171`). If a 429 delays page 1 by 60 minutes, the listings from page 0 get deactivated although they are still live. With ~150 listings there are two pages, so this can happen.
