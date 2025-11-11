<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Зрадник</th>
        <th>Ім'я</th>
        <th>Нік Телеграм</th>
        <th>ID Телеграм</th>
        <th>Інстаграм</th>
        <th>Телефон</th>
        <th>ДР</th>
        <th>Міста</th>
        <th>Рід діяльності</th>
        <th>Чому ТТ</th>
        <th>Модель</th>
        <th>Генерація</th>
        <th>Колір</th>
        <th>Номер</th>
        <th>ІНЗ</th>
    </tr>
    </thead>
    <tbody>

    @foreach ($users as $user)
        @php
            /** @var \App\Models\User $user */

        $cities = $user->cities->pluck('name')->join(', ');
$cars = $user->cars;
        @endphp


        @if($cars->isEmpty())
            <tr>
                <td>{{ '#' . $user->id }}</td>
                <td>{{ $user->active ? 'Ні': 'Tак'}}</td>
                <td>{{ $user->name ?? '' }}</td>
                <td>{{ $user->telegram_nickname ?? '' }}</td>
                <td>{{ $user->telegram_id ? '#' . $user->telegram_id : '' }}</td>
                <td>{{ $user->instagram_nickname ?? '' }}</td>
                <td>{{ $user->phone . ' '}}</td>
                <td>{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d'): '' }}</td>
                <td>{{ $cities }}</td>
                <td>{{ $user->occupation_description ?? '' }}</td>
                <td>{{ $user->why_tt ?? '' }}</td>
            </tr>
        @else
            @foreach($cars as $car)
                <tr>
                    <td>{{ '#' . $user->id }}</td>
                    <td>{{ $user->active ? 'Ні': 'Tак'}}</td>
                    <td>{{ $user->name ?? '' }}</td>
                    <td>{{ $user->telegram_nickname ?? '' }}</td>
                    <td>{{ $user->telegram_id ? '#' . $user->telegram_id : '' }}</td>
                    <td>{{ $user->instagram_nickname ?? '' }}</td>
                    <td>{{ $user->phone . ' '}}</td>
                    <td>{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d'): '' }}</td>
                    <td>{{ $cities }}</td>
                    <td>{{ $user->occupation_description ?? '' }}</td>
                    <td>{{ $user->why_tt ?? '' }}</td>
                    <td>{{ $car->model->name }}</td>
                    <td>{{ $car->gene->name }}</td>
                    <td>{{ $car->color->name }}</td>
                    <td>{{ $car->license_plate}}</td>
                    <td>{{ $car->personalized_license_plate}}</td>
                </tr>
            @endforeach
        @endif

    @endforeach

    </tbody>
</table>
