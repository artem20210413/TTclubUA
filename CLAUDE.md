# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

TT-Club: the backend for an Audi TT owners' club. Laravel 11 (PHP 8.2+) serves a JSON API (Sanctum tokens) to a mobile app, a small public Blade/Livewire site (club registration form at `/`, `/register`), and a Telegram bot. Jetstream/Fortify scaffolding is installed but the real product surface is `routes/api.php`. Code comments and user-facing strings are mostly Ukrainian (some Russian).

## Commands

Local dev runs with Laravel Sail (root `docker-compose.yml`: service `laravel.test` on :80 + `mysql` on :3306; the MySQL init script also creates the `testing` database). Run from the repo root:

```bash
vendor/bin/sail up -d                 # start (vendor/bin/sail down to stop)
vendor/bin/sail artisan migrate
vendor/bin/sail artisan db:seed
vendor/bin/sail composer install
vendor/bin/sail npm install && vendor/bin/sail npm run build
vendor/bin/sail shell                 # shell inside laravel.test
```

Tests (Pest 3, `tests/Feature` uses `RefreshDatabase` against the `testing` DB from `phpunit.xml`):

```bash
vendor/bin/sail pest                                              # whole suite
vendor/bin/sail pest tests/Feature/PurchaseRequestTest.php        # one file
vendor/bin/sail pest --filter="still returns success"             # one test
```

Testing gotchas:
- **Never run tests while a config cache exists.** A local `bootstrap/cache/config.php` (git-ignored, may be stale) makes Laravel ignore the `phpunit.xml` env, so `RefreshDatabase` would wipe the dev `laravel` database instead of `testing`. Check with `ls bootstrap/cache/config.php`; if it exists, either run `vendor/bin/sail artisan optimize:clear` (ask the user first) or bypass the caches without deleting them:
  `docker compose exec -T -e APP_CONFIG_CACHE=/tmp/nocfg.php -e APP_ROUTES_CACHE=/tmp/noroutes.php laravel.test php vendor/bin/pest`
  A stale route/config cache also means the running app ignores route/config edits until it is cleared.
- Migrations are MySQL-specific; tests cannot run on SQLite. Tests need the Docker MySQL (`testing` database).
- Several Jetstream scaffold tests (teams, API tokens, email verification, etc.) fail on `main` already; compare against `main` before attributing failures to a change.

An older alternative stack lives in `_docker/` (`cd _docker && ./srv up`, `./srv artisan …`; nginx on :8080, MySQL on :9001, PHP in `php-fpm` at `/var/www`).
Lint/format: `vendor/bin/sail pint` (Laravel preset; the constitution requires Pint-clean code, e.g. `! $x` with a space).

Deploy: `php artisan deploy` (`app/Console/Commands/DeployCommand.php`) does `git pull origin main`, `composer install`, `migrate`, `optimize`. Only `main` is ever deployed.

## Architecture

**API layer.** Routes in `routes/api.php`, guarded by `auth:sanctum` and `role:<name>` (spatie/laravel-permission; roles in `EnumUserRoles`). Controllers return through global helpers in `app/Helper/ResponseHelpers.php`: `success($message, $data)` → `{message, data}` and `error(Throwable)` → `{message, error}` with status from `ApiException::getStatus()`. Business errors are thrown as `App\Http\Controllers\Api\ApiException(message, code, httpStatus)` and caught in the controller. Custom middleware aliases are registered in `bootstrap/app.php` (`only.ua`, `params.throttle`, `telegram.webhook`, `app.attributes`).

**`app/Eloquent/*Eloquent.php`** are static query/command helpers per aggregate (e.g. `UserEloquent::updateByTg`, `RegistrationEloquent`), not Eloquent models. Models live in `app/Models` and commonly use spatie media-library collections (`EnumTypeMedia`) and owen-it auditing.

**Telegram (bot + outgoing messages).** Two directions:
- *Incoming*: `POST /api/telegram/webhook` (secret header checked by `VerifyTelegramWebhook`) → `TelegramController` wraps the payload in DTOs (`app/Services/Telegram/Dto`). Private chats go to `TelegramCommandHandler` → `Commands/TelegramCommands` (command enum + `Set/Command*` classes; two-step commands like `/setpassword` use `TelegramCommandState` in cache plus the last logged message). Group events (join/leave) go to `TelegramActionPublicHandler`, which only acts in chats allowed by `EnumTelegramEvents::MEMBERSHIP` / `NEW_MEMBER_WELCOME` via `hasChat()`. Every incoming update is logged to `telegram_messages` (`TelegramLoggerEloquent::createIn`), which the daily digest later reads. The exception is `callback_query` (inline-button presses): `TelegramController` hands it to `TelegramCallbackHandler` and returns before any logging; the user is found only by `callback_query.from.id`. That handler is the one place that calls `Telegram\Bot\Api` directly (`answerCallbackQuery`, `editMessageReplyMarkup`, `editMessageText`), because these are replies to the pressed message, not outgoing sends; it always answers the press and never throws.
- *Outgoing*: always `EnumTelegramEvents::X->getIds()` → `TelegramRecipients::routes()` → a `Notification` returning `TelegramMessagePayload` → `App\Notifications\Channels\TelegramChannel`. Never call `Telegram\Bot\Api` directly from business code. With `APP_ENV=local`, `getIds()` redirects every destination except `TEST`/`SYSTEM_ERRORS` to the test chat; don't bypass this. `TelegramChannel` swallows send errors (logs + alerts the system-errors chat), so callers never see Telegram failures. Messages use HTML parse mode; templates live in `resources/lang/uk/telegram.php` and are rendered with `TelegramBotHelpers::renderTemplate()`. Chat IDs come from `config/telegram.php` `chats.*` (env `TELEGRAM_CHAT_*`).

**Login by Telegram code.** `POST /api/login/tg/send-code` stores a code in cache (`User::generateAndStoreLoginCode`) and sends it to the user's `telegram_id`; `POST /api/login/tg/verify` checks it with a per-code failed-attempt limit (`config('auth.login_code.max_attempts')`). A user gets a `telegram_id` only by sharing their own contact with the bot (`UserEloquent::updateByTg`). Deactivated users are refused at both steps. Every successful login (password or code) sends `LoginSuccessNotification` with a "Це не я" button (`callback_data` `sess:ask` → `sess:yes`/`sess:no`); "Так" deletes all Sanctum tokens and the pending code of every user with that `telegram_id`.

**Background work.** Scheduler entries are in `routes/console.php` (Autoria car sync, daily AI digest at 22:00, birthdays, pin expiry sweep, Monobank webhook check, pruning). Jobs in `app/Jobs`. The daily digest (`SendDailyDigestJob` + `app/Services/Digest/*`) summarizes the day's main-chat messages via Gemini (`app/Services/Gemini`), is idempotent per day via a `daily_digests` claim row, and falls back to birthday greetings in `failed()`. Creating an `App\Models\Notification` row triggers an FCM push from its `booted()` hook (`FcmService::pushNotification`, `app/Services/Fcm`). Monobank payments arrive at `/api/webhook/monobank` (`FinanceController`, `MonobankService`).

## Project conventions & tooling

- **Keep the README in sync.** Any significant change must update `README.md` **and** its Ukrainian translation `README.uk.md` in the same piece of work. Significant means a new feature or module, a new or changed integration, environment variable or config key, a changed setup/run/test/deploy step, a new scheduled task, or a changed architecture convention. Small bug fixes and internal refactors don't need it. Mention the README update in the final summary.

- **House-convention skills** in `.claude/skills/`: `tt-api-endpoint`, `tt-eloquent-model`, `tt-queued-job`, `tt-telegram-notify`. Load the matching one before adding or changing an endpoint, a model, a queued job, or any Telegram sending.
- **Constitution** (`.specify/memory/constitution.md`): Simplicity & YAGNI (no speculative abstractions) and Laravel & framework conventions (thin controllers, Form Requests, use the installed packages rather than custom equivalents, Pint-clean).
- **Spec-Kit workflow**: features are developed through `speckit-*` skills, with artifacts in `specs/NNN-feature-name/` (spec, plan, tasks). The active feature is recorded in `.specify/feature.json`. Docs under `docs/` and speckit docs are mirrored with a Ukrainian `.uk.md` translation. `/tt-feature` (`.claude/skills/tt-feature`) runs the whole cycle (specify → clarify → plan → tasks → analyze → implement) with questions via AskUserQuestion and a confirmation gate before implementing; «без підтверджень» in its arguments makes it autonomous.
- A `code-reviewer` subagent (`.claude/agents/code-reviewer.md`) reviews diffs against these conventions.
