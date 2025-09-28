<!doctype html>
<html lang="{{Illuminate\Support\Facades\App::getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">


    <link rel="stylesheet" href="{{ asset('media/css/custom.css') }}">

    <title>@yield('title')</title>
    {{-- Подключаем Vite: SCSS соберётся в CSS  //'resources/js/app.js'--}}
    @vite(['resources/scss/app.scss'])
    @stack('styles')

    <style>
        body {
            position: relative;
        }

        *, p, form{
            padding: 0;
            margin: 0;
            border: none;
        }

    </style>

</head>
<body style="background-color: var(--background-color)">

{{--<header> header</header>--}}
@yield('body')
{{--<footer> footer</footer>--}}

</body>
</html>
