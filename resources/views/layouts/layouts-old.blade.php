<!doctype html>
<html lang="{{Illuminate\Support\Facades\App::getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">


    <link rel="stylesheet" href="{{ asset('media/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('media/export/bootstrap/bootstrap.min.css') }}">
    {{--    <script src="{{ asset('build/assets/app-eMHK6VFw.js') }}" defer></script>--}}

    <title>@yield('title')</title>

    <style>

    </style>

</head>
<body>

{{--<header> header</header>--}}
<main class="container pt-5 pb-5"> @yield('body')</main>
{{--<footer> footer</footer>--}}

</body>
</html>
