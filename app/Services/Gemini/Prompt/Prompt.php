<?php

namespace App\Services\Gemini\Prompt;

use App\Models\User;

class Prompt
{
    public static function buildBirthdayPrompt(User $user): string
    {
        $languageMap = [
            'uk' => 'українською мовою',
            'ru' => 'російською мовою',
            'en' => 'англійською мовою',
            'sk' => 'словацькою мовою',
        ];

        $langText = $languageMap[$user->language ?? 'uk'];
        $styleText = EnumPromptStyle::FRIENDLY->description();

        // Собираем все автомобили пользователя
        $carsText = '';
        if ($user->cars && $user->cars->count() > 0) {
            $carDetails = $user->cars->map(function ($car) {
                $model = $car->model->name ?? '';
                $generation = $car->gene->name ?? '';
                $color = $car->color->name ?? '';
                $desc = trim("{$model} {$generation} $color");

                return $desc;
            })->implode(', ');

            $carsText = "Користувач має Audi TT: {$carDetails}.";
        }

        // Особенности покупки TT
        $ttReason = $user->why_tt ? "Купив Audi TT тому, що {$user->why_tt}." : '';

        return trim("
        Створи {$langText} {$styleText} привітання з днем народження.
        {$ttReason}
        {$carsText}
        Ім'я: {$user->name}
        Вимоги:
        - Привітання повинно звучати природно.
        - Уникати зайвих повторів.
        - Ніяких шаблонних фраз типу «від усього серця».
        - Довжина — 2–4 речення.
    ");
    }

    /**
     * Compact, token-frugal prompt (FR-011): concise Ukrainian digest of useful
     * highlights from a day's chat messages, without fabricating content (FR-003).
     *
     * @param  array<int, string>  $messages
     */
    public static function buildDailyDigestPrompt(array $messages): string
    {
        $joined = implode("\n", array_map(
            static fn (int $i, string $m): string => ($i + 1).'. '.$m,
            array_keys($messages),
            $messages,
        ));

        return trim("
Ти помічник чату клубу. Нижче — сьогоднішні повідомлення чату (по одному в рядку).
Зроби стислий підсумок УКРАЇНСЬКОЮ мовою лише корисного: хто що продає/купує,
домовленості, важливі запитання й відповіді, оголошення.
Правила:
- Використовуй ЛИШЕ інформацію з повідомлень, нічого не вигадуй.
- Коротко, до 8 пунктів, кожен — один рядок з емодзі-маркером.
- Якщо корисного немає — відповідай рівно: 'Сьогодні без помітних обговорень.'
- Не додавай вступів чи пояснень, лише підсумок.

Повідомлення:
{$joined}
");
    }

    public static function buildStatisticsMentionPrompt(array $data): string
    {
        $styleText = EnumPromptStyle::GARAGE->description();

        return trim("
    Ти — креативний копірайтер автоклубу TT Club UA. Твоє завдання: написати підсумковий звіт активності 'Фа-Фа' (коли одноклубники помічають один одного на дорогах) за минулий місяць.

    ДАНІ ДЛЯ ЗВІТУ:
    - Звіт за: {$data['MONTH_NAME']}
    - Всього 'Фа-Фа' за весь час існовання додатка: {$data['TOTAL_ALL_TIME']}
    - Всього за минулий місяць: {$data['TOTAL_MONTH']}
    - Найактивніший день місяця: {$data['MOST_ACTIVE_DAY']}
    - Найпопулярніший колір: {$data['MOST_SPOTTED_COLOR']} ({$data['COLOR_COUNT']} згадок)

    СПИСОК ТОП-МАШИН (JSON):
    {$data['TOP_CAR_DATA']}

    СПИСОК ТОП-МИСЛИВЦІВ (JSON):
    {$data['TOP_HUNTER_DATA']}

    ВИМОГИ ДО СТИЛЮ ТА ФОРМАТУ:
    - Максимальна довжина тексту — 1500 символів.
    - Виділяй всі змінні у тексті зі звіту.
    - Мова: українська. Стиль: {$styleText}.
    - Якщо в JSON на одному місці декілька переможців (масиви 'cars' або 'winners'), обов'язково згадай їх ВСІХ.
    - Формат для машин: 'Назва авто - власник @username'.
    - Формат для мисливців: '@username всього Х фа-фа'. Обов'язково вказуй кількість унікальних авто (unique_cars_count), за якими побудовано топ.
    - Використовуй емодзі (🥇, 🎖, 🏎💨, 🌈).

    СУВОРІ ПРАВИЛА ФОРМАТУВАННЯ (ДЛЯ TELEGRAM HTML):
        - ЗАБОРОНЕНО використовувати символи Markdown для заголовків (наприклад, ### або ##).
        - ЗАБОРОНЕНО використовувати горизонтальні розділювачі типу '---'.
        - Для виділення заголовків використовуй ТІЛЬКИ емодзі та жирний текст через тег <b>. Приклад: 🏆 <b>ТОП-МАШИНИ</b>.
        - Текст повинен бути чистим HTML, який підтримує Telegram (використовуй <b> для акцентів та <i> для іронії/сленгу).
        - Ніяких списків через зірочки (*). Замість них використовуй емодзі-маркери (наприклад, 🏎 або ⚡️).

    СТРУКТУРА ЗВІТУ:
    - Привітання.
    - Загальна статистика (пробіг за весь час та місяць, найактивніший день, колір місяця).
    - ТОП авто (авто, які ловили найчастіше).
    - ТОП людина (ті, хто впіймав найбільше РІЗНИХ авто).
    - Заклик фіксувати одноклубників далі.
    - Згенеровано штучним інтелектом (бета-тестування)
    ");
    }
}
