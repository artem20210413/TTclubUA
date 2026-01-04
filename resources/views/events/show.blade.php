<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 1. Обычные SEO-теги -->
    <title>{{ $event->title }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($event->description), 155) }}">

    <!-- 2. Open Graph / Facebook / Telegram -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $event->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($event->description), 155) }}">
    <meta property="og:image" content="{{ $event->getFirstMediaUrl(\App\Enum\EnumTypeMedia::PHOTO_EVENT->value) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name', 'Laravel') }}">

    <!-- 3. Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $event->title }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($event->description), 155) }}">
    <meta name="twitter:image" content="{{ $event->getFirstMediaUrl(\App\Enum\EnumTypeMedia::PHOTO_EVENT->value) }}">

    {{-- Сюда можно добавить стили --}}
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 20px auto; padding: 0 15px; }
        .cover-image { max-width: 100%; height: auto; margin-bottom: 20px; }
        .content { margin-top: 20px; }
    </style>
</head>
<body>

    <h1>{{ $event->title }}</h1>
    <p><strong>Место:</strong> {{ $event->place }}</p>
    <p><strong>Дата:</strong> {{ $event->event_date->format('d.m.Y H:i') }}</p>

    @if($event->hasMedia(\App\Enum\EnumTypeMedia::PHOTO_EVENT->value))
        <img src="{{ $event->getFirstMediaUrl(\App\Enum\EnumTypeMedia::PHOTO_EVENT->value) }}" alt="{{ $event->title }}" class="cover-image">
    @endif

    <div class="content">
        {!! $event->description !!}
    </div>

</body>
</html>
