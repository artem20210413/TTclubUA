# TT-Club

> English version: [README.md](README.md)

Бекенд українського **клубу власників Audi TT**. Обслуговує мобільний застосунок клубу (JSON API), публічний сайт із реєстрацією та Telegram-бота в чатах клубу.

## Можливості

- **Учасники та авто**: профілі, ролі, міста, авто учасників, імпорт і експорт учасників в Excel.
- **Ринок TT**: оголошення Audi TT з Autoria, синхронізація двічі на день (API `external-cars`).
- **Реєстрація**: публічна форма на сайті → перевірка адміном → підтвердження в акаунт учасника.
- **«Фа-фа» (згадки)**: учасники фотографують клубні авто на дорозі; власник отримує пуш, а в чаті клубу з'являється пост.
- **Фінанси**: сезонні внески, витрати, банка Monobank (вебхук), статистика по сезонах.
- **Життя клубу**: події та календар, партнери й акції, товари із заявками на покупку, розіграші, пропозиції.
- **Telegram-бот**:
  - вхід у застосунок за одноразовим кодом, що надходить у Telegram;
  - прив'язка Telegram-акаунта через надсилання власного контакту; `/setpassword` та інші команди;
  - відстеження вступу й виходу в головному чаті клубу (активує або деактивує акаунти), привітання новачків;
  - щоденний AI-дайджест головного чату (Google Gemini), дні народження, щомісячна статистика згадок, закріплення повідомлень;
  - сповіщення про системні помилки в окремий чат.
- **Пуш-сповіщення** через Firebase Cloud Messaging.

## Технології

| Сфера | Що використовується |
|---|---|
| Фреймворк | Laravel 11, PHP 8.2+ |
| Авторизація | Sanctum (API-токени), Jetstream/Fortify, spatie/laravel-permission (ролі) |
| Дані | MySQL 8, Eloquent, owen-it/laravel-auditing, spatie/laravel-medialibrary |
| Інтеграції | irazasyed/telegram-bot-sdk, kreait/laravel-firebase, Google Gemini, Monobank, Autoria |
| Фронт (сайт) | Blade, Livewire 3, Tailwind, Vite |
| Тести | Pest 3 |

## Запуск (Laravel Sail)

Потрібен Docker.

```bash
cp .env.example .env              # далі заповніть значення (див. нижче)
composer install                  # або: docker run --rm -v "$(pwd)":/app -w /app laravel/sail-php83-composer composer install
vendor/bin/sail up -d
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate --seed
vendor/bin/sail npm install && vendor/bin/sail npm run build
```

Застосунок буде доступний на http://localhost (MySQL на порту 3306). Сідери створюють ролі й дозволи, моделі та покоління авто, кольори, міста й типи публікацій.

Для фонових задач потрібні ще два процеси:

```bash
vendor/bin/sail artisan queue:work      # черга (QUEUE_CONNECTION=database)
vendor/bin/sail artisan schedule:work   # планувальник, див. routes/console.php
```

> Старе Docker-оточення лежить у `_docker/`: `cd _docker && ./srv up`, `./srv artisan migrate`, `./srv artisan db:seed`, `./srv npm install`, `./srv npm run build` (nginx на :8080).

## Налаштування

Крім стандартних змінних Laravel (`APP_*`, `DB_*`, `CACHE_STORE`, `QUEUE_CONNECTION`, …):

| Змінна | Призначення |
|---|---|
| `TELEGRAM_BOT_TOKEN` | Токен бота |
| `TELEGRAM_WEBHOOK_SECRET` | Секрет, що перевіряється в заголовку `X-Telegram-Bot-Api-Secret-Token`. **Обов'язково задайте на проді**: якщо змінна порожня, вебхук приймає будь-які запити |
| `TELEGRAM_CHAT_TT_CLUB` | Головний чат клубу: вступ/вихід, привітання, дайджест, згадки |
| `TELEGRAM_CHAT_WELCOME` | Адмінський лог-чат: реєстрації, зміни учасників, дні народження |
| `TELEGRAM_CHAT_SUGGESTIONS`, `TELEGRAM_CHAT_PURCHASE_REQUESTS` | Пропозиції та заявки на покупку |
| `TELEGRAM_CHAT_SYSTEM_ERRORS` | Сповіщення про системні помилки |
| `TELEGRAM_CHAT_TEST_BOT_2` | Тестовий чат. При `APP_ENV=local` **усі** повідомлення йдуть сюди замість справжніх чатів |
| `LOGIN_CODE_MAX_ATTEMPTS` | Скільки неправильних спроб ввести код входу дозволено, перш ніж код анулюється (за замовчуванням 5) |
| `GEMINI_API_KEY` | Підсумовування для щоденного дайджесту |
| `FIREBASE_CREDENTIALS` | Шлях до JSON сервісного акаунта Firebase (пуші) |
| `MONOBANK_TOKEN`, `MONOBANK_JAR_ID` | Банка Monobank для внесків |
| `AUTORIA_API_KEY` | Синхронізація оголошень Autoria |
| `MOBILE_*` | Universal/App Links мобільного застосунку (`config/mobile.php`) |

Правила клубу (мінімальний внесок за сезон, місяць початку сезону) — у `config/club.php`.

URL вебхука Telegram: `https://<host>/api/telegram/webhook`. Реєструється через `setWebhook` з тим самим `secret_token`, що й у `TELEGRAM_WEBHOOK_SECRET`.

## Тести і стиль коду

```bash
vendor/bin/sail pest                                   # увесь набір
vendor/bin/sail pest --filter="назва тесту"            # один тест
vendor/bin/sail pint                                   # стиль коду (пресет Laravel)
```

- Тести працюють із базою `testing` (її створює MySQL-контейнер Sail). Міграції написані під MySQL, SQLite не підтримується.
- **Перед запуском тестів переконайтеся, що немає кешу конфігу** (`bootstrap/cache/config.php`). Інакше Laravel проігнорує `phpunit.xml`, і `RefreshDatabase` зітре вашу робочу базу. Очистити кеш: `vendor/bin/sail artisan optimize:clear`.

## Структура (те, що відрізняється від стандартного Laravel)

| Шлях | Що там |
|---|---|
| `routes/api.php` | Основна частина продукту: API для мобільного застосунку |
| `app/Eloquent/*Eloquent.php` | Статичні хелпери запитів і дій для кожної сутності (не моделі) |
| `app/Helper/ResponseHelpers.php` | `success()` / `error()` — формат відповідей API |
| `app/Services/Telegram` | Бот: DTO вебхука, обробники команд і подій чатів |
| `app/Notifications/Channels/TelegramChannel.php` | Єдиний шлях надсилання в Telegram; чати обираються через `App\Enum\EnumTelegramEvents` |
| `resources/lang/uk/telegram.php` | Шаблони повідомлень Telegram |
| `app/Services/Digest`, `app/Services/Gemini` | Щоденний AI-дайджест |
| `routes/console.php` | Задачі за розкладом |
| `specs/` | Специфікації фіч (Spec-Kit) |

## Процес розробки

- Фічі проходять через [Spec-Kit](docs/speckit-stack/README.uk.md): у `specs/NNN-feature/` лежать специфікація, план і задачі.
- Принципи проєкту: `.specify/memory/constitution.md` (простота/YAGNI, стандартні конвенції Laravel, код без зауважень Pint).
- Домовленості для ендпоінтів, моделей, черг і повідомлень Telegram: `.claude/skills/tt-*`. Інструкції для Claude Code: `CLAUDE.md`.

## Деплой

`php artisan deploy` на сервері: `git pull origin main`, `composer install`, `migrate`, `optimize`. Деплоїться лише гілка `main`.
