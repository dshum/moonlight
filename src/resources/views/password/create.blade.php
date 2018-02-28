@extends('moonlight::layouts.login')

@section('title', 'Сброс пароля')

@section('js')
<script src="/packages/moonlight/js/reset.js"></script>
@endsection

@section('content')
<div class="login">
    <div class="path">
        Сброс пароля
    </div>
    <div class="block">
        <div class="error"></div>
        <div class="ok"></div>
        <form action="{{ route('moonlight.reset.save') }}" autocomplete="off" method="POST">
            <input type="hidden" name="login" value="{{ $login }}">
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="row">
                <label>Новый пароль:</label><span name="password" class="error"></span><br>
                <input type="password" name="password" placeholder="Новый пароль">
            </div>
            <div class="row">
                <label>Подтверждение:</label><span name="password_confirmation" class="error"></span><br>
                <input type="password" name="password_confirmation" placeholder="Подтверждение">
            </div>
            <div class="row submit">
                <input type="submit" value="Отправить" class="btn">
            </div>
            <div class="back">
                <a href="{{ route('moonlight.login') }}">Вернуться на страницу входа</a>
            </div>
        </form>
    </div>
</div>
@endsection