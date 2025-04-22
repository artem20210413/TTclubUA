<!doctype html>
<html lang="{{Illuminate\Support\Facades\App::getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

{{--    @vite(['resources/css/app.css', 'resources/css/bootstrap/bootstrap.min.css', 'resources/js/app.js'])--}}
    <link rel="stylesheet" href="{{ asset('build/assets/app-DdHod6mR.css') }}">
    <script src="{{ asset('build/assets/app-eMHK6VFw.js') }}" defer></script>

    <title>@yield('title')</title>

    <style>
        /*body {*/
        /*    display: flex;*/
        /*    flex-direction: column;*/
        /*    height: 100vh;*/
        /*}*/

        /*header {*/
        /*    height: 80px;*/
        /*    background-color: yellow;*/
        /*    flex-shrink: 0;*/
        /*}*/

        /*main {*/
        /*    !*background-color: blue;*!*/
        /*    flex-grow: 1;*/
        /*}*/

        /*footer {*/
        /*    height: 100px;*/
        /*    background-color: green;*/
        /*    flex-shrink: 0;*/
        /*}*/

    </style>

</head>
<body>

<header> header</header>
<main class="container"> @yield('body')</main>
<footer> footer</footer>

</body>
</html>
