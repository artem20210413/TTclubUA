@extends('layouts.layouts')

@section('title','Ласкаво просимо')
@section('body_class','page-home')

@section('body')
    <main class="hero" role="main" aria-label="Запрошення до спільноти Audi TT">
        <div class="hero__gradient">
            <div class="hero__inner">

                <div class="hero__intro">
                    <div class="hero__stack">
                        <img class="logo" src="{{ asset('media/images/logo.webp') }}" alt="Логотип Audi TT Club UA">
                        <p class="header">Вітаємо! У спільноті фанатів Audi TT</p>
                        <p class="basis">
                            Нас об'єднує стиль, динаміка і любов до легендарної Audi TT. Атмосфера, підтримка,
                            спільні поїздки та справжні знайомства. Спільнота — об’єднати власників і фанатів Audi TT
                            для спілкування, підтримки та обміну досвідом. Події — організація зустрічей, поїздок
                            і заходів для учасників клубу.
                        </p>
                        <button type="button" class="btn">Хочу до вас!</button>
                    </div>
                </div>

                <div class="hero__media" aria-label="Відеозапрошення голови клубу">
                    @include('components.svg.play')
                    <p class="media-text">Дивись запрошення від голови клубу</p>
                </div>

            </div>
        </div>
    </main>
@endsection


{{--@php use Illuminate\Pagination\LengthAwarePaginator; @endphp--}}
{{--@extends('layouts.layouts')--}}

{{--@section('title')--}}
{{--    Ласкаво просимо--}}
{{--@endsection--}}

{{--@section('body')--}}

{{--    <style>--}}
{{--        .banner {--}}
{{--            background-image: url('{{  asset('media/images/banner_1.webp')}}');--}}
{{--            background-size: cover;--}}
{{--            background-position: center;--}}
{{--            background-repeat: no-repeat;--}}
{{--            height: 100vh;--}}
{{--            width: 100%;--}}
{{--            display: flex;--}}
{{--        }--}}

{{--        .gradient {--}}
{{--            display: flex;--}}
{{--            flex-direction: column;--}}
{{--            justify-content: center;--}}
{{--            width: 75vw;--}}
{{--            height: 100vh;--}}
{{--            flex-shrink: 0;--}}
{{--            background: linear-gradient(-90deg, rgba(30, 30, 30, 0.00) 0%, rgba(0, 0, 0, 0.93) 52.78%);--}}
{{--        }--}}

{{--        .logo {--}}
{{--            width: 130px;--}}
{{--        }--}}

{{--        p.header {--}}
{{--            font-weight: 700;--}}
{{--            font-size: 75px;--}}
{{--            line-height: 106.667%; /* 106.667% */--}}
{{--            letter-spacing: 0.374px;--}}
{{--        }--}}

{{--        p.basis {--}}
{{--            font-size: 20px;--}}
{{--            line-height: 25px; /* 125% */--}}
{{--            letter-spacing: 0.38px;--}}
{{--        }--}}

{{--        button.go {--}}
{{--            cursor: pointer;--}}
{{--            width: 348px;--}}
{{--            height: 87px;--}}
{{--            padding: 10px;--}}
{{--            justify-content: center;--}}
{{--            align-items: center;--}}

{{--            font-size: 28px;--}}
{{--            letter-spacing: 0.38px;--}}

{{--            border-radius: 300px;--}}
{{--            border: 2px solid #FFF;--}}
{{--            background: 110% 110% no-repeat, radial-gradient(219.21% 50% at 50% 50%, #7B7B7B 0%, #0B0B0B 90.38%);--}}
{{--            box-shadow: 0 0 10px 0 rgba(189, 235, 255, 0.49);--}}
{{--        }--}}

{{--        button.go :hover {--}}
{{--            border: 2px solid #000;--}}
{{--            background: radial-gradient(219.21% 50% at 50% 50%, #FFF 0%, rgba(255, 255, 255, 0.35) 100%);--}}
{{--            box-shadow: 0 0 10px 0 rgba(189, 235, 255, 0.49);--}}
{{--        }--}}

{{--    </style>--}}



{{--    <main class="banner">--}}
{{--        <div class="gradient">--}}

{{--            <div style="display: flex;--}}
{{--            flex-direction: column;--}}
{{--            justify-content: center;--}}
{{--            margin-left: 5vw;--}}
{{--            gap: 60px;">--}}

{{--                <div style="display: flex;">--}}
{{--                    <div--}}
{{--                        style="display: flex; align-items: flex-start; width: 648px; flex-direction: column; gap: 20px;">--}}
{{--                        <img class="logo" src="{{asset('media/images/logo.webp')}}">--}}
{{--                        <p class="header">Вітаємо! У спільноті фанатів Audi TT</p>--}}
{{--                        <p class="basis">Нас об'єднує стиль, динаміка і любов до легендарної Audi TT. Атмосфера,--}}
{{--                            підтримка,--}}
{{--                            спільні--}}
{{--                            поїздки та--}}
{{--                            справжні знайомства. Спільнота — об’єднати власників і фанатів Audi TT для спілкування,--}}
{{--                            підтримки та--}}
{{--                            обміну досвідом. Події — організація зустрічей, поїздок і заходів для учасників клубу.</p>--}}
{{--                        <button class="go">--}}
{{--                            Хочу до вас!--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div style="display: flex;--}}
{{--    align-items: center;--}}
{{--    flex-direction: row;--}}
{{--    gap: 22px;;--}}
{{--            ">--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" width="83" height="83" viewBox="0 0 83 83" fill="none">--}}
{{--                        <circle cx="41.5" cy="41.5" r="41.5" fill="#303234"/>--}}
{{--                        <circle cx="41.5" cy="41.5001" r="32.7389" fill="#26292D"/>--}}
{{--                        <path--}}
{{--                            d="M49.45 38.6714C51.45 39.8261 51.45 42.7129 49.45 43.8676L39.4292 49.6531C37.4292 50.8078 34.9292 49.3644 34.9292 47.055L34.9292 35.484C34.9292 33.1745 37.4292 31.7312 39.4292 32.8859L49.45 38.6714Z"--}}
{{--                            fill="white"/>--}}
{{--                    </svg>--}}
{{--                    <p>Дивись запрошення від голови клубу</p>--}}
{{--                    --}}{{--                <svg xmlns="http://www.w3.org/2000/svg" width="83" height="83" viewBox="0 0 83 83" fill="none">--}}
{{--                    --}}{{--                    <circle cx="41.5" cy="41.5" r="40.5" fill="#303234" stroke="white" stroke-width="2"/>--}}
{{--                    --}}{{--                    <circle cx="41.5" cy="41.5001" r="32.7389" fill="#26292D"/>--}}
{{--                    --}}{{--                    <path d="M49.45 38.6714C51.45 39.8261 51.45 42.7129 49.45 43.8676L39.4292 49.6531C37.4292 50.8078 34.9292 49.3644 34.9292 47.055L34.9292 35.484C34.9292 33.1745 37.4292 31.7312 39.4292 32.8859L49.45 38.6714Z" fill="white"/>--}}
{{--                    --}}{{--                </svg>--}}
{{--                    <div>--}}

{{--                    </div>--}}
{{--                </div>--}}

{{--            </div>--}}

{{--        </div>--}}
{{--    </main>--}}
{{--@endsection--}}




