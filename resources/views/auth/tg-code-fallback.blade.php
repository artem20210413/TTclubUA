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
    </style>
</head>
<body>
<main class="card" role="main">
    <div class="inner">
        <div id="opening-state">
            <h1 class="title">Відкриваємо застосунок…</h1>
            <p class="opening">Якщо застосунок не відкрився автоматично, за секунду тут з'явиться посилання на завантаження.</p>
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

        function showFallback() {
            document.getElementById('opening-state').classList.add('hidden');
            document.getElementById('fallback-state').classList.remove('hidden');
        }

        if (!phone || !code) {
            // Немає даних для входу — одразу показуємо fallback без спроби відкрити застосунок.
            showFallback();
            return;
        }

        var deepLink = '{{ $customScheme }}://login-tg-code?' + params.toString();

        // Якщо за 1.5с сторінка все ще видима (тобто застосунок не перехопив редірект
        // і система не переключилась на нього) — вважаємо, що застосунок не встановлений.
        var fallbackTimer = setTimeout(showFallback, 1500);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                clearTimeout(fallbackTimer);
            }
        });

        window.location.href = deepLink;
    })();
</script>
</body>
</html>
