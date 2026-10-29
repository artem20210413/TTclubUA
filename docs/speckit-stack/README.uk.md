# Стек Spec-Kit + Claude Skills

> Переклад українською. Канонічним джерелом істини є `README.md` (англійською).

Цей проєкт використовує **[GitHub Spec-Kit](https://github.com/github/spec-kit)** («speckit»)
як інструментарій для Spec-Driven Development (SDD), підключений до Claude Code у вигляді
набору **Skills** (`.claude/skills/speckit-*/SKILL.md`), а не slash-команд у вигляді окремих
markdown-файлів. Це єдиний непередвстановлений (project-specific) інструментарій, знайдений у
цьому репозиторії — усе інше, доступне агенту (Postman, dataviz, review тощо), є глобальним
скілом/плагіном, а не тим, що декларують власні `.claude/` та `.specify/` директорії цього
проєкту.

Ця папка документує цей стек настільки детально, щоби інший проєкт міг відтворити ідентичний
конвеєр. Читайте у такому порядку:

1. **README.md** (цей файл) — карта складових і наскрізний життєвий цикл.
2. **ARCHITECTURE.md** — рантайм `.specify/`: конфігураційні файли, скрипти, стек
   резолюції/перевизначення шаблонів, механізм хуків розширень, декларативний файл воркфлоу.
3. **SKILLS.md** — глибокий розбір усіх 10 скілів: тригер, вхідні дані, вихідні дані, точний
   алгоритм, обмеження/крайні випадки, формат виводу.
4. **BOOTSTRAP.md** — покрокова інструкція, як встановити цей самий стек у новому проєкті.

## Яку проблему це вирішує

Spec-Kit перетворює «напиши фічу» на повторюваний конвеєр із контрольними точками, щоб LLM-агент
(чи людина) ніколи не переходив одразу від однорядкового запиту до коду. Кожна фіча отримує
власну директорію під `specs/`, що містить специфікацію, план і список задач — усі вони
виводяться одне з одного та з проєктної *конституції* (незаперечних принципів). Конвеєр
побудований так, що вихід кожного етапу є обов'язковим входом наступного — пропустити кроки без
явного обходу неможливо.

## 10 скілів у порядку життєвого циклу

| # | Скіл | Коли виконується | Читає | Записує |
|---|------|-------------------|-------|---------|
| 0 | `speckit-constitution` | Налаштування проєкту або будь-яка зміна керівних принципів | `.specify/memory/constitution.md`, контекст репозиторію | `.specify/memory/constitution.md` (+ поширення на шаблони/команди) |
| 1 | `speckit-specify` | Старт нової фічі | Вільний текстовий опис фічі від користувача, конституція | Новий `specs/NNN-short-name/spec.md`, `.specify/feature.json`, `checklists/requirements.md` |
| 2 | `speckit-clarify` | Після specify, перед plan | `spec.md`, конституція | `spec.md` (додає секцію `## Clarifications`), повторна перевірка `checklists/requirements.md` |
| 3 | `speckit-plan` | Коли специфікація зрозуміла | `spec.md`, конституція, `plan-template.md` | `plan.md`, `research.md`, `data-model.md`, `contracts/*`, `quickstart.md` |
| 4 | `speckit-tasks` | Після plan | `plan.md`, `spec.md`, `data-model.md`, `contracts/`, `research.md` | `tasks.md` (впорядкований за залежностями чекліст по користувацьких історіях) |
| 5 | `speckit-checklist` | Будь-коли після появи spec.md | `spec.md`/`plan.md`/`tasks.md`, конституція | `checklists/<domain>.md` («юніт-тести для англійської») |
| 6 | `speckit-analyze` | Після tasks, перед implement | `spec.md`, `plan.md`, `tasks.md`, конституція | Нічого — тільки звіт у чаті (read-only) |
| 7 | `speckit-implement` | Коли tasks + чеклісти готові | `tasks.md`, `plan.md`, `data-model.md`, `contracts/`, конституція | Код застосунку; позначає задачі `[X]` у `tasks.md` |
| 8 | `speckit-converge` | Після проходу implement, щоб виявити розбіжності | `spec.md`, `plan.md`, `tasks.md`, поточний код | Дописує секцію `## Phase N: Convergence` у `tasks.md` (append-only) |
| 9 | `speckit-taskstoissues` | Опційно, лише з GitHub-рімоутом | `tasks.md`, `git remote` | GitHub Issues (по одному на задачу, дедуплікація за `T###` у заголовку) |

`speckit-analyze` та `speckit-checklist` — це **ворітниці якості (quality gates)**, а не етапи
конвеєра: їх можна викликати повторно, і вони ніколи не змінюють структуру
`spec.md`/`plan.md`/`tasks.md`, окрім перемикання чекбоксів. `speckit-converge` — єдина команда,
яка має запускатися *після* `speckit-implement`, щоб узгодити розбіжність між тим, що було
заплановано, і тим, що реально побудовано.

## Наскрізний життєвий цикл

```
/speckit-constitution   (раз на проєкт, повертатися при зміні принципів)
        │
        ▼
/speckit-specify "<опис фічі>"
        │  → specs/NNN-short-name/spec.md
        │  → specs/NNN-short-name/checklists/requirements.md (автоматична ворітниця якості)
        ▼
/speckit-clarify                              (≤5 цільових питань/відповідей, вшиваються у spec.md)
        ▼
/speckit-plan                                  → plan.md, research.md, data-model.md,
        │                                         contracts/, quickstart.md
        ▼
/speckit-tasks                                  → tasks.md (фазований, по user story, з позначками [P])
        │
        ├─ /speckit-checklist <domain>          (опційно, повторювана ворітниця якості)
        ├─ /speckit-analyze                     (опційно, read-only звіт по всіх артефактах)
        │
        ▼
/speckit-implement                              → реальний код, чекбокси в tasks.md перемикаються
        │
        ▼
/speckit-converge                               (опційно) → дописує задачі на залишок роботи
        │
        ▼
/speckit-taskstoissues                          (опційно, лише GitHub) → по одному Issue на задачу
```

Кожен файл `SKILL.md` захищає цей порядок на рівні реалізації: `speckit-clarify` попереджає,
якщо запущений без spec, `speckit-plan`/`speckit-tasks`/`speckit-implement` жорстко завершуються
через `check-prerequisites.sh`, якщо відсутній потрібний попередній артефакт, а
`speckit-analyze`/`speckit-converge` відмовляються виконуватись, доки не існує `tasks.md`.

## Карта директорій

```
.claude/skills/speckit-*/SKILL.md     # 10 визначень скілів (саме це завантажує Claude Code)
.specify/
├── feature.json                      # вказівник на АКТИВНУ наразі фічу
├── init-options.json                 # як цей проєкт був ініціалізований (ai, стиль нумерації…)
├── integration.json                  # яка AI-інтеграція встановлена + стиль її викликів
├── memory/
│   └── constitution.md               # незаперечні принципи ЦЬОГО проєкту
├── scripts/bash/                     # спільні shell-хелпери, які викликають усі скіли
│   ├── common.sh
│   ├── create-new-feature.sh
│   ├── check-prerequisites.sh
│   ├── setup-plan.sh
│   └── setup-tasks.sh
├── templates/                        # канонічні шаблони, що копіюються у кожну директорію фічі
│   ├── spec-template.md
│   ├── plan-template.md
│   ├── tasks-template.md
│   └── checklist-template.md
└── workflows/speckit/workflow.yml    # декларативний макро-воркфлоу (specify→plan→tasks→implement)

specs/
└── NNN-short-feature-name/           # по одній директорії на фічу, послідовний 3-значний префікс
    ├── spec.md
    ├── checklists/
    │   ├── requirements.md           # автогенерується speckit-specify
    │   └── <domain>.md               # створюється вручну через speckit-checklist
    ├── plan.md
    ├── research.md
    ├── data-model.md
    ├── contracts/
    ├── quickstart.md
    └── tasks.md
```

## Що є унікальним саме для цього проєкту

Сам стек (скрипти, шаблони, визначення скілів) — це стандартний, незмінений дистрибутив
Spec-Kit; нічого тут не написане вручну для цього репозиторію. Єдиний проєкт-специфічний
артефакт — це `.specify/memory/constitution.md`, який кодує два принципи, підлаштовані під цю
кодову базу:

1. **Simplicity & YAGNI** — жодних умоглядних абстракцій; перевага вбудованим механізмам
   Laravel.
2. **Laravel & Framework Conventions** — тонкі контролери, Form Requests, бізнес-логіка в
   моделях/actions/сервісах, використання встановлених пакетів (Jetstream, Sanctum,
   spatie/laravel-permission, spatie/laravel-medialibrary, owen-it/laravel-auditing) замість
   винаходу власних аналогів, чистий за Pint стиль коду.

Кожен запуск `/speckit-plan` перевіряється на відповідність цим двом принципам. Новий проєкт,
що переймає цей стек, **не повинен** копіювати цю конституцію буквально — варто запустити
`/speckit-constitution` з нуля і вивести принципи з реального стеку та розміру команди того
проєкту (див. BOOTSTRAP.md).

Цей репозиторій також прийняв одну **локальну конвенцію, яка не є частиною стандартного
Spec-Kit**: кожен артефакт, створений усередині `specs/<feature>/`, отримує супутній переклад
`*.uk.md` українською (наприклад, `spec.md` → `spec.uk.md`), що підтримується синхронно як
зручна read-only копія. Англійський файл залишається канонічним, бо власний інструментарій
Spec-Kit (скрипти, скіли) завжди читає лише файл без суфікса.

## Реальний приклад у цьому репозиторії

`specs/008-purchase-request-api/` показує фічу, яка пройшла шляхом
specify → plan → tasks (містить `spec.md`, `plan.md`, `tasks.md`, `research.md`,
`data-model.md`, `quickstart.md`, `contracts/`, `checklists/`). Сім попередніх фіч
(`001`…`007`) демонструють той самий шаблон на різних стадіях завершеності — гарний набір для
орієнтування, як виглядають «справжні» артефакти цього конвеєра, на відміну від шаблонів.
