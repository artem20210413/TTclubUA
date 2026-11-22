# TT-Club

> Ukrainian version: [README.uk.md](README.uk.md)

Backend for the Ukrainian **Audi TT owners' club**. It serves the club's mobile app (JSON API), a public registration website, and a Telegram bot that lives in the club's chats.

## Features

- **Members & cars**: profiles, roles, cities, members' cars, Excel import and export of members.
- **TT market**: Audi TT listings synced from Autoria twice a day (`external-cars` API). When AUTO.RIA answers 403 (the developer package is exhausted or the API key is rejected), the sync stops without retries and the system-errors chat gets one alert a day with a "Renew package" button.
- **Registration**: public form on the website → admin review → approval into a member account.
- **"Fa-fa" mentions**: members photograph club cars on the road; the owner gets a push and a post in the club chat.
- **Finance**: membership fees per season, costs, Monobank jar integration (webhook), season statistics.
- **Club life**: events/calendar, partners and promotions, goods with purchase requests, prize draws, suggestions.
- **Telegram bot**:
  - login to the app by a one-time code sent in Telegram (not for deactivated members);
  - a login alert after every login (password or code) with the method, device, IP and time and a "🚫 Це не я — завершити всі сесії" button; after a "Так" confirmation it ends all the member's app sessions (Sanctum tokens) and the pending login code. An admin setting a member's password also ends that member's sessions;
  - linking a Telegram account by sharing your own contact; `/setpassword` and other bot commands;
  - join/leave tracking in the main club chat (activates/deactivates accounts), welcome message;
  - daily AI digest of the main chat (Google Gemini), birthdays, monthly mention stats, pinned messages;
  - system-error alerts to a dedicated chat.
- **Push notifications** via Firebase Cloud Messaging.

## Tech stack

| Area | Used |
|---|---|
| Framework | Laravel 11, PHP 8.2+ |
| Auth | Sanctum (API tokens), Jetstream/Fortify, spatie/laravel-permission (roles) |
| Data | MySQL 8, Eloquent, owen-it/laravel-auditing, spatie/laravel-medialibrary |
| Integrations | irazasyed/telegram-bot-sdk, kreait/laravel-firebase, Google Gemini, Monobank, Autoria |
| Front (website) | Blade, Livewire 3, Tailwind, Vite |
| Tests | Pest 3 |

## Getting started (Laravel Sail)

Requirements: Docker.

```bash
cp .env.example .env              # then fill in the values (see below)
composer install                  # or: docker run --rm -v "$(pwd)":/app -w /app laravel/sail-php83-composer composer install
vendor/bin/sail up -d
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate --seed
vendor/bin/sail npm install && vendor/bin/sail npm run build
```

The app is available at http://localhost (MySQL on port 3306). The seeders create roles and permissions, car models/generations, colors, cities, and publication types.

Background work needs two extra processes:

```bash
vendor/bin/sail artisan queue:work      # queue (QUEUE_CONNECTION=database)
vendor/bin/sail artisan schedule:work   # scheduler, see routes/console.php
```

> An older Docker setup is kept in `_docker/`: `cd _docker && ./srv up`, `./srv artisan migrate`, `./srv artisan db:seed`, `./srv npm install`, `./srv npm run build` (nginx on :8080).

## Configuration

Besides the standard Laravel variables (`APP_*`, `DB_*`, `CACHE_STORE`, `QUEUE_CONNECTION`, …):

| Variable | Purpose |
|---|---|
| `TELEGRAM_BOT_TOKEN` | Bot token |
| `TELEGRAM_WEBHOOK_SECRET` | Secret checked in the `X-Telegram-Bot-Api-Secret-Token` header. **Set it in production**; when empty the webhook accepts any request |
| `TELEGRAM_CHAT_TT_CLUB` | Main club chat: join/leave tracking, welcome, digest, mentions |
| `TELEGRAM_CHAT_WELCOME` | Admin log chat: registrations, member changes, birthdays, Monobank jar payments that could not be matched to a member |
| `TELEGRAM_CHAT_SUGGESTIONS`, `TELEGRAM_CHAT_PURCHASE_REQUESTS` | Suggestions and purchase requests |
| `TELEGRAM_CHAT_SYSTEM_ERRORS` | System error alerts |
| `TELEGRAM_CHAT_TEST_BOT_2` | Test chat. With `APP_ENV=local` **all** messages go here instead of real chats |
| `LOGIN_CODE_MAX_ATTEMPTS` | Wrong login-code attempts allowed before the code is revoked (default 5) |
| `GEMINI_API_KEY` | Daily digest summarization |
| `FIREBASE_CREDENTIALS` | Path to the Firebase service-account JSON (push notifications) |
| `MONOBANK_TOKEN`, `MONOBANK_JAR_ID` | Monobank jar for membership fees |
| `AUTORIA_API_KEY` | Autoria car sync |
| `MOBILE_*` | Universal/App Links for the mobile app (`config/mobile.php`) |

Club rules (minimum season fee, season start month) live in `config/club.php`.

Telegram webhook URL: `https://<host>/api/telegram/webhook`. Register or update it with:

```bash
php artisan tg:set-webhook          # shows the current settings and the new ones, asks for confirmation
php artisan tg:set-webhook --info   # only shows the current settings (getWebhookInfo)
```

The command keeps the current webhook URL (or uses `APP_URL` + `/api/telegram/webhook` when none is set; `--url=` overrides it), sends `TELEGRAM_WEBHOOK_SECRET` as `secret_token` and the update types from `config/telegram.php` (`bots.mybot.allowed_updates`: `message`, `chat_member`, `my_chat_member`, `callback_query`). It refuses to run without the secret and warns when an update type would stop arriving. Run it once after deploying the version with login-alert buttons: until then Telegram does not deliver button presses (`callback_query`). Use it instead of the SDK's `telegram:webhook --setup`, which does not send the secret.

## Tests and code style

```bash
vendor/bin/sail pest                                   # whole suite
vendor/bin/sail pest --filter="name of the test"       # a single test
vendor/bin/sail pint                                   # code style (Laravel preset)
```

- Tests use the `testing` database (created by the Sail MySQL container); migrations are MySQL-only, SQLite is not supported.
- **Before running tests make sure there is no config cache** (`bootstrap/cache/config.php`). Otherwise Laravel ignores `phpunit.xml` and `RefreshDatabase` wipes your dev database. Clear it with `vendor/bin/sail artisan optimize:clear`.

## Project structure (what is not standard Laravel)

| Path | What is there |
|---|---|
| `routes/api.php` | The main product surface: API for the mobile app |
| `app/Eloquent/*Eloquent.php` | Static query/command helpers per entity (not models) |
| `app/Helper/ResponseHelpers.php` | `success()` / `error()` — the API response envelope |
| `app/Services/Telegram` | Bot: webhook DTOs, command handlers, chat-event handler |
| `app/Notifications/Channels/TelegramChannel.php` | The only way messages are sent to Telegram; chats are chosen via `App\Enum\EnumTelegramEvents` |
| `resources/lang/uk/telegram.php` | Telegram message templates |
| `app/Services/Digest`, `app/Services/Gemini` | Daily AI digest |
| `routes/console.php` | Scheduled tasks |
| `specs/` | Feature specifications (Spec-Kit) |

## Development workflow

- Features go through [Spec-Kit](docs/speckit-stack/README.md): `specs/NNN-feature/` holds the spec, plan and tasks.
- The whole cycle in one command: `/tt-feature <task or path to a .md file>` runs specify → clarify → plan → tasks → analyze → implement, asks questions along the way and asks for confirmation before implementing. Add «без підтверджень» to run it end to end without stops.
- Project principles: `.specify/memory/constitution.md` (simplicity/YAGNI, standard Laravel conventions, Pint-clean code).
- House conventions for endpoints, models, queued jobs and Telegram messages: `.claude/skills/tt-*`. Guidance for Claude Code: `CLAUDE.md`.

## Deploy

`php artisan deploy` on the server: `git pull origin main`, `composer install`, `migrate`, `optimize`. Only the `main` branch is deployed.
