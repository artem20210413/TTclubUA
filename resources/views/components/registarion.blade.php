<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@php
    $cities = \App\Models\City::all();
    $genes = \App\Models\CarGene::all();
    $models = \App\Models\CarModel::all();
    $colors = \App\Models\Color::all();
@endphp

<form method="POST" action="{{ route('web.post.registration') }}" enctype="multipart/form-data">
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h4 class="mb-3">👤 Інформація про користувача</h4>

    <div class="mb-3">
        <label for="name" class="form-label">Ім'я</label>
        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">Телефон</label>
        <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Telegram</label>
        <input type="text" class="form-control" name="telegram_nickname" value="{{ old('telegram_nickname') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Instagram</label>
        <input type="text" class="form-control" name="instagram_nickname" value="{{ old('instagram_nickname') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Міста (можна кілька)</label>
        <select class="form-select" name="cities[]" multiple>
            @foreach($cities as $city)
                <option value="{{ $city->id }}" @selected(collect(old('cities'))->contains($city->id))>{{ $city->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Дата народження</label>
        <input type="text" class="form-control" name="birth_date" id="birth_date_picker" placeholder="дд-мм-рррр" value="{{ old('birth_date') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Опис занять</label>
        <textarea class="form-control" name="occupation_description" rows="3">{{ old('occupation_description') }}</textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Пароль</label>
        <input type="password" class="form-control" name="password" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Підтвердження паролю</label>
        <input type="password" class="form-control" name="password_confirmation" required>
    </div>

    <div class="mb-4">
        <label class="form-label">Фото користувача (опціонально)</label>
        <input type="file" class="form-control" name="file" accept="image/*">
    </div>

    <hr>

    <h4 class="mb-3">🚗 Автомобілі</h4>

    <div id="cars-wrapper">
        <div class="car-item border p-3 mb-4 rounded">
            <div class="mb-3">
                <label class="form-label">Покоління</label>
                <select name="cars[0][gene_id]" class="form-select" required>
                    @foreach($genes as $gene)
                        <option value="{{ $gene->id }}" @selected(old('cars.0.gene_id') == $gene->id)>{{ $gene->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Модель</label>
                <select name="cars[0][model_id]" class="form-select" required>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}" @selected(old('cars.0.model_id') == $model->id)>{{ $model->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Колір</label>
                <select name="cars[0][color_id]" class="form-select" required>
                    @foreach($colors as $color)
                        <option value="{{ $color->id }}" @selected(old('cars.0.color_id') == $color->id)>{{ $color->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Назва авто (опціонально)</label>
                <input type="text" class="form-control" name="cars[0][name]" value="{{ old('cars.0.name') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">VIN-код (опціонально)</label>
                <input type="text" class="form-control" name="cars[0][vin_code]" value="{{ old('cars.0.vin_code') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Держ. номер</label>
                <input type="text" class="form-control" name="cars[0][license_plate]" value="{{ old('cars.0.license_plate') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Індивідуальний номер (опціонально)</label>
                <input type="text" class="form-control" name="cars[0][personalized_license_plate]" value="{{ old('cars.0.personalized_license_plate') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Фото авто (опціонально)</label>
                <input type="file" class="form-control" name="cars[0][file]" accept="image/*">
            </div>
        </div>
    </div>

    <div class="mb-4 text-left">
        <button type="button" class="btn btn-secondary" onclick="addCar()">➕ Додати ще авто</button>
    </div>

    <div class="mb-4 text-center">
        <button type="submit" class="btn btn-primary">📂 Створити</button>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    flatpickr("#birth_date_picker", {
        dateFormat: "d-m-Y", // формат как в Laravel-валидации
        allowInput: true,    // позволяет и ввод вручную, и выбор
        locale: "uk"         // можно сменить на "ru" или другой
    });
</script>

<script>
    let countCars = 1;

    function addCar() {
        const carList = document.querySelector('#cars-wrapper');
        const original = document.querySelector('.car-item');
        const clone = original.cloneNode(true); // глубокое клонирование

        // Заменить name="cars[0]..." на name="cars[countCars]..."
        const inputs = clone.querySelectorAll('[name]');
        inputs.forEach(input => {
            input.name = input.name.replace(/\bcars\[0]/, `cars[${countCars}]`);
            if (input.type === 'file' || input.tagName === 'SELECT') {
                input.value = '';
            } else {
                input.value = input.defaultValue || '';
            }
        });

        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-danger mb-3';
        deleteBtn.textContent = '🗑️ Видалити авто';
        deleteBtn.onclick = () => clone.remove();

        // Добавляем кнопку в клон
        clone.appendChild(deleteBtn);

        carList.appendChild(clone); // вставляем в конец

        countCars++;
    }
</script>


