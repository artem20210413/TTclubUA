<!doctype html>
<html lang="{{Illuminate\Support\Facades\App::getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">


    {{--    <link rel="stylesheet" href="{{ asset('media/css/custom.css') }}">--}}

    <title>@yield('title')</title>

    @vite(['resources/scss/app.scss'])
    @stack('styles')

    <style>
        @font-face {
            font-family: 'SF Pro Display';
            src: url( '/fonts/SF-Pro-Display-Regular.otf') format('opentype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'SF Pro Display';
            src: url('/fonts/SF-Pro-Display-Bold.otf') format('opentype');
            font-weight: 700;
            font-style: normal;
        }

        @font-face {
            font-family: 'SF Pro Display';
            src: url('/fonts/SF-Pro-Display-Italic.otf') format('opentype');
            font-weight: 400;
            font-style: italic;
        }
    </style>

</head>
<body>

{{--<header> header</header>--}}
@yield('body')
{{--<footer> footer</footer>--}}

</body>
</html>
