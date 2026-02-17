<?php

use Telegram\Bot\Commands\HelpCommand;

return [
    /*
    |--------------------------------------------------------------------------
    | Your Telegram Bots
    |--------------------------------------------------------------------------
    | You may use multiple bots at once using the manager class. Each bot
    | that you own should be configured here.
    |
    | Here are each of the telegram bots config parameters.
    |
    | Supported Params:
    |
    | - name: The *personal* name you would like to refer to your bot as.
    |
    |       - token:    Your Telegram Bot's Access Token.
                        Refer for more details: https://core.telegram.org/bots#botfather
    |                   Example: (string) '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11'.
    |
    |       - commands: (Optional) Commands to register for this bot,
    |                   Supported Values: "Command Group Name", "Shared Command Name", "Full Path to Class".
    |                   Default: Registers Global Commands.
    |                   Example: (array) [
    |                       'admin', // Command Group Name.
    |                       'status', // Shared Command Name.
    |                       Acme\Project\Commands\BotFather\HelloCommand::class,
    |                       Acme\Project\Commands\BotFather\ByeCommand::class,
    |             ]
    */

    'chats' => [
        'welcome' => env('TELEGRAM_CHAT_WELCOME'),
        'test_bot_2' => env('TELEGRAM_CHAT_TEST_BOT_2'),
        'tt_club_ua' => env('TELEGRAM_CHAT_TT_CLUB'),
        'suggestions' => env('TELEGRAM_CHAT_SUGGESTIONS'),
    ],
    'messages' => [
        "fa_fa" => "<b>Фа-фа!</b> 🚗\n{employee}\nПривіт від {owner} 👋",
        "new_suggestion" => "📢 <b>Нове звернення!</b>\n"
            . "<b>Від:</b> {user}\n"
            . "📞<b>:</b> {phone}\n"
            . "⚙️<b>:</b> {environment_line}\n"
            . "📄<b>:</b> {description}",
        "registration" => [
            'user' => "ім'я: {name}\n"
                . "Телефон: {phone}\n"
                . "Міста: {cities}\n"
                . "Дата народження: {birth_date}\n"
                . "ТГ: {telegram_nickname} \n"
                . "Інста: {instagram_nickname}\n"
                . "Рід діяльності: {occupation_description}\n"
                . "Адреса НП (для подарунків): {mail_address}\n"
                . "Чому саме ауді ТТ?: {why_tt}\n"
                . "Дата створення: {created_at}\n",
            'car' => "🚘 Авто {model} {gene}:\n"
                . "Колір: {color}\n"
                . "Номер: {license_plate}\n"
                . "Індивідуальний номер: {personalized_license_plate}\n\n",
            'without_car' => "Немає Audi TT.",
        ],
        "auth_code" => "<b>Ваш код для входу</b>\n"
            . "<code>{code}</code>\n\n"
            . "Код діє {minutes} хвилин.",
        "new_member_welcome" => [
            'text' => "Привіііііт, {member} - наш новий КРАЩИЙ ДРУГ. 🤩🤩🤩🤩🤩🤩🤩🤩🤩🤩🤩🤩\n\n" .
                "ТТ Клуб - це Родина, Дружба, Досвід, Спілкування, Допомога та Емоції!\n\n" .

                "🔥❗️❗️❗️❗️❗️🔥\n\n" .

                "🔥Наша мета:\n" .
                "— Дружба, об\'єднання спільних інтересів. (Принаймні один інтерес у нас вже співпав)  ТТ конектінг піпл\n" .
                "— Ділитися вашим досвідом про ТТ\n" .
                "— Допомагати, якщо у когось щось трапилось на дорозі чи з автомобілем (зламався, застряг у піску, загубився у лісі... ) 🙏 \n" .
                "Пишемо, не соромимось!\n\n" .

                "🚨Спілкування (у чаті можемо піднімати будь-які теми)  головне з повагою один до одного і без негативу\n" .
                "— намагатися бути присутнім у чаті... якщо за день багато вхідних повідомлень і тема нецікава — пролистайте, але все ж, оновлюйте чат. 🙏🏻 Не накопичуйте 100/5000 смс. Адже комусь може знадобитися саме ваша порада або допомога.  😎\n" .
                "— читайте закріплені СМС в чаті.\n\n" .

                "Нам вже йде 8й рік. І ми маємо багато власних ТТрадицій\n" .
                "—подорожі бандою по Україні.\n" .
                "—щорічне святкування нашого ДН\n" .
                "—наш фірмовий ТТорт і кава на парковках\n" .
                "—ТТ кіноТТеатри під відкритим небом.\n" .
                "—покатушки по місту колоною\n" .
                "— просування нашого  ТТ бренду з душею і любов‘ю.\n" .
                "— ранкова кава в чашці ТТ\n" .
                "— поїздки в ліс зимою на наших quaTTro  помісити СНІГ 😎\n" .
                "Та багато інших розкажемо при зустрічах..\n\n" .

                "AUDI ТТ — це особливий автомобіль, і його власники так само особливі! Давай разом з нами покращувати наше ком‘юніті, адже воно само себе не будує. \n\n" .

                "КОЖЕН З НАС ВАЖЛИВА часТТинка ВЕЛИКОЇ родини\n" .
                "TTCLUB_UA🇺🇦🙏",

            'links' => [
                'Барахолка Audi TT' => 'https://t.me/+bncKfZLK4CszZWRi',
                'Instagram TT Club UA' => 'https://www.instagram.com/ttclub_ua',
                'Мерч TT Club UA' => 'https://www.instagram.com/markett_club_ua',
                'Bot TT Club UA' => 'https://t.me/TTclubUaBot',
                'Додаток TT Club UA' => 'https://www.ttclub.com.ua/app-download',

            ]
        ],
        'stats_mention' => "📢 TT CLUB UA: ЗВІТ ФА-ФА! 🏎💨\n" .
            "Привіт, бандо! Поки ви грієте мотори та натираєте диски, ми підбили дебет і кредит наших Фа-Фа у додатку. За останній місяць ми були неймовірно активними на дорогах!\n\n" .
            "📈 ЯК ЦЕ БУЛО В ЦИФРАХ: Всього ми нарахували <b>{TOTAL_MENTIONS}</b> палких «привітів».\n\n" .
            "🔥 НАЙГАРЯЧІШИЙ ДЕНЬ: Найбільше спалахів камер та вигуків «О, ТТшка!» було {MOST_ACTIVE_DAY}. Справжній бум активності!\n\n" .
            "🏆 ТОП-3 МАГНІТИ ДЛЯ ПОГЛЯДІВ: Ці красуні збирали лайки найчастіше:\n" .
            "\t - 🥇 {TOP_CAR_1_NAME} власник є {TOP_CAR_1_OWNER} — {TOP_CAR_1_COUNT} разів помітили\n" .
            "\t - 🥈 {TOP_CAR_2_NAME} власник є {TOP_CAR_2_OWNER} — {TOP_CAR_2_COUNT} разів помітили\n" .
            "\t - 🥉 {TOP_CAR_3_NAME} власник є {TOP_CAR_3_OWNER} — {TOP_CAR_3_COUNT} разів помітили\n\n" .
            "🎯 ГОЛОВНИЙ МИСЛИВЕЦЬ: Цей бро впіймав найбільше ТТшек у об'єктив: 🎖 {TOP_HUNTER} — {TOP_HUNTER_COUNT}! Красава! 👏\n\n" .
            "🎨 КОЛІР МІСЯЦЯ: Найчастіше в об’єктиви потрапляли ТТшки в кольорі: 🌈 {MOST_SPOTTED_COLOR} ({COLOR_COUNT} згадок).\n\n" .
            "Дякуємо всім, хто в темі! Не забувайте фіксувати одноклубників у додатку — зробимо наступний місяць ще яскравішим. 🤘\n\n" .
            "TT Club UA — Фа-Фа своїм! 🏎💨"

    ],
    'bots' => [
        'mybot' => [
            'token' => env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN'),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH', 'YOUR-CERTIFICATE-PATH'),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', 'YOUR-BOT-WEBHOOK-URL'),
            /*
             * @see https://core.telegram.org/bots/api#update
             */
            'allowed_updates' => null,
            'commands' => [
                // Acme\Project\Commands\MyTelegramBot\BotCommand::class
            ],
        ],

        //        'mySecondBot' => [
        //            'token' => '123456:abc',
        //        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Bot Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the bots you wish to use as
    | your default bot for regular use.
    |
    */
    'default' => 'mybot',

    /*
    |--------------------------------------------------------------------------
    | Asynchronous Requests [Optional]
    |--------------------------------------------------------------------------
    |
    | When set to True, All the requests would be made non-blocking (Async).
    |
    | Default: false
    | Possible Values: (Boolean) "true" OR "false"
    |
    */
    'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Handler [Optional]
    |--------------------------------------------------------------------------
    |
    | If you'd like to use a custom HTTP Client Handler.
    | Should be an instance of \Telegram\Bot\HttpClients\HttpClientInterface
    |
    | Default: GuzzlePHP
    |
    */
    'http_client_handler' => null,

    /*
    |--------------------------------------------------------------------------
    | Base Bot Url [Optional]
    |--------------------------------------------------------------------------
    |
    | If you'd like to use a custom Base Bot Url.
    | Should be a local bot api endpoint or a proxy to the telegram api endpoint
    |
    | Default: https://api.telegram.org/bot
    |
    */
    'base_bot_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Resolve Injected Dependencies in commands [Optional]
    |--------------------------------------------------------------------------
    |
    | Using Laravel's IoC container, we can easily type hint dependencies in
    | our command's constructor and have them automatically resolved for us.
    |
    | Default: true
    | Possible Values: (Boolean) "true" OR "false"
    |
    */
    'resolve_command_dependencies' => true,

    /*
    |--------------------------------------------------------------------------
    | Register Telegram Global Commands [Optional]
    |--------------------------------------------------------------------------
    |
    | If you'd like to use the SDK's built in command handler system,
    | You can register all the global commands here.
    |
    | Global commands will apply to all the bots in system and are always active.
    |
    | The command class should extend the \Telegram\Bot\Commands\Command class.
    |
    | Default: The SDK registers, a help command which when a user sends /help
    | will respond with a list of available commands and description.
    |
    */
    'commands' => [
        HelpCommand::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Command Groups [Optional]
    |--------------------------------------------------------------------------
    |
    | You can organize a set of commands into groups which can later,
    | be re-used across all your bots.
    |
    | You can create 4 types of groups:
    | 1. Group using full path to command classes.
    | 2. Group using shared commands: Provide the key name of the shared command
    | and the system will automatically resolve to the appropriate command.
    | 3. Group using other groups of commands: You can create a group which uses other
    | groups of commands to bundle them into one group.
    | 4. You can create a group with a combination of 1, 2 and 3 all together in one group.
    |
    | Examples shown below are by the group type for you to understand each of them.
    */
    'command_groups' => [
        /* // Group Type: 1
           'commmon' => [
                Acme\Project\Commands\TodoCommand::class,
                Acme\Project\Commands\TaskCommand::class,
           ],
        */

        /* // Group Type: 2
           'subscription' => [
                'start', // Shared Command Name.
                'stop', // Shared Command Name.
           ],
        */

        /* // Group Type: 3
            'auth' => [
                Acme\Project\Commands\LoginCommand::class,
                Acme\Project\Commands\SomeCommand::class,
            ],

            'stats' => [
                Acme\Project\Commands\UserStatsCommand::class,
                Acme\Project\Commands\SubscriberStatsCommand::class,
                Acme\Project\Commands\ReportsCommand::class,
            ],

            'admin' => [
                'auth', // Command Group Name.
                'stats' // Command Group Name.
            ],
        */

        /* // Group Type: 4
           'myBot' => [
                'admin', // Command Group Name.
                'subscription', // Command Group Name.
                'status', // Shared Command Name.
                'Acme\Project\Commands\BotCommand' // Full Path to Command Class.
           ],
        */
    ],

    /*
    |--------------------------------------------------------------------------
    | Shared Commands [Optional]
    |--------------------------------------------------------------------------
    |
    | Shared commands let you register commands that can be shared between,
    | one or more bots across the project.
    |
    | This will help you prevent from having to register same set of commands,
    | for each bot over and over again and make it easier to maintain them.
    |
    | Shared commands are not active by default, You need to use the key name to register them,
    | individually in a group of commands or in bot commands.
    | Think of this as a central storage, to register, reuse and maintain them across all bots.
    |
    */
    'shared_commands' => [
        // 'start' => Acme\Project\Commands\StartCommand::class,
        // 'stop' => Acme\Project\Commands\StopCommand::class,
        // 'status' => Acme\Project\Commands\StatusCommand::class,
    ],
];
