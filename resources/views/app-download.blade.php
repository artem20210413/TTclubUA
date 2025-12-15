<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Скачать приложение</title>
    <style>
        :root {
            --bg: #0f1220;
            --card: #151a2e;
            --text: #e7eaf6;
            --muted: #9aa3b2;
            --accent: #6aa0ff;
            --accent-2: #8b5cf6;
            --ring: rgba(106, 160, 255, .45);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: ui-sans-serif, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            color: var(--text);
            background: radial-gradient(1200px 600px at 20% -10%, rgba(106, 160, 255, .18), transparent 50%),
            radial-gradient(900px 500px at 90% 10%, rgba(139, 92, 246, .18), transparent 55%),
            var(--bg);
            display: grid;
            place-items: center;
            padding: 2rem;
        }
        .logo-wrap {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 120px; /* размер можешь менять */
            height: auto;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.25));
        }
        .card {
            width: min(720px, 100%);
            background: linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .02));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, .07);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
            overflow: hidden;
        }

        .inner {
            padding: clamp(20px, 4vw, 40px);
        }

        .title {
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: .3px;
            font-size: clamp(28px, 4vw, 40px);
            margin: 0 0 .5rem 0;
        }

        .subtitle {
            color: var(--muted);
            font-size: clamp(14px, 2.2vw, 16px);
            margin: 0 0 1.25rem 0;
        }

        .cta {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-top: 20px;
        }

        @media (min-width: 560px) {
            .cta {
                grid-template-columns: 1fr 1fr;
            }
        }

        .btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 14px;
            padding: 14px 16px;
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
            letter-spacing: .2px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(0, 0, 0, .08));
            transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .25);
        }

        .btn:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, .22);
            box-shadow: 0 10px 22px rgba(0, 0, 0, .34), 0 0 0 6px var(--ring);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn .icon {
            display: inline-flex;
            width: 22px;
            height: 22px;
        }

        .btn-primary {
            background: linear-gradient(180deg, rgba(106, 160, 255, .25), rgba(106, 160, 255, .15));
        }

        .btn-secondary {
            background: linear-gradient(180deg, rgba(139, 92, 246, .25), rgba(139, 92, 246, .15));
        }

        .policy-wrap {
            margin-top: 16px;
            text-align: center;
        }

        .policy-link {
            color: var(--muted);
            text-decoration: none;
            border-bottom: 1px dashed rgba(154, 163, 178, .5);
            transition: color .12s ease, border-color .12s ease;
        }

        .policy-link:hover {
            color: var(--text);
            border-color: rgba(231, 234, 246, .85);
        }

        /* Tiny footer */
        .foot {
            margin-top: 26px;
            color: #8791a6;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
<main class="card" role="main">
    <div class="inner">

        <div class="logo-wrap">
            <img src="/media/images/img.png" alt="TT Club UA" class="logo">
        </div>


        <h1 class="title">Завантажити застосунок</h1>
        <p class="subtitle">Оберіть платформу та встановіть офіційний застосунок клубу. Без реклами. Безкоштовно.</p>

        <section class="cta" aria-label="Кнопки завантаження">
            <!-- iOS App (App Store) -->
            <a class="btn btn-primary" href="https://apps.apple.com/app/tt-club-ua/id6746709217" target="_blank"
               rel="noopener noreferrer" aria-label="Завантажити в App Store">
{{--              <span class="icon" aria-hidden="true">--}}
                <!-- Apple glyph -->

              </span>
                <span>Завантажити для iOS</span>
            </a>

            <!-- Android App (Google Play) -->
{{--            <a class="btn btn-secondary" href="https://play.google.com/store/apps/details?id=com.example.app"--}}
{{--               target="_blank" rel="noopener noreferrer" aria-label="Завантажити в Google Play">--}}
{{--              <span class="icon" aria-hidden="true">--}}

{{--              </span>--}}
{{--                <span>Завантажити для Android</span>--}}
{{--            </a>--}}

            <!-- Android App (APK) -->
            <a class="btn btn-secondary" href="/TT_club_UA.apk"
               target="_blank" rel="noopener noreferrer" aria-label="Завантажити APK для Android">
                {{--              <span class="icon" aria-hidden="true">--}}

                </span>
                <span>Завантажити APK для Android</span>
            </a>
        </section>

        <div class="policy-wrap">
            <a class="policy-link" href="/privacy" target="_blank" rel="noopener">Політика конфіденційності</a>
        </div>

        <div class="foot">© 2025 TT Club UA • Всі права захищені</div>
    </div>
</main>

</body>
</html>
