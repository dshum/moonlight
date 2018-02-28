@extends('moonlight::layouts.login')

@section('title', 'Moonlight')

@section('js')
<script src="/packages/moonlight/js/login.js"></script>
@endsection

@section('content')
<div class="login">
    <div class="path">
        Вход
    </div>
    <div class="block">
        <div class="error"></div>
        <form action="{{ route('moonlight.login') }}" autocomplete="off" method="POST">
            <div class="row">
                <label>Логин</label><br>
                <input type="text" name="login" value="{{ $login or null }}" placeholder="Логин">
            </div>
            <div class="row">
                <label>Пароль</label><br>
                <input type="password" name="password" placeholder="Пароль"><br>
                <a href="{{ route('moonlight.reset') }}">Забыли пароль?</a>
            </div>
            <div class="row">
                <p>
                    <input type="checkbox" name="remember" id="remember" value="1"{{ $remember ? ' checked' : '' }}>
                    <label for="remember">Запомнить меня</label>
                </p>
            </div>
            <div class="row submit">
                <input type="submit" value="Войти" class="btn">
            </div>
        </form>
    </div>
</div>
@endsection