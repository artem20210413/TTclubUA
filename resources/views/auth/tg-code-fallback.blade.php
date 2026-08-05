<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Вхід у TT Club UA</title>
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

        .card {
            width: min(560px, 100%);
            background: linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .02));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, .07);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
            overflow: hidden;
        }

        .inner {
            padding: clamp(20px, 4vw, 40px);
            text-align: center;
        }

        .title {
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: .3px;
            font-size: clamp(24px, 4vw, 32px);
            margin: 0 0 .5rem 0;
        }

        .subtitle {
            color: var(--muted);
            font-size: clamp(14px, 2.2vw, 16px);
            margin: 0 0 1.5rem 0;
        }

        .btn {
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
            background: linear-gradient(180deg, rgba(106, 160, 255, .25), rgba(106, 160, 255, .15));
            box-shadow: 0 6px 16px rgba(0, 0, 0, .25);
        }

        .foot {
            margin-top: 26px;
            color: #8791a6;
            font-size: 12px;
        }

        .opening {
            color: var(--muted);
            font-size: 14px;
        }

        .hidden {
            display: none;
        }

        .btn-secondary {
            margin-top: 12px;
            background: linear-gradient(180deg, rgba(139, 92, 246, .25), rgba(139, 92, 246, .15));
        }
    </style>
</head>
<body>
<main class="card" role="main">
    <div class="inner">
        <div id="open-state" class="hidden">
            <h1 class="title">Вхід у TT Club UA</h1>
            <p class="subtitle">Натисніть, щоб відкрити застосунок і завершити вхід.</p>

            <a id="open-app-btn" class="btn" href="#">Відкрити застосунок</a>
            <a class="btn btn-secondary" href="{{ route('app.download') }}">Завантажити застосунок</a>
        </div>

        <div id="fallback-state" class="hidden">
            <h1 class="title">Застосунок ще не встановлено</h1>
            <p class="subtitle">Щоб увійти за кодом з Telegram, спершу встановіть застосунок TT Club UA, після чого повторіть спробу входу.</p>

            <a class="btn" href="{{ route('app.download') }}">Завантажити застосунок</a>
        </div>

        <div class="foot">© {{ now()->year }} TT Club UA</div>
    </div>
</main>

<script>
    (function () {
        var params = new URLSearchParams(window.location.search);
        var phone = params.get('phone');
        var code = params.get('code');

        if (!phone || !code) {
            // Немає даних для входу — показуємо fallback без спроби відкрити застосунок.
            document.getElementById('fallback-state').classList.remove('hidden');
            return;
        }

        var deepLink = '{{ $customScheme ?? 'ttclubua' }}://login-tg-code?' + params.toString();

        // Показуємо кнопку одразу: більшість браузерів (особливо iOS Safari) блокують
        // автоматичний редірект на кастомну схему без прямого тапу користувача,
        // тому кнопка — основний, надійний спосіб відкрити застосунок.
        document.getElementById('open-state').classList.remove('hidden');
        document.getElementById('open-app-btn').addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = deepLink;
        });

        // Додатково пробуємо автоматичний редірект — спрацює в браузерах,
        // які це дозволяють (без гарантій, тому кнопка вище лишається основним шляхом).
        window.location.href = deepLink;
    })();
</script>
</body>
</html>
