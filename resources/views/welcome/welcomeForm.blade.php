@php use Illuminate\Pagination\LengthAwarePaginator; @endphp
@extends('layouts.layouts')

@section('title')
    W
@endsection

@section('body')
    <form method="POST" action="">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Ім'я</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Електронна пошта</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Пароль</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="agree" name="agree">
            <label class="form-check-label" for="agree">Я погоджуюсь з умовами</label>
        </div>

        <button type="submit" class="btn btn-primary">Зберегти</button>
    </form>


@endsection




