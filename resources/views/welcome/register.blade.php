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
            {{--            @if ($errors->has('password'))--}}
            {{--                <div class="alert error">--}}
            {{--                    {{ $errors->first('password') }}--}}
            {{--                </div>--}}
            {{--            @endif--}}
        @endif

        <form class="card form" method="POST" action="{{ route('web.register.apply') }}" enctype="multipart/form-data"
        >
            @csrf

            <h1 class="h1">Заявка на учасника</h1>

            {{-- ===== Ваша інфо ===== --}}
            <fieldset class="fieldset">
                <div class="grid grid-4">
                    <label class="field @error('full_name') fail  @enderror">
                        <span>Ім’я та Прізвище</span>
                        <input name="full_name" type="text" required value="{{ old('full_name') }}">
                    </label>

                    <label class="field @error('phone') fail  @enderror">
                        <span>Телефон</span>
                        <input name="phone" type="tel" inputmode="tel" placeholder="380…" required
                               value="{{ old('phone') }}">
                    </label>

                    <label class="field @error('birthday') fail  @enderror">
                        <span>Дата народження</span>
                        <input name="birthday" type="date" required value="{{ old('birthday') }}">
                    </label>

                    <label class="field @error('city') fail  @enderror">
                        <span>Місто проживання</span>
                        <select class="form-select" name="city" required>
                            <option value="">Оберіть...</option>
                            @foreach($cities as $city)
                                <option
                                    value="{{ $city->id }}" @selected(collect(old('city'))->contains($city->id))>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        {{--                        <input name="city" type="text" required value="{{ old('city') }}">--}}
                    </label>

                    <label class="field @error('tg') fail  @enderror">
                        <span>Нік у Telegram</span>
                        <input name="tg" type="text" placeholder="@nickname" required value="{{ old('tg') }}">
                    </label>

                    <label class="field @error('ig') fail  @enderror">
                        <span>Нік в Instagram</span>
                        <input name="ig" type="text" placeholder="@nickname" value="{{ old('ig') }}">
                    </label>

                    <label class="field field--wide @error('tt_handle') fail  @enderror">
                        <span>Адреса НП для подарунку</span>
                        <input name="tt_handle" type="text" placeholder="м. Київ. НП - 123"
                               value="{{ old('tt_handle') }}">
                    </label>

                    <label class="field field--wide @error('why_tt') fail  @enderror">
                        <span>Чому саме Audi TT?</span>
                        <textarea name="why_tt" required rows="3">{{ old('why_tt') }}</textarea>
                    </label>

                    <label class="field field--wide @error('bio') fail  @enderror">
                        <span>Опис занять, роб. діяльності</span>
                        <textarea name="bio" required rows="3">{{ old('bio') }}</textarea>
                    </label>

                    <div class="field field--wide upload @error('profile_photo') fail  @enderror">
                        <span>Додати фото профілю</span>
                        <label class="upload__drop">
                            <input id="profile_photo" type="file" accept="image/*" name="profile_photo" required
                                   data-upload>
                            {{--                            <span class="upload__hint">Завантажити фото</span>--}}
                            @include('components.svg.image')

                            <img class="upload__preview" alt="Превʼю фото" style="display: none">
                        </label>
                    </div>

                    <div class="field pass @error('password') fail  @enderror">
                        <span>Вигадати пароль</span>
                        <div class="pass__wrap">
                            <input id="password" name="password" type="password" required minlength="6">
                            <button type="button" class="icon-btn" aria-label="Показати пароль"
                                    data-toggle-password="#password">
                                <span class="eye eye-open">@include('components.svg.eye')</span>
                                <span class="eye eye-close" hidden>@include('components.svg.eye-close')</span>
                            </button>
                        </div>
                    </div>

                    <div class="field pass @error('password') fail  @enderror">
                        <span>Підтвердження паролю</span>
                        <div class="pass__wrap">
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                   minlength="6">
                            <button type="button" class="icon-btn" aria-label="Показати пароль"
                                    data-toggle-password="#password_confirmation">
                                <span class="eye eye-open">@include('components.svg.eye')</span>
                                <span class="eye eye-close" hidden>@include('components.svg.eye-close')</span>
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
                    <div class="car" data-car>
                        <div class="grid grid-2" id="carSection">
                            <div class="grid grid-2">

                                <label class="field @error('car[model]') fail  @enderror">
                                    <span>Модель</span>
                                    <select name="car[model]" required>
                                        <option value="">Оберіть…</option>
                                        @foreach($models as $model)
                                            <option
                                                value="{{ $model->id }}" @selected(old("car.model") == $model->id)>{{ $model->name }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="field @error('car[gen]') fail  @enderror">
                                    <span>Генерація</span>
                                    <select name="car[gen]" required>
                                        <option value="">Оберіть...</option>
                                        @foreach($genes as $gene)
                                            <option
                                                value="{{ $gene->id }}" @selected(old("car.gen") == $gene->id)>{{ $gene->name }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="field @error('car[plate]') fail  @enderror">
                                    <span>Державний номер</span>
                                    <input name="car[plate]" type="text" required value="{{ old('car.plate') }}"
                                           placeholder="KA6969CH">
                                </label>

                                <label class="field @error('car[vanity]') fail  @enderror">
                                    <span>Індивідуальний номер</span>
                                    <input name="car[vanity]" type="text" value="{{ old('car.vanity') }}"
                                           placeholder="UGROZA">
                                </label>

                                <label class="field field--full @error('car.year') fail  @enderror">
                                    <span>Рік випуску</span>
                                    <select name="car[year]">
                                        <option value="">Оберіть...</option>
                                        @for($y = now()->year; $y >= 1998; $y--)
                                            <option
                                                @selected(old("car.year") == $gene->id) value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </label>

                                <div class="field field--full @error('car.color') fail  @enderror">
                                    <span>Оберіть колір</span>
                                    <div class="colors">
                                        @foreach($colors  as $i => $color)
                                            <label class="color">
                                                <input type="radio" name="car[color]"
                                                       value="{{ $color->id }}"
                                                    @checked($i === 0 || old('сar.color') == $color->id)>
                                                <div style="--dot: {{ $color->hex ==='#' ? '#fff':  $color->hex}}">

                                                    @if($color->hex == '#')
                                                        <span
                                                            style="background: conic-gradient(red 0% 14.28%,orange 14.28% 28.56%,yellow 28.56% 42.84%,green 42.84% 57.12%,cyan 57.12% 71.4%,blue 71.4% 85.68%,violet 85.68% 100%);"></span>
                                                    @else
                                                        <span style="--dot: {{ $color->hex }}"></span>
                                                    @endif

                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="field upload @error('car[photo]') fail  @enderror">
                                <span>Завантажити фото авто (16:9)</span>
                                <label class="upload__drop car__drop">
                                    <input type="file" accept="image/*" name="car[photo]" required data-upload>
                                    <span class="upload__hint">@include('components.svg.image')</span>
                                    <img class="upload__preview" alt="Превʼю фото" style="display: none">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <label class="friend-check">
                    <input type="checkbox"
                           id="no_tt_friend"
                           name="no_tt_friend"
                           value="1"
                           data-toggle-car="#carSection" @checked(old('no_tt_friend'))>
                    <span class="friend-check__box"></span>
                    <span class="friend-check__label">Немаю Audi TT, але хочу бути другом клубу</span>
                </label>
            </fieldset>

            <div class="actions">
                <button type="submit" class="btn btn--primary">Подати заявку</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const cb = document.getElementById('no_tt_friend');
            const section = document.querySelector(cb?.dataset.toggleCar || '#carSection');

            if (!cb || !section) return;

            const carFields = section.querySelectorAll('input, select, textarea');

            function applyState(checked) {
                // Переключаем класс на форме (для стилей)
                section.closest('fieldset')?.classList.toggle('is-friend', checked);

                carFields.forEach(el => {
                    const isCar = (el.name || '').startsWith('car');
                    if (!isCar) return;
                    if (checked) {
                        // запомним, что было required
                        if (el.required) el.dataset.wasRequired = 'true';
                        el.required = false;
                        el.disabled = true;
                    } else {
                        if (el.dataset.wasRequired === 'true') el.required = true;
                        el.disabled = false;
                    }
                });
            }

            // начальное состояние (например, после old())
            applyState(cb.checked);

            // по клику переключаем
            cb.addEventListener('change', () => applyState(cb.checked));
        })();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.field').forEach(field => {
                const input = field.querySelector('input[required], textarea[required], select[required]');
                const labelSpan = field.querySelector('span');

                if (input && labelSpan) {
                    labelSpan.textContent = labelSpan.textContent.trim() + '*';
                }
            });
        });
    </script>

    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(btn => {
            btn.addEventListener('click', () => {
                const selector = btn.getAttribute('data-toggle-password');
                const input = document.querySelector(selector);
                if (!input) return;

                const eyeOpen = btn.querySelector('.eye-open');
                const eyeClose = btn.querySelector('.eye-close');

                if (input.type === 'password') {
                    input.type = 'text';
                    eyeOpen.hidden = true;
                    eyeClose.hidden = false;
                } else {
                    input.type = 'password';
                    eyeOpen.hidden = false;
                    eyeClose.hidden = true;
                }
            });
        });
    </script>

    <script>
        document.querySelectorAll('[data-upload]').forEach(input => {
            input.addEventListener('change', e => {
                const file = e.target.files[0];
                if (!file) return;

                const drop = input.closest('.upload__drop');
                const preview = drop.querySelector('.upload__preview');
                const hint = drop.querySelector('svg');

                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
                if (hint) hint.style.display = "none"; // убираем иконку

                // очищаем blob после загрузки
                preview.onload = () => URL.revokeObjectURL(preview.src);
            });
        });
    </script>

@endsection
