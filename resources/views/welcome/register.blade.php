@php use Illuminate\Pagination\LengthAwarePaginator; @endphp
@extends('layouts.layouts')

@section('title','Заявка на участника')

@section('body')

    @php
        $cities = \App\Models\City::orderBy('name')->get();
        $genes = \App\Models\CarGene::all();
        $models = \App\Models\CarModel::all();
        $colors = \App\Models\Color::all();
    @endphp

    <div class="register-page">
        <div class="logo__intro">
            <img class="logo" src="{{ asset('media/images/logo.webp') }}" alt="Логотип Audi TT Club UA">
        </div>

        @if(session('status'))
            <div class="alert success">{{ session('status') }}</div>
        @endif

        {{-- Ошибки валидации --}}
        @if ($errors->any())
            <div class="alert error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="card form" method="POST" action="{{ route('web.register.apply') }}" enctype="multipart/form-data"
              novalidate>
            @csrf

            <h1 class="h1">Заявка на учасника</h1>

            {{-- ===== Ваша інфо ===== --}}
            <fieldset class="fieldset">
                <div class="grid grid-4">
                    <label class="field">
                        <span>Ім’я та Прізвище*</span>
                        <input name="full_name" type="text" required value="{{ old('full_name') }}">
                    </label>

                    <label class="field">
                        <span>Телефон*</span>
                        <input name="phone" type="tel" inputmode="tel" placeholder="+380…" required
                               value="{{ old('phone') }}">
                    </label>

                    <label class="field">
                        <span>Дата народження*</span>
                        <input name="birthday" type="date" required value="{{ old('birthday') }}">
                    </label>

                    <label class="field">
                        <span>Місто проживання*</span>
                        <select class="form-select" name="city">
                            @foreach($cities as $city)
                                <option
                                    value="{{ $city->id }}" @selected(collect(old('city'))->contains($city->id))>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        {{--                        <input name="city" type="text" required value="{{ old('city') }}">--}}
                    </label>

                    <label class="field">
                        <span>Нік у Telegram</span>
                        <input name="tg" type="text" placeholder="@nickname" value="{{ old('tg') }}">
                    </label>

                    <label class="field">
                        <span>Нік в Instagram</span>
                        <input name="ig" type="text" placeholder="@nickname" value="{{ old('ig') }}">
                    </label>

                    <label class="field field--wide ">
                        <span>Адрес @TT (для підпису)</span>
                        <input name="tt_handle" type="text" placeholder="@tt_example" value="{{ old('tt_handle') }}">
                    </label>

                    <label class="field field--wide ">
                        <span>Чому саме Audi TT?</span>
                        <textarea name="why_tt" rows="3">{{ old('why_tt') }}</textarea>
                    </label>

                    <label class="field field--wide ">
                        <span>Опис занять, роб. діяльності</span>
                        <textarea name="bio" rows="3">{{ old('bio') }}</textarea>
                    </label>

                    <div class="field field--wide upload">
                        <span>Додати фото профілю</span>
                        <label class="upload__drop">
                            <input id="profile_photo" type="file" accept="image/*" name="profile_photo">
{{--                            <span class="upload__hint">Завантажити фото</span>--}}
                            @include('components.svg.image')
                        </label>
                        <img id="profile_preview" class="upload__preview" alt="" hidden>
                    </div>

                    <div class="field pass">
                        <span>Вигадати пароль*</span>
                        <div class="pass__wrap">
                            <input id="password" name="password" type="password" required minlength="6">
                            <button type="button" class="icon-btn" aria-label="Показати пароль"
                                    data-toggle-password="#password">👁
                            </button>
                        </div>
                    </div>

                    <div class="field pass">
                        <span>Підтвердження паролю*</span>
                        <div class="pass__wrap">
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                   minlength="6">
                            <button type="button" class="icon-btn" aria-label="Показати пароль"
                                    data-toggle-password="#password_confirmation">👁
                            </button>
                        </div>
                    </div>
                </div>
            </fieldset>

            {{-- ===== Ваше авто ===== --}}
            <fieldset class="fieldset">

                <h1 class="h1">Авто</h1>
{{--                <legend class="legend">Авто</legend>--}}

                <div id="cars" class="cars">
                    {{-- Шаблон одного авто --}}
                    <div class="car card--soft" data-car>
                        <div class="grid grid-2">
                            <label class="field">
                                <span>Модель*</span>
                                <select name="car[model]" required>
                                    <option value="">Оберіть…</option>
                                    @foreach($models as $model)
                                        <option
                                            value="{{ $model->id }}" @selected(old("car.model") == $model->id)>{{ $model->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="field">
                                <span>Генерація</span>
                                <select name="car[gen]">
                                    <option value="">Оберіть...</option>
                                    @foreach($genes as $gene)
                                        <option
                                            value="{{ $gene->id }}" @selected(old("car.gen") == $gene->id)>{{ $gene->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="field">
                                <span>Державний номер*</span>
                                <input name="car[plate]" type="text" value="{{ old('cars.0.plate') }}"
                                       placeholder="KA6969CH">
                            </label>

                            <label class="field">
                                <span>Індивідуальний номер</span>
                                <input name="car[vanity]" type="text" value="{{ old('cars.0.vanity') }}"
                                       placeholder="UGROZA">
                            </label>

                            <label class="field">
                                <span>Рік випуску</span>
                                <select name="car[year]">
                                    <option value="">Оберіть...</option>
                                    @for($y = now()->year; $y >= 1998; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </label>

                            <div class="field">
                                <span>Оберіть колір</span>
                                <div class="colors">
                                    @foreach($colors as $color)
                                        <label class="color">
                                            <input type="radio" name="car[color]"
                                                   value="{{ $color->id }}" {{ $color->id=== old('color')?'checked':'' }}>
                                            @if($color->hex == '#')
                                                <span
                                                    style="background: conic-gradient(red 0% 14.28%,orange 14.28% 28.56%,yellow 28.56% 42.84%,green 42.84% 57.12%,cyan 57.12% 71.4%,blue 71.4% 85.68%,violet 85.68% 100%);"></span>
                                            @else
                                                <span style="--dot: {{ $color->hex }}"></span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="field upload field--full">
                                <span>Завантажити фото авто</span>
                                <label class="upload__drop">
                                    <input type="file" accept="image/*" name="car[photo]" data-car-photo>
                                    <span class="upload__hint">Додати фото</span>
                                </label>
                                <img class="upload__preview" alt="" hidden>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="link-btn" id="addCar">Додати ще авто +</button>
            </fieldset>

            <div class="actions">
                <button type="submit" class="btn btn--primary">Подати заявку</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            /* Показать/скрыть пароль */
            document.querySelectorAll('[data-toggle-password]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const input = document.querySelector(btn.dataset.togglePassword);
                    if (!input) return;
                    input.type = input.type === 'password' ? 'text' : 'password';
                });
            });

            /* Превью фото (профиль) */
            const prof = document.getElementById('profile_photo');
            if (prof) {
                prof.addEventListener('change', e => {
                    const img = document.getElementById('profile_preview');
                    const file = e.target.files?.[0];
                    if (!img) return;
                    if (file) {
                        img.src = URL.createObjectURL(file);
                        img.hidden = false;
                    }
                });
            }

            /* Добавление ещё одного авто */
            const cars = document.getElementById('cars');
            const addBtn = document.getElementById('addCar');
            if (addBtn && cars) {
                addBtn.addEventListener('click', () => {
                    const index = cars.querySelectorAll('[data-car]').length;
                    const tpl = cars.querySelector('[data-car]').cloneNode(true);

                    // очистка значений + новые name с индексом
                    tpl.querySelectorAll('input,select,textarea').forEach(el => {
                        const name = el.getAttribute('name');
                        if (name) el.setAttribute('name', name.replace(/\[\d+]/, '[' + index + ']'));
                        if (el.type === 'radio' || el.type === 'checkbox') el.checked = false;
                        else el.value = '';
                    });

                    // сброс превью
                    const prev = tpl.querySelector('.upload__preview');
                    if (prev) {
                        prev.hidden = true;
                        prev.removeAttribute('src');
                    }

                    // навесим превью на фото авто
                    tpl.querySelectorAll('[data-car-photo]').forEach(inp => {
                        inp.addEventListener('change', e => {
                            const img = inp.closest('.field').querySelector('.upload__preview');
                            const file = e.target.files?.[0];
                            if (img && file) {
                                img.src = URL.createObjectURL(file);
                                img.hidden = false;
                            }
                        });
                    });

                    cars.appendChild(tpl);
                });

                // превью для первого авто
                cars.querySelectorAll('[data-car-photo]').forEach(inp => {
                    inp.addEventListener('change', e => {
                        const img = inp.closest('.field').querySelector('.upload__preview');
                        const file = e.target.files?.[0];
                        if (img && file) {
                            img.src = URL.createObjectURL(file);
                            img.hidden = false;
                        }
                    });
                });
            }
        </script>
    @endpush
@endsection
